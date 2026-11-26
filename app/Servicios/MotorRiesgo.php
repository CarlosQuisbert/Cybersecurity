<?php

namespace App\Servicios;

use InvalidArgumentException;

/**
 * Motor de evaluación del riesgo (apartados 2.5.7, 3.3.1 y 3.3.3.1 de la tesis).
 *
 * Componente independiente y sin acceso a base de datos: recibe los pesos, los
 * niveles de cada criterio y los umbrales, y devuelve el índice de riesgo (0-100)
 * y el nivel de prioridad.
 *
 *   R(i) = 100 · Σ wj · vj(i),   con vj = (nivel − 1) / 4
 */
class MotorRiesgo
{
    public const NIVEL_MINIMO = 1;
    public const NIVEL_MAXIMO = 5;

    /** Niveles de prioridad de mayor a menor. */
    public const NIVELES_PRIORIDAD = ['critico', 'alto', 'medio', 'bajo'];

    /** Criterio que solo aplica a incidentes de origen adversario. */
    public const CRITERIO_PROGRESION = 'progresion_adversaria';

    /**
     * Normaliza un nivel ordinal 1-5 al rango 0-1: niveles 1, 3 y 5 → 0; 0,5; 1.
     */
    public static function normalizar(int $nivel): float
    {
        if ($nivel < self::NIVEL_MINIMO || $nivel > self::NIVEL_MAXIMO) {
            throw new InvalidArgumentException("El nivel {$nivel} está fuera de la escala 1-5.");
        }

        return ($nivel - self::NIVEL_MINIMO) / (self::NIVEL_MAXIMO - self::NIVEL_MINIMO);
    }

    /**
     * Combina las subescalas de un criterio. Para el alcance (usuarios y sistemas)
     * se toma el mayor de los niveles; un criterio de escala única usa su nivel.
     */
    public static function combinar(array $niveles, string $regla = 'unica'): int
    {
        if (empty($niveles)) {
            throw new InvalidArgumentException('No hay niveles para combinar.');
        }

        foreach ($niveles as $nivel) {
            self::normalizar((int) $nivel);
        }

        return $regla === 'maximo' || count($niveles) > 1 ? (int) max($niveles) : (int) reset($niveles);
    }

    /**
     * Pesos que se aplican al incidente. En incidentes sin origen adversario se
     * excluyen los criterios exclusivos del adversario y su peso se redistribuye
     * de forma proporcional entre los restantes (RF6).
     *
     * @param array $pesos           [codigo => peso global]
     * @param array $soloAdversario  códigos de criterios exclusivos del adversario
     */
    public static function pesosAplicables(array $pesos, bool $adversario, array $soloAdversario = [self::CRITERIO_PROGRESION]): array
    {
        $aplicables = $adversario ? $pesos : array_diff_key($pesos, array_flip($soloAdversario));

        $suma = array_sum($aplicables);
        if ($suma <= 0) {
            throw new InvalidArgumentException('La suma de los pesos debe ser mayor que cero.');
        }

        return array_map(fn ($peso) => $peso / $suma, $aplicables);
    }

    /**
     * Convierte el índice en nivel de prioridad según los tres umbrales.
     *
     * @param array $umbrales ['critico' => x, 'alto' => y, 'medio' => z]
     */
    public static function nivelPrioridad(float $indice, array $umbrales): string
    {
        self::validarUmbrales($umbrales);

        if ($indice >= $umbrales['critico']) return 'critico';
        if ($indice >= $umbrales['alto']) return 'alto';
        if ($indice >= $umbrales['medio']) return 'medio';

        return 'bajo';
    }

    public static function validarUmbrales(array $umbrales): void
    {
        foreach (['critico', 'alto', 'medio'] as $clave) {
            if (! isset($umbrales[$clave]) || ! is_numeric($umbrales[$clave])) {
                throw new InvalidArgumentException("Falta el umbral «{$clave}».");
            }
        }

        if (! ($umbrales['critico'] > $umbrales['alto'] && $umbrales['alto'] > $umbrales['medio']
            && $umbrales['medio'] > 0 && $umbrales['critico'] <= 100)) {
            throw new InvalidArgumentException('Los umbrales deben cumplir 0 < medio < alto < crítico ≤ 100.');
        }
    }

    /**
     * Calcula el índice de riesgo y el nivel de prioridad de un incidente.
     *
     * @param array $pesos      [codigo => peso global] de los nueve criterios
     * @param array $niveles    [codigo => nivel 1-5] (el criterio excluido puede faltar)
     * @param bool  $adversario si el incidente es de origen adversario
     * @param array $umbrales   ['critico' => x, 'alto' => y, 'medio' => z]
     */
    public static function calcular(array $pesos, array $niveles, bool $adversario, array $umbrales, array $soloAdversario = [self::CRITERIO_PROGRESION]): array
    {
        $aplicados = self::pesosAplicables($pesos, $adversario, $soloAdversario);

        $aportes = [];
        $suma = 0.0;
        foreach ($aplicados as $codigo => $peso) {
            if (! array_key_exists($codigo, $niveles) || $niveles[$codigo] === null) {
                throw new InvalidArgumentException("Falta el nivel del criterio «{$codigo}».");
            }
            $nivel = (int) $niveles[$codigo];
            $valor = self::normalizar($nivel);
            $aporte = $peso * $valor * 100;
            $suma += $aporte;
            $aportes[$codigo] = [
                'nivel' => $nivel,
                'valor' => $valor,
                'peso' => $peso,
                'aporte' => round($aporte, 4),
            ];
        }

        $indice = round($suma, 2);

        return [
            'indice' => $indice,
            'nivel_prioridad' => self::nivelPrioridad($indice, $umbrales),
            'pesos_aplicados' => $aplicados,
            'aportes' => $aportes,
            'excluidos' => array_values(array_keys(array_diff_key($pesos, $aplicados))),
        ];
    }

    /**
     * Orden de la cola de prioridad: mayor índice primero; ante empate, mayor
     * progresión adversaria y, después, el más antiguo. Los no evaluados van al final.
     *
     * Cada elemento: ['indice' => ?float, 'progresion' => ?int, 'antiguedad' => int (timestamp)]
     */
    public static function comparar(array $a, array $b): int
    {
        $ia = $a['indice'] ?? null;
        $ib = $b['indice'] ?? null;

        if ($ia === null || $ib === null) {
            if ($ia === $ib) return ($a['antiguedad'] ?? 0) <=> ($b['antiguedad'] ?? 0);
            return $ia === null ? 1 : -1;
        }

        return [round($ib, 2), $b['progresion'] ?? 0, $a['antiguedad'] ?? 0]
           <=> [round($ia, 2), $a['progresion'] ?? 0, $b['antiguedad'] ?? 0];
    }
}
