<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\BloqueCriterio;
use App\Models\ConfiguracionModelo;
use App\Models\Criterio;
use App\Models\Escala;
use App\Models\Tactica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Carga el modelo de priorización tal como lo define la tesis:
 * jerarquía (3.3.1.2), criterios y trazabilidad (Tabla 3.2), escalas (Tablas 3.3 a 3.12),
 * catálogo de tácticas (Tabla 2.6) y pesos del panel de expertos (3.3.1.3).
 */
class ModeloPriorizacionSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $this->cargarJerarquia();
            $this->cargarTacticas();
            $this->cargarConfiguracionInicial();
            $this->cargarInventarioDeEjemplo();
        });
    }

    private function cargarJerarquia(): void
    {
        $bloques = [
            ['codigo' => 'impacto', 'nombre' => 'Impacto', 'orden' => 1],
            ['codigo' => 'activo_alcance', 'nombre' => 'Activo y alcance', 'orden' => 2],
            ['codigo' => 'evolucion', 'nombre' => 'Evolución', 'orden' => 3],
            ['codigo' => 'respuesta_cumplimiento', 'nombre' => 'Respuesta y cumplimiento', 'orden' => 4],
        ];
        foreach ($bloques as $b) {
            BloqueCriterio::updateOrCreate(['codigo' => $b['codigo']], $b);
        }
        $bloque = BloqueCriterio::pluck('id', 'codigo');

        foreach ($this->criterios() as $orden => $c) {
            $criterio = Criterio::updateOrCreate(['codigo' => $c['codigo']], [
                'bloque_id' => $bloque[$c['bloque']],
                'nombre' => $c['nombre'],
                'descripcion' => $c['descripcion'] ?? null,
                'fundamento' => $c['fundamento'],
                'fuente' => $c['fuente'] ?? Criterio::FUENTE_ANALISTA,
                'combinacion' => $c['combinacion'] ?? 'unica',
                'solo_adversario' => $c['solo_adversario'] ?? false,
                'orden' => $orden + 1,
            ]);

            foreach ($c['escalas'] as $ordenEscala => $e) {
                $escala = Escala::updateOrCreate(['codigo' => $e['codigo']], [
                    'criterio_id' => $criterio->id,
                    'nombre' => $e['nombre'],
                    'nota' => $e['nota'] ?? null,
                    'orden' => $ordenEscala + 1,
                ]);
                foreach ($e['niveles'] as $i => $descriptor) {
                    // Solo se crea si no existe: los descriptores editados desde el panel se respetan.
                    $escala->niveles()->firstOrCreate(['nivel' => $i + 1], ['descriptor' => $descriptor]);
                }
            }
        }
    }

    private function criterios(): array
    {
        return [
            [
                'codigo' => 'confidencialidad', 'bloque' => 'impacto', 'nombre' => 'Confidencialidad',
                'fundamento' => 'Dimensiones de seguridad de la ISO (2022b); impacto sobre la información del NIST.',
                'escalas' => [[
                    'codigo' => 'confidencialidad', 'nombre' => 'Impacto sobre la confidencialidad',
                    'niveles' => [
                        'No se verifica exposición de información',
                        'Exposición de información de carácter público o de circulación interna irrestricta',
                        'Exposición de información de uso interno a personas no autorizadas dentro de la organización',
                        'Exposición de información reservada o de datos personales de un número acotado de titulares',
                        'Exposición de información reservada o de datos personales a terceros externos, o evidencia de publicación fuera de la organización',
                    ],
                ]],
            ],
            [
                'codigo' => 'integridad', 'bloque' => 'impacto', 'nombre' => 'Integridad',
                'fundamento' => 'Dimensiones de seguridad de la ISO (2022b).',
                'escalas' => [[
                    'codigo' => 'integridad', 'nombre' => 'Impacto sobre la integridad',
                    'niveles' => [
                        'No se verifica alteración de información',
                        'Alteración detectada y revertida por los controles automáticos',
                        'Alteración de registros no críticos, reversible a partir de respaldos vigentes',
                        'Alteración de registros críticos o transaccionales, reversible con esfuerzo significativo',
                        'Alteración de registros críticos sin posibilidad de verificar el estado original',
                    ],
                ]],
            ],
            [
                'codigo' => 'disponibilidad', 'bloque' => 'impacto', 'nombre' => 'Disponibilidad',
                'fundamento' => 'Dimensiones de seguridad de la ISO (2022b); impacto funcional del NIST.',
                'escalas' => [[
                    'codigo' => 'disponibilidad', 'nombre' => 'Impacto sobre la disponibilidad',
                    'niveles' => [
                        'No se verifica afectación del servicio',
                        'Degradación perceptible del rendimiento sin interrupción del servicio',
                        'Interrupción de un servicio no crítico',
                        'Interrupción de un servicio crítico dentro del tiempo de tolerancia definido',
                        'Interrupción de un servicio crítico excediendo el tiempo de tolerancia definido',
                    ],
                ]],
            ],
            [
                'codigo' => 'criticidad_activo', 'bloque' => 'activo_alcance', 'nombre' => 'Criticidad del activo',
                'fuente' => Criterio::FUENTE_INVENTARIO,
                'descripcion' => 'El nivel no lo asigna el analista: se obtiene del inventario de activos valorado. Si el activo no figura en el inventario se asigna el nivel mínimo y se registra la omisión.',
                'fundamento' => 'Valoración de activos del Ministerio de Hacienda y Administraciones Públicas (2012, MAGERIT v3); control de inventario de activos de la ISO (2022a).',
                'escalas' => [[
                    'codigo' => 'criticidad_activo', 'nombre' => 'Criticidad del activo afectado',
                    'niveles' => [
                        'Activo no inventariado o de valor despreciable para la organización',
                        'Estación de trabajo o activo de uso individual sin información institucional almacenada',
                        'Activo de alcance departamental o servicio de área',
                        'Activo institucional de soporte, cuya indisponibilidad afecta a varias áreas',
                        'Activo institucional crítico, o que sostiene un proceso definido como crítico por la organización',
                    ],
                ]],
            ],
            [
                'codigo' => 'alcance', 'bloque' => 'activo_alcance', 'nombre' => 'Alcance',
                'combinacion' => 'maximo',
                'descripcion' => 'Se valora en usuarios y en sistemas afectados; el nivel del criterio es el mayor de los dos. El alcance en usuarios mide las personas alcanzadas con independencia de si el servicio se interrumpió.',
                'fundamento' => 'Evidencia de Hore et al. (2023) sobre la dependencia contextual de la severidad.',
                'escalas' => [
                    [
                        'codigo' => 'alcance_usuarios', 'nombre' => 'Alcance: usuarios afectados',
                        'nota' => 'Cortes calibrados para una organización de dotación media; ajustar según el apartado 3.1.1.',
                        'niveles' => [
                            'Ningún usuario afectado, o un único usuario',
                            'Entre dos y diez usuarios',
                            'Entre once y cincuenta usuarios, o una unidad organizacional completa',
                            'Más de cincuenta usuarios, o varias unidades organizacionales',
                            'La totalidad de la organización, o usuarios externos y clientes',
                        ],
                    ],
                    [
                        'codigo' => 'alcance_sistemas', 'nombre' => 'Alcance: sistemas afectados',
                        'niveles' => [
                            'Ningún sistema comprometido',
                            'Un componente aislado de un sistema',
                            'Un sistema de información completo',
                            'Varios sistemas pertenecientes a un mismo dominio funcional',
                            'Infraestructura transversal: directorio de identidades, red, virtualización o respaldos',
                        ],
                    ],
                ],
            ],
            [
                'codigo' => 'progresion_adversaria', 'bloque' => 'evolucion', 'nombre' => 'Progresión adversaria',
                'fuente' => Criterio::FUENTE_TACTICAS, 'solo_adversario' => true,
                'descripcion' => 'Nivel de la táctica más avanzada evidenciada. En incidentes sin origen adversario el criterio se excluye y su peso se redistribuye entre los restantes.',
                'fundamento' => 'Principio establecido por Sadlek et al. (2025); catálogo de la MITRE Corporation (2026).',
                'escalas' => [[
                    'codigo' => 'progresion_adversaria', 'nombre' => 'Progresión adversaria',
                    'niveles' => [
                        'Preparación previa: reconocimiento; desarrollo de recursos',
                        'Acceso inicial: acceso inicial; ejecución',
                        'Consolidación: persistencia; escalada de privilegios; evasión de defensas; acceso a credenciales; descubrimiento',
                        'Expansión: movimiento lateral; recolección; mando y control',
                        'Consumación: exfiltración; impacto',
                    ],
                ]],
            ],
            [
                'codigo' => 'probabilidad_propagacion', 'bloque' => 'evolucion', 'nombre' => 'Probabilidad de propagación',
                'fundamento' => 'Componente de probabilidad de la ISO (2022b), adaptado a incidente materializado.',
                'escalas' => [[
                    'codigo' => 'probabilidad_propagacion', 'nombre' => 'Probabilidad de propagación',
                    'niveles' => [
                        'Incidente contenido, sin vector de propagación identificado',
                        'Vector de propagación existente pero neutralizado por controles vigentes',
                        'Propagación posible, con controles que la mitigan parcialmente',
                        'Propagación probable, con evidencia de intentos en curso',
                        'Propagación confirmada o en curso al momento de la evaluación',
                    ],
                ]],
            ],
            [
                'codigo' => 'esfuerzo_recuperacion', 'bloque' => 'respuesta_cumplimiento', 'nombre' => 'Esfuerzo de recuperación',
                'fundamento' => 'Criterio de recuperabilidad del NIST.',
                'escalas' => [[
                    'codigo' => 'esfuerzo_recuperacion', 'nombre' => 'Esfuerzo de recuperación',
                    'niveles' => [
                        'Recuperación predecible con recursos propios, en el orden de horas',
                        'Recuperación que requiere recursos internos adicionales, en el orden de un día',
                        'Recuperación predecible pero prolongada, en el orden de varios días',
                        'Recuperación que requiere intervención de terceros o reconstrucción de componentes',
                        'Pérdida definitiva de información, o necesidad de preservar evidencia que impide la recuperación inmediata',
                    ],
                ]],
            ],
            [
                'codigo' => 'exigencia_normativa', 'bloque' => 'respuesta_cumplimiento', 'nombre' => 'Exigencia normativa',
                'fundamento' => 'Decreto Supremo N° 2514 y Reglamento de la ASFI (2016), según sector.',
                'escalas' => [[
                    'codigo' => 'exigencia_normativa', 'nombre' => 'Exigencia normativa',
                    'nota' => 'Sector financiero (ASFI): el nivel 4 corresponde a los incidentes cuya omisión en el registro constituiría una observación de auditoría. Sector público (D.S. 2514): el nivel 4 se activa con la obligación de reporte en 24 horas.',
                    'niveles' => [
                        'Sin implicancia regulatoria',
                        'Debe constar en el registro de incidentes conforme a la clasificación exigida',
                        'Debe integrar el reporte periódico al regulador o el informe de auditoría interna',
                        'Requiere notificación al organismo de control dentro de un plazo determinado',
                        'Plazo de notificación en curso de vencimiento, o exigencia de comunicación a terceros afectados',
                    ],
                ]],
            ],
        ];
    }

    /**
     * Tácticas de la Tabla 2.6 con su nivel de progresión (escala j).
     * La versión 19 divide la evasión de defensas en dos tácticas: la segunda
     * puede agregarse desde el catálogo una vez verificada en la fuente.
     */
    private function cargarTacticas(): void
    {
        $tacticas = [
            ['TA0043', 'Reconocimiento', 'Recolectar información para planificar operaciones futuras', 1],
            ['TA0042', 'Desarrollo de recursos', 'Establecer los recursos necesarios para la operación', 1],
            ['TA0001', 'Acceso inicial', 'Ingresar al entorno objetivo', 2],
            ['TA0002', 'Ejecución', 'Ejecutar código malicioso en el sistema comprometido', 2],
            ['TA0003', 'Persistencia', 'Mantener el acceso pese a reinicios o cambios de credenciales', 3],
            ['TA0004', 'Escalada de privilegios', 'Obtener permisos de mayor nivel', 3],
            ['TA0005', 'Evasión de defensas', 'Evitar la detección por los controles existentes', 3],
            ['TA0006', 'Acceso a credenciales', 'Obtener nombres de cuenta y contraseñas', 3],
            ['TA0007', 'Descubrimiento', 'Conocer el entorno y sus componentes', 3],
            ['TA0008', 'Movimiento lateral', 'Desplazarse hacia otros sistemas del entorno', 4],
            ['TA0009', 'Recolección', 'Reunir la información de interés para el objetivo', 4],
            ['TA0011', 'Mando y control', 'Comunicarse con los sistemas comprometidos', 4],
            ['TA0010', 'Exfiltración', 'Extraer la información del entorno', 5],
            ['TA0040', 'Impacto', 'Manipular, interrumpir o destruir sistemas e información', 5],
        ];

        foreach ($tacticas as $orden => [$id, $nombre, $objetivo, $nivel]) {
            Tactica::firstOrCreate(['identificador' => $id], [
                'nombre' => $nombre,
                'objetivo' => $objetivo,
                'nivel_progresion' => $nivel,
                'orden' => $orden + 1,
                'activo' => true,
            ]);
        }
    }

    /**
     * Versión 1: pesos del panel de cinco expertos (agregación por media geométrica).
     * Los umbrales son provisionales hasta completar el apartado 3.3.1.6.
     */
    private function cargarConfiguracionInicial(): void
    {
        if (ConfiguracionModelo::exists()) return;

        $configuracion = ConfiguracionModelo::create([
            'version' => 1,
            'descripcion' => 'Pesos del panel de cinco expertos (apartado 3.3.1.3). Umbrales PROVISIONALES por cuartiles del índice: reemplazar por los obtenidos en el apartado 3.3.1.6.',
            'version_attack' => 'Enterprise v19 (abril 2026)',
            'umbral_critico' => 75,
            'umbral_alto' => 50,
            'umbral_medio' => 25,
            'capacidad_criticos' => 3,
            'vigente' => true,
            'origen_pesos' => [
                'metodo' => 'AHP, agregación de juicios individuales por media geométrica',
                'expertos' => 5,
                'cr_bloques' => 0.004,
                'cr_impacto' => 0.008,
            ],
        ]);

        $pesosBloque = ['impacto' => 0.435, 'activo_alcance' => 0.242, 'evolucion' => 0.200, 'respuesta_cumplimiento' => 0.124];
        foreach ($pesosBloque as $codigo => $peso) {
            $configuracion->pesosBloque()->create([
                'bloque_id' => BloqueCriterio::where('codigo', $codigo)->value('id'),
                'peso' => $peso,
            ]);
        }

        $pesosLocales = [
            'confidencialidad' => 0.377, 'integridad' => 0.209, 'disponibilidad' => 0.413,
            'criticidad_activo' => 0.535, 'alcance' => 0.465,
            'progresion_adversaria' => 0.635, 'probabilidad_propagacion' => 0.365,
            'esfuerzo_recuperacion' => 0.378, 'exigencia_normativa' => 0.622,
        ];
        foreach ($pesosLocales as $codigo => $peso) {
            $configuracion->pesosCriterio()->create([
                'criterio_id' => Criterio::where('codigo', $codigo)->value('id'),
                'peso_local' => $peso,
            ]);
        }
    }

    private function cargarInventarioDeEjemplo(): void
    {
        if (Activo::exists()) return;

        $activos = [
            ['ACT-001', 'Core bancario', 'Aplicación', 'Tecnología', 5],
            ['ACT-002', 'Banca por internet', 'Aplicación', 'Canales digitales', 5],
            ['ACT-003', 'Directorio de identidades', 'Infraestructura', 'Tecnología', 5],
            ['ACT-004', 'Servidor de correo electrónico', 'Servidor', 'Tecnología', 4],
            ['ACT-005', 'Servidor de archivos compartidos', 'Servidor', 'Tecnología', 3],
            ['ACT-006', 'Portal web institucional', 'Aplicación', 'Comunicación', 3],
            ['ACT-007', 'Estación de trabajo de agencia', 'Equipo de usuario', 'Agencias', 2],
        ];

        foreach ($activos as [$codigo, $nombre, $tipo, $area, $criticidad]) {
            Activo::create([
                'codigo' => $codigo,
                'nombre' => $nombre,
                'tipo' => $tipo,
                'area' => $area,
                'criticidad' => $criticidad,
                'descripcion' => 'Activo de ejemplo: reemplazar por el inventario valorado de la organización.',
                'activo' => true,
            ]);
        }
    }
}
