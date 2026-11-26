<?php

namespace Tests\Unit;

use App\Servicios\AnalisisSensibilidad;
use App\Servicios\ProcesoAnaliticoJerarquico as AHP;
use PHPUnit\Framework\TestCase;

/**
 * Comprueba el AHP con la matriz agregada de bloques publicada en la tesis (3.3.1.3).
 */
class ProcesoAnaliticoJerarquicoTest extends TestCase
{
    public function test_matriz_agregada_de_bloques_reproduce_los_pesos_de_la_tesis()
    {
        $matriz = [
            [1, 1.741, 2.491, 3.178],
            [0.574, 1, 1.149, 2.000],
            [0.401, 0.871, 1, 1.741],
            [0.315, 0.500, 0.574, 1],
        ];

        $r = AHP::analizar($matriz);

        $this->assertEqualsWithDelta(0.435, $r['pesos'][0], 0.002);
        $this->assertEqualsWithDelta(0.242, $r['pesos'][1], 0.002);
        $this->assertEqualsWithDelta(0.200, $r['pesos'][2], 0.002);
        $this->assertEqualsWithDelta(0.124, $r['pesos'][3], 0.002);
        // La tesis publica 4,012; con las celdas redondeadas a 3 decimales se obtiene 4,0095.
        $this->assertEqualsWithDelta(4.012, $r['lambda_max'], 0.005);
        $this->assertEqualsWithDelta(0.004, $r['cr'], 0.001);
        $this->assertLessThan(0.01, $r['cr']);
        $this->assertTrue($r['consistente']);
    }

    public function test_matriz_desde_juicios_es_reciproca()
    {
        $m = AHP::matrizDesdeJuicios(3, ['3', '1/5', 0.5]);

        $this->assertSame(1.0, $m[1][1]);
        $this->assertEqualsWithDelta(1 / 3, $m[1][0], 1e-12);
        $this->assertEqualsWithDelta(5.0, $m[2][0], 1e-12);
        $this->assertEqualsWithDelta(2.0, $m[2][1], 1e-12);
    }

    public function test_matriz_perfectamente_consistente_tiene_cr_cero()
    {
        // w = (0,5; 0,3; 0,2): aij = wi / wj
        $m = AHP::matrizDesdeJuicios(3, [5 / 3, 2.5, 1.5]);
        $r = AHP::analizar($m);

        $this->assertEqualsWithDelta(0.5, $r['pesos'][0], 1e-9);
        $this->assertEqualsWithDelta(0.0, $r['cr'], 1e-9);
    }

    public function test_matriz_inconsistente_supera_el_umbral()
    {
        // A > B, B > C, pero C > A con fuerza: juicios contradictorios.
        $m = AHP::matrizDesdeJuicios(3, [3, '1/7', 5]);
        $r = AHP::analizar($m);

        $this->assertFalse($r['consistente']);
        $this->assertNotEmpty(AHP::comparacionesMasInconsistentes($m, $r['pesos']));
    }

    public function test_media_geometrica_preserva_la_reciprocidad()
    {
        $a = AHP::matrizDesdeJuicios(2, [3]);
        $b = AHP::matrizDesdeJuicios(2, [5]);
        $g = AHP::agregarGeometrica([$a, $b]);

        $this->assertEqualsWithDelta(sqrt(15), $g[0][1], 1e-12);
        $this->assertEqualsWithDelta(1 / sqrt(15), $g[1][0], 1e-12);
    }

    public function test_juicio_fuera_de_escala_se_rechaza()
    {
        $this->expectException(\InvalidArgumentException::class);
        AHP::interpretarJuicio(12);
    }

    public function test_variar_bloque_redistribuye_y_conserva_la_suma()
    {
        $pesos = ['a' => 0.5, 'b' => 0.3, 'c' => 0.2];
        $v = AnalisisSensibilidad::variarBloque($pesos, 'a', 0.20);

        $this->assertEqualsWithDelta(0.6, $v['a'], 1e-12);
        $this->assertEqualsWithDelta(1.0, array_sum($v), 1e-12);
        $this->assertEqualsWithDelta(0.3 / 0.2, $v['b'] / $v['c'], 1e-12);
    }

    public function test_spearman()
    {
        $this->assertEqualsWithDelta(1.0, AnalisisSensibilidad::spearman([1, 2, 3], [10, 20, 30]), 1e-12);
        $this->assertEqualsWithDelta(-1.0, AnalisisSensibilidad::spearman([1, 2, 3], [3, 2, 1]), 1e-12);
    }
}
