<?php

namespace Tests\Unit;

use App\Servicios\MotorRiesgo;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias del motor de cálculo (Tabla del apartado 3.3.3.1 de la tesis).
 */
class MotorRiesgoTest extends TestCase
{
    /** Pesos globales del panel (apartado 3.3.1.3). */
    private function pesos(): array
    {
        $bloques = ['impacto' => 0.435, 'activo_alcance' => 0.242, 'evolucion' => 0.200, 'respuesta_cumplimiento' => 0.124];
        $locales = [
            'impacto' => ['confidencialidad' => 0.377, 'integridad' => 0.209, 'disponibilidad' => 0.413],
            'activo_alcance' => ['criticidad_activo' => 0.535, 'alcance' => 0.465],
            'evolucion' => ['progresion_adversaria' => 0.635, 'probabilidad_propagacion' => 0.365],
            'respuesta_cumplimiento' => ['esfuerzo_recuperacion' => 0.378, 'exigencia_normativa' => 0.622],
        ];

        return \App\Servicios\AnalisisSensibilidad::pesosGlobales($bloques, $locales);
    }

    private function umbrales(): array
    {
        return ['critico' => 75, 'alto' => 50, 'medio' => 25];
    }

    private function todos(int $nivel): array
    {
        return array_fill_keys(array_keys($this->pesos()), $nivel);
    }

    public function test_normalizacion_de_niveles_1_3_y_5()
    {
        $this->assertSame(0.0, MotorRiesgo::normalizar(1));
        $this->assertSame(0.5, MotorRiesgo::normalizar(3));
        $this->assertSame(1.0, MotorRiesgo::normalizar(5));
    }

    public function test_nivel_fuera_de_escala_se_rechaza()
    {
        $this->expectException(InvalidArgumentException::class);
        MotorRiesgo::normalizar(6);
    }

    public function test_los_nueve_pesos_suman_uno()
    {
        $pesos = $this->pesos();
        $this->assertCount(9, $pesos);
        $this->assertEqualsWithDelta(1.0, array_sum($pesos), 1e-9);
        // Coinciden con la tabla de pesos globales de la tesis.
        $this->assertEqualsWithDelta(0.180, $pesos['disponibilidad'], 0.001);
        $this->assertEqualsWithDelta(0.164, $pesos['confidencialidad'], 0.001);
        $this->assertEqualsWithDelta(0.047, $pesos['esfuerzo_recuperacion'], 0.001);
    }

    public function test_todos_los_criterios_en_nivel_1_dan_indice_0()
    {
        $r = MotorRiesgo::calcular($this->pesos(), $this->todos(1), true, $this->umbrales());
        $this->assertSame(0.0, $r['indice']);
        $this->assertSame('bajo', $r['nivel_prioridad']);
    }

    public function test_todos_los_criterios_en_nivel_5_dan_indice_100()
    {
        $r = MotorRiesgo::calcular($this->pesos(), $this->todos(5), true, $this->umbrales());
        $this->assertSame(100.0, $r['indice']);
        $this->assertSame('critico', $r['nivel_prioridad']);
    }

    public function test_sin_origen_adversario_se_excluye_la_progresion_y_los_ocho_pesos_suman_1()
    {
        $niveles = $this->todos(5);
        unset($niveles['progresion_adversaria']);

        $r = MotorRiesgo::calcular($this->pesos(), $niveles, false, $this->umbrales());

        $this->assertSame(['progresion_adversaria'], $r['excluidos']);
        $this->assertCount(8, $r['pesos_aplicados']);
        $this->assertEqualsWithDelta(1.0, array_sum($r['pesos_aplicados']), 1e-9);
        $this->assertSame(100.0, $r['indice']);
        // Redistribución proporcional: cada peso se divide por (1 − peso de la progresión).
        $p = $this->pesos();
        $this->assertEqualsWithDelta($p['disponibilidad'] / (1 - $p['progresion_adversaria']), $r['pesos_aplicados']['disponibilidad'], 1e-9);
    }

    public function test_falta_un_criterio_requerido()
    {
        $this->expectException(InvalidArgumentException::class);
        $niveles = $this->todos(3);
        unset($niveles['progresion_adversaria']);
        MotorRiesgo::calcular($this->pesos(), $niveles, true, $this->umbrales());
    }

    public function test_indice_es_la_suma_ponderada()
    {
        $niveles = $this->todos(1);
        $niveles['disponibilidad'] = 5; // valor 1
        $niveles['confidencialidad'] = 3; // valor 0,5
        $p = $this->pesos();

        $r = MotorRiesgo::calcular($p, $niveles, true, $this->umbrales());

        $this->assertEqualsWithDelta(round(100 * ($p['disponibilidad'] + 0.5 * $p['confidencialidad']), 2), $r['indice'], 1e-9);
    }

    public function test_alcance_toma_el_mayor_de_usuarios_y_sistemas()
    {
        $this->assertSame(4, MotorRiesgo::combinar([2, 4], 'maximo'));
        $this->assertSame(3, MotorRiesgo::combinar([3]));
    }

    public function test_umbrales_asignan_el_nivel_de_prioridad()
    {
        $u = $this->umbrales();
        $this->assertSame('critico', MotorRiesgo::nivelPrioridad(75, $u));
        $this->assertSame('alto', MotorRiesgo::nivelPrioridad(74.99, $u));
        $this->assertSame('medio', MotorRiesgo::nivelPrioridad(25, $u));
        $this->assertSame('bajo', MotorRiesgo::nivelPrioridad(24.99, $u));
    }

    public function test_umbrales_incoherentes_se_rechazan()
    {
        $this->expectException(InvalidArgumentException::class);
        MotorRiesgo::nivelPrioridad(50, ['critico' => 40, 'alto' => 50, 'medio' => 25]);
    }

    public function test_empate_se_resuelve_por_mayor_progresion_y_despues_por_antiguedad()
    {
        $cola = [
            'nuevo_progresion_baja' => ['indice' => 60.0, 'progresion' => 2, 'antiguedad' => 200],
            'antiguo_progresion_alta' => ['indice' => 60.0, 'progresion' => 4, 'antiguedad' => 100],
            'nuevo_progresion_alta' => ['indice' => 60.0, 'progresion' => 4, 'antiguedad' => 300],
            'indice_mayor' => ['indice' => 61.0, 'progresion' => 1, 'antiguedad' => 400],
            'sin_evaluar' => ['indice' => null, 'progresion' => null, 'antiguedad' => 50],
        ];
        uasort($cola, [MotorRiesgo::class, 'comparar']);

        $this->assertSame(
            ['indice_mayor', 'antiguo_progresion_alta', 'nuevo_progresion_alta', 'nuevo_progresion_baja', 'sin_evaluar'],
            array_keys($cola)
        );
    }
}
