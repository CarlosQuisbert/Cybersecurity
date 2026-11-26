<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\ConfiguracionModelo;
use App\Models\Criterio;
use App\Models\Evaluacion;
use App\Models\Incidente;
use App\Models\RegistroBitacora;
use App\Models\Tactica;
use App\Models\TipoIncidente;
use App\Models\Usuario;
use App\Servicios\MotorRiesgo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pruebas de integración del prototipo (apartado 3.3.3.2 de la tesis).
 * Requieren la base de datos migrada y con los seeders del modelo; cada prueba
 * se ejecuta dentro de una transacción que se revierte al terminar.
 */
class PrototipoTest extends TestCase
{
    use DatabaseTransactions;

    private function usuario(string $rol, string $contrasena = 'secreto123'): Usuario
    {
        $usuario = Usuario::create([
            'nombre' => 'Prueba ' . $rol,
            'usuario' => 'prueba.' . uniqid(),
            'identificacion' => (string) random_int(10000000, 99999999),
            'contrasena' => bcrypt($contrasena),
        ]);
        $usuario->assignRole($rol);

        return $usuario;
    }

    private function datosIncidente(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'Exfiltración de base de clientes',
            'descripcion' => 'Prueba de integración',
            'tipo_incidente_id' => TipoIncidente::where('nombre', 'Compromiso o fuga de información')->value('id'),
            'calificacion' => 'incidente',
            'origen' => 'adversario',
            'tacticas' => [Tactica::where('identificador', 'TA0010')->value('id')],
            'activo_id' => Activo::where('criticidad', 5)->value('id'),
        ], $extra);
    }

    /** Escalas que valora el analista, todas en el mismo nivel. */
    private function valoraciones(int $nivel): array
    {
        $valores = [];
        foreach (Criterio::with('escalas')->where('fuente', 'analista')->get() as $criterio) {
            foreach ($criterio->escalas as $escala) $valores[$escala->id] = $nivel;
        }

        return $valores;
    }

    public function test_iniciar_sesion_con_credenciales_validas_e_invalidas()
    {
        $usuario = $this->usuario('Analista', 'clave-correcta');

        $this->post('/iniciar-sesion', ['usuario' => $usuario->usuario, 'contrasena' => 'clave-incorrecta'])
            ->assertSessionHasErrors('usuario');
        $this->assertGuest('admin');

        $this->post('/iniciar-sesion', ['usuario' => $usuario->usuario, 'contrasena' => 'clave-correcta'])
            ->assertRedirect('/panel/inicio');
        $this->assertAuthenticatedAs($usuario, 'admin');
        $this->assertTrue(RegistroBitacora::where('accion', 'sesion.iniciar')->where('usuario_id', $usuario->id)->exists());
    }

    public function test_registrar_un_incidente_y_consultarlo()
    {
        $analista = $this->usuario('Analista');

        $id = $this->actingAs($analista, 'admin')->postJson('/panel/incidentes', $this->datosIncidente())
            ->assertCreated()
            ->assertJsonPath('origen', 'adversario')
            ->json('id');

        $this->getJson('/panel/incidentes/' . $id)
            ->assertOk()
            ->assertJsonPath('titulo', 'Exfiltración de base de clientes')
            ->assertJsonPath('tacticas.0.identificador', 'TA0010')
            ->assertJsonPath('estado', 'abierto');
    }

    public function test_enviar_la_valoracion_devuelve_el_indice_y_el_nivel()
    {
        $analista = $this->usuario('Analista');
        $incidente = Incidente::create(collect($this->datosIncidente())->except('tacticas')->all());
        $incidente->tacticas()->sync($this->datosIncidente()['tacticas']);

        // Todas las escalas del analista en 5, activo crítico y exfiltración (nivel 5): índice 100.
        $this->actingAs($analista, 'admin')
            ->postJson("/panel/incidentes/{$incidente->id}/evaluaciones", ['valoraciones' => $this->valoraciones(5)])
            ->assertCreated()
            ->assertJsonPath('evaluacion.indice_riesgo', 100)
            ->assertJsonPath('evaluacion.nivel_prioridad', 'critico')
            ->assertJsonPath('incidente.nivel_prioridad', 'critico');

        // Todo en 1 salvo los datos del inventario y la táctica, calculado con el motor.
        $respuesta = $this->postJson("/panel/incidentes/{$incidente->id}/evaluaciones", ['valoraciones' => $this->valoraciones(1)])->assertCreated();
        $pesos = ConfiguracionModelo::vigente()->pesosGlobales();
        $esperado = round(100 * ($pesos['criticidad_activo'] + $pesos['progresion_adversaria']), 2);
        $this->assertEqualsWithDelta($esperado, $respuesta->json('evaluacion.indice_riesgo'), 0.01);
    }

    public function test_incidente_sin_origen_adversario_excluye_la_progresion()
    {
        $analista = $this->usuario('Analista');
        $incidente = Incidente::create($this->datosIncidente(['origen' => 'no_adversario', 'activo_id' => null, 'tacticas' => null]) + []);

        $evaluacion = $this->actingAs($analista, 'admin')
            ->postJson("/panel/incidentes/{$incidente->id}/evaluaciones", ['valoraciones' => $this->valoraciones(5)])
            ->assertCreated()
            ->json('evaluacion');

        $this->assertTrue($evaluacion['progresion_excluida']);
        $this->assertTrue($evaluacion['omision_inventario']);
        $this->assertSame('excluido', $evaluacion['detalle']['criterios']['progresion_adversaria']['fuente']);
        $pesos = collect($evaluacion['detalle']['criterios'])->pluck('peso_aplicado')->filter()->sum();
        $this->assertEqualsWithDelta(1.0, $pesos, 0.0001);
    }

    public function test_falta_una_valoracion_se_rechaza()
    {
        $analista = $this->usuario('Analista');
        $incidente = Incidente::create($this->datosIncidente(['tacticas' => null]));
        $incidente->tacticas()->sync($this->datosIncidente()['tacticas']);
        $valores = $this->valoraciones(3);
        array_pop($valores);

        $this->actingAs($analista, 'admin')
            ->postJson("/panel/incidentes/{$incidente->id}/evaluaciones", ['valoraciones' => $valores])
            ->assertStatus(422);
    }

    public function test_acceder_a_una_funcion_de_otro_rol_es_denegado()
    {
        $analista = $this->usuario('Analista');
        $reportante = $this->usuario('Reportante');
        $incidente = Incidente::create($this->datosIncidente(['tacticas' => null]));

        $this->actingAs($analista, 'admin')->get('/panel/modelo')->assertForbidden();
        $this->actingAs($analista, 'admin')->putJson("/panel/incidentes/{$incidente->id}/seguimiento", ['estado' => 'cerrado'])->assertForbidden();
        $this->actingAs($reportante, 'admin')->postJson("/panel/incidentes/{$incidente->id}/evaluaciones", ['valoraciones' => [1 => 1]])->assertForbidden();
        $this->actingAs($reportante, 'admin')->get('/panel/usuarios')->assertForbidden();
    }

    public function test_cambiar_el_estado_registra_usuario_y_fecha()
    {
        $responsable = $this->usuario('Responsable de seguridad');
        $incidente = Incidente::create($this->datosIncidente(['tacticas' => null]));

        $this->actingAs($responsable, 'admin')
            ->putJson("/panel/incidentes/{$incidente->id}/seguimiento", ['estado' => 'contenido', 'asignado_a' => $responsable->id, 'notas' => 'Cuenta bloqueada'])
            ->assertOk();

        $historial = $incidente->historial()->first();
        $this->assertSame('abierto', $historial->estado_anterior);
        $this->assertSame('contenido', $historial->estado_nuevo);
        $this->assertSame($responsable->id, $historial->cambiado_por);
        $this->assertNotNull($historial->creado_en);
        $this->assertNotNull($incidente->fresh()->contenido_en);
    }

    public function test_cambiar_un_peso_usa_el_nuevo_peso_y_conserva_la_version_anterior()
    {
        $administrador = $this->usuario('Administrador');
        $incidente = Incidente::create($this->datosIncidente(['tacticas' => null]));
        $incidente->tacticas()->sync($this->datosIncidente()['tacticas']);
        $anterior = ConfiguracionModelo::vigente();

        $valores = $this->valoraciones(1);
        $disponibilidad = Criterio::where('codigo', 'disponibilidad')->first()->escalas->first()->id;
        $valores[$disponibilidad] = 5;
        $this->actingAs($administrador, 'admin')
            ->postJson("/panel/incidentes/{$incidente->id}/evaluaciones", ['valoraciones' => $valores])->assertCreated();
        $indiceAnterior = $incidente->fresh()->indice_riesgo;

        // Nueva versión: el bloque de impacto pasa a pesar más.
        $pesosBloque = $anterior->pesosBloque->pluck('peso', 'bloque_id')->map(fn ($p) => (float) $p)->all();
        $impacto = \App\Models\BloqueCriterio::where('codigo', 'impacto')->value('id');
        $pesosBloque = \App\Servicios\AnalisisSensibilidad::variarBloque($pesosBloque, $impacto, 0.20);

        $this->postJson('/panel/modelo/configuraciones', [
            'version_attack' => $anterior->version_attack,
            'umbral_critico' => 75, 'umbral_alto' => 50, 'umbral_medio' => 25,
            'capacidad_criticos' => 3,
            'pesos_bloque' => $pesosBloque,
            'pesos_criterio' => $anterior->pesosCriterio->pluck('peso_local', 'criterio_id')->map(fn ($p) => (float) $p)->all(),
            'recalcular' => true,
        ])->assertCreated();

        $nueva = ConfiguracionModelo::vigente();
        $this->assertSame($anterior->version + 1, $nueva->version);
        $this->assertFalse($anterior->fresh()->vigente);

        $evaluaciones = Evaluacion::where('incidente_id', $incidente->id)->orderBy('id')->get();
        $this->assertCount(2, $evaluaciones);
        $this->assertSame($anterior->id, $evaluaciones[0]->configuracion_id);
        $this->assertSame($nueva->id, $evaluaciones[1]->configuracion_id);
        $this->assertNotEquals($indiceAnterior, $incidente->fresh()->indice_riesgo);
        $this->assertEqualsWithDelta(
            round(100 * ($nueva->pesosGlobales()['disponibilidad'] + $nueva->pesosGlobales()['criticidad_activo'] + $nueva->pesosGlobales()['progresion_adversaria']), 2),
            $incidente->fresh()->indice_riesgo, 0.01
        );
    }

    public function test_la_cola_ordena_por_indice_y_desempata_por_progresion()
    {
        $nombre = fn ($i) => 'Cola ' . $i;
        $base = ['descripcion' => 'x', 'tipo_incidente_id' => TipoIncidente::value('id'), 'calificacion' => 'incidente', 'origen' => 'adversario', 'estado' => 'abierto'];
        $a = Incidente::create($base + ['titulo' => $nombre('A'), 'indice_riesgo' => 99.98, 'progresion_nivel' => 2, 'detectado_en' => now()->subDay()]);
        $b = Incidente::create($base + ['titulo' => $nombre('B'), 'indice_riesgo' => 99.98, 'progresion_nivel' => 5, 'detectado_en' => now()]);
        $c = Incidente::create($base + ['titulo' => $nombre('C'), 'indice_riesgo' => 99.99, 'progresion_nivel' => 1, 'detectado_en' => now()]);
        Incidente::create(array_merge($base, ['titulo' => $nombre('D'), 'calificacion' => 'descartado', 'indice_riesgo' => 100]));

        $orden = \App\Http\Controllers\Panel\ColaPrioridadController::cola()
            ->filter(fn ($i) => strpos($i->titulo, 'Cola ') === 0)->pluck('titulo')->values()->all();

        $this->assertSame(['Cola C', 'Cola B', 'Cola A'], $orden);
    }

    public function test_adjuntar_evidencia_guarda_su_huella_y_queda_en_la_bitacora()
    {
        Storage::fake('local');
        $analista = $this->usuario('Analista');
        $incidente = Incidente::create($this->datosIncidente(['tacticas' => null]));
        $archivo = UploadedFile::fake()->createWithContent('registro.log', 'contenido de prueba');

        $evidencia = $this->actingAs($analista, 'admin')
            ->post("/panel/incidentes/{$incidente->id}/evidencias", ['archivo' => $archivo, 'descripcion' => 'Log del firewall'], ['Accept' => 'application/json'])
            ->assertCreated()
            ->json();

        $this->assertSame(hash('sha256', 'contenido de prueba'), $evidencia['hash_sha256']);
        $this->assertTrue(RegistroBitacora::where('accion', 'evidencia.adjuntar')->where('entidad_id', $incidente->id)->exists());

        $this->get("/panel/incidentes/{$incidente->id}/evidencias/{$evidencia['id']}/descargar")->assertOk();
        $this->assertTrue(RegistroBitacora::where('accion', 'evidencia.descargar')->where('entidad_id', $incidente->id)->exists());
    }
}
