<?php

namespace App\Servicios;

/**
 * Análisis de sensibilidad de los pesos (apartados 2.5.8 y 3.5.2 de la tesis):
 * se varía el peso de cada bloque, se redistribuye la diferencia de forma
 * proporcional entre los demás y se mide la estabilidad del ordenamiento.
 */
class AnalisisSensibilidad
{
    public const ESCENARIOS = [-0.20, -0.10, 0.10, 0.20];

    /**
     * Multiplica el peso de un bloque por (1 + variación) y reparte la diferencia
     * entre los bloques restantes en proporción a su peso.
     */
    public static function variarBloque(array $pesosBloque, $bloque, float $variacion): array
    {
        $original = $pesosBloque[$bloque];
        $nuevo = min(1.0, max(0.0, $original * (1 + $variacion)));
        $diferencia = $nuevo - $original;

        $restoOriginal = array_sum($pesosBloque) - $original;
        $resultado = [];
        foreach ($pesosBloque as $clave => $peso) {
            $resultado[$clave] = $clave == $bloque
                ? $nuevo
                : ($restoOriginal > 0 ? $peso - $diferencia * ($peso / $restoOriginal) : $peso);
        }

        return $resultado;
    }

    /**
     * Pesos globales = peso del bloque × peso local (ambos normalizados).
     *
     * @param array $pesosBloque  [bloque => peso]
     * @param array $pesosLocales [bloque => [criterio => peso local]]
     */
    public static function pesosGlobales(array $pesosBloque, array $pesosLocales): array
    {
        $sumaBloques = array_sum($pesosBloque) ?: 1;
        $globales = [];
        foreach ($pesosLocales as $bloque => $locales) {
            $sumaLocal = array_sum($locales) ?: 1;
            foreach ($locales as $criterio => $peso) {
                $globales[$criterio] = ($pesosBloque[$bloque] / $sumaBloques) * ($peso / $sumaLocal);
            }
        }

        return $globales;
    }

    /**
     * Rangos con empates promediados (valor mayor = rango 1).
     */
    public static function rangos(array $valores): array
    {
        $claves = array_keys($valores);
        $ordenadas = $claves;
        usort($ordenadas, fn ($a, $b) => $valores[$b] <=> $valores[$a]);

        $rangos = [];
        $n = count($ordenadas);
        for ($i = 0; $i < $n;) {
            $j = $i;
            while ($j + 1 < $n && $valores[$ordenadas[$j + 1]] == $valores[$ordenadas[$i]]) $j++;
            $promedio = ($i + $j) / 2 + 1;
            for ($k = $i; $k <= $j; $k++) $rangos[$ordenadas[$k]] = $promedio;
            $i = $j + 1;
        }

        return $rangos;
    }

    /**
     * Correlación de rangos de Spearman (Pearson sobre rangos, admite empates).
     */
    public static function spearman(array $x, array $y): ?float
    {
        if (count($x) < 2) return null;

        $rx = self::rangos($x);
        $ry = self::rangos($y);
        $claves = array_keys($x);
        $mx = array_sum($rx) / count($rx);
        $my = array_sum($ry) / count($ry);

        $num = $dx = $dy = 0.0;
        foreach ($claves as $k) {
            $a = $rx[$k] - $mx;
            $b = $ry[$k] - $my;
            $num += $a * $b;
            $dx += $a * $a;
            $dy += $b * $b;
        }

        if ($dx == 0.0 || $dy == 0.0) return $dx == $dy ? 1.0 : null;

        return $num / sqrt($dx * $dy);
    }

    /**
     * Ejecuta el análisis sobre un conjunto de casos.
     *
     * @param array $casos [id => ['niveles' => [criterio => nivel], 'adversario' => bool]]
     */
    public static function ejecutar(array $casos, array $pesosBloque, array $pesosLocales, array $umbrales, array $escenarios = self::ESCENARIOS): array
    {
        $base = self::calcularTodos($casos, self::pesosGlobales($pesosBloque, $pesosLocales), $umbrales);

        $filas = [];
        foreach (array_keys($pesosBloque) as $bloque) {
            foreach ($escenarios as $variacion) {
                $pesos = self::variarBloque($pesosBloque, $bloque, $variacion);
                $variado = self::calcularTodos($casos, self::pesosGlobales($pesos, $pesosLocales), $umbrales);

                $cambios = 0;
                foreach ($base as $id => $r) {
                    if ($r['nivel'] !== $variado[$id]['nivel']) $cambios++;
                }

                $filas[] = [
                    'bloque' => $bloque,
                    'variacion' => $variacion,
                    'peso_original' => $pesosBloque[$bloque],
                    'peso_variado' => $pesos[$bloque],
                    'casos' => count($base),
                    'cambios_nivel' => $cambios,
                    'proporcion_cambios' => count($base) ? $cambios / count($base) : 0,
                    'spearman' => self::spearman(
                        array_map(fn ($r) => $r['indice'], $base),
                        array_map(fn ($r) => $r['indice'], $variado)
                    ),
                ];
            }
        }

        return ['base' => $base, 'escenarios' => $filas];
    }

    private static function calcularTodos(array $casos, array $pesos, array $umbrales): array
    {
        $resultado = [];
        foreach ($casos as $id => $caso) {
            $calculo = MotorRiesgo::calcular($pesos, $caso['niveles'], $caso['adversario'], $umbrales);
            $resultado[$id] = ['indice' => $calculo['indice'], 'nivel' => $calculo['nivel_prioridad']];
        }

        return $resultado;
    }
}
