<?php

namespace App\Servicios;

use InvalidArgumentException;

/**
 * Proceso analítico jerárquico de Saaty (apartados 2.5.2 a 2.5.6 de la tesis):
 * matriz recíproca, vector de prioridades por autovector principal, razón de
 * consistencia y agregación de juicios de varios expertos por media geométrica.
 */
class ProcesoAnaliticoJerarquico
{
    /** Índice aleatorio según el orden de la matriz (Tabla 2.8, Saaty 2008). */
    public const INDICE_ALEATORIO = [
        1 => 0.0, 2 => 0.0, 3 => 0.58, 4 => 0.90, 5 => 1.12, 6 => 1.24,
        7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49, 11 => 1.51,
    ];

    public const UMBRAL_CONSISTENCIA = 0.10;

    /**
     * Interpreta un juicio de la escala fundamental: "3", "1/5", 0.2 …
     * Debe estar entre 1/9 y 9.
     */
    public static function interpretarJuicio($valor): float
    {
        if (is_string($valor) && strpos($valor, '/') !== false) {
            [$a, $b] = array_map('trim', explode('/', $valor, 2));
            if (! is_numeric($a) || ! is_numeric($b) || (float) $b == 0.0) {
                throw new InvalidArgumentException("Juicio inválido: «{$valor}».");
            }
            $numero = (float) $a / (float) $b;
        } elseif (is_numeric($valor)) {
            $numero = (float) $valor;
        } else {
            throw new InvalidArgumentException("Juicio inválido: «{$valor}».");
        }

        if ($numero < 1 / 9 - 1e-9 || $numero > 9 + 1e-9) {
            throw new InvalidArgumentException("El juicio «{$valor}» está fuera de la escala 1/9 a 9.");
        }

        return $numero;
    }

    /**
     * Construye la matriz recíproca de orden n a partir de los n(n−1)/2 juicios
     * del triángulo superior, en orden (1,2), (1,3), …, (2,3), …
     */
    public static function matrizDesdeJuicios(int $n, array $juicios): array
    {
        $esperados = $n * ($n - 1) / 2;
        $juicios = array_values($juicios);
        if (count($juicios) !== $esperados) {
            throw new InvalidArgumentException("Una matriz de orden {$n} requiere {$esperados} juicios.");
        }

        $matriz = array_fill(0, $n, array_fill(0, $n, 1.0));
        $k = 0;
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $valor = self::interpretarJuicio($juicios[$k++]);
                $matriz[$i][$j] = $valor;
                $matriz[$j][$i] = 1 / $valor;
            }
        }

        return $matriz;
    }

    /**
     * Autovector principal derecho normalizado (método de la potencia).
     */
    public static function vectorPrioridades(array $matriz, int $iteraciones = 1000, float $tolerancia = 1e-12): array
    {
        $n = count($matriz);
        $w = array_fill(0, $n, 1 / $n);

        for ($iteracion = 0; $iteracion < $iteraciones; $iteracion++) {
            $nuevo = self::multiplicar($matriz, $w);
            $suma = array_sum($nuevo);
            $nuevo = array_map(fn ($x) => $x / $suma, $nuevo);

            $diferencia = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $diferencia = max($diferencia, abs($nuevo[$i] - $w[$i]));
            }
            $w = $nuevo;
            if ($diferencia < $tolerancia) break;
        }

        return $w;
    }

    public static function lambdaMax(array $matriz, array $w): float
    {
        $aw = self::multiplicar($matriz, $w);
        $n = count($w);
        $suma = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $suma += $aw[$i] / $w[$i];
        }

        return $suma / $n;
    }

    /**
     * Pesos, λmáx, CI = (λmáx − n)/(n − 1), RI y CR = CI/RI.
     */
    public static function analizar(array $matriz): array
    {
        $n = count($matriz);
        $w = self::vectorPrioridades($matriz);
        $lambda = self::lambdaMax($matriz, $w);
        $ci = $n > 1 ? max(0.0, ($lambda - $n) / ($n - 1)) : 0.0;
        $ri = self::INDICE_ALEATORIO[$n] ?? 1.51;
        $cr = $ri > 0 ? $ci / $ri : 0.0;

        return [
            'pesos' => $w,
            'lambda_max' => $lambda,
            'ci' => $ci,
            'ri' => $ri,
            'cr' => $cr,
            'consistente' => $cr <= self::UMBRAL_CONSISTENCIA + 1e-12,
        ];
    }

    /**
     * Agrega las matrices de varios expertos con la media geométrica de cada celda,
     * que preserva la reciprocidad (apartado 2.5.6).
     */
    public static function agregarGeometrica(array $matrices): array
    {
        if (empty($matrices)) {
            throw new InvalidArgumentException('No hay matrices para agregar.');
        }

        $m = count($matrices);
        $n = count($matrices[0]);
        $agregada = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $producto = 1.0;
                foreach ($matrices as $matriz) {
                    $producto *= $matriz[$i][$j];
                }
                $agregada[$i][$j] = $producto ** (1 / $m);
            }
        }

        return $agregada;
    }

    /**
     * Comparaciones que más inconsistencia aportan: las que más se alejan del
     * cociente wi/wj implícito. Se informan al experto sin sugerirle valores.
     */
    public static function comparacionesMasInconsistentes(array $matriz, array $w, int $cuantas = 2): array
    {
        $n = count($matriz);
        $lista = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $lista[] = [
                    'i' => $i,
                    'j' => $j,
                    'desvio' => abs(log($matriz[$i][$j] * $w[$j] / $w[$i])),
                ];
            }
        }
        usort($lista, fn ($a, $b) => $b['desvio'] <=> $a['desvio']);

        return array_slice($lista, 0, $cuantas);
    }

    private static function multiplicar(array $matriz, array $v): array
    {
        $resultado = [];
        foreach ($matriz as $i => $fila) {
            $suma = 0.0;
            foreach ($fila as $j => $valor) {
                $suma += $valor * $v[$j];
            }
            $resultado[$i] = $suma;
        }

        return $resultado;
    }
}
