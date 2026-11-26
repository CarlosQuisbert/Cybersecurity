<?php

namespace Database\Seeders;

use App\Models\Incidente;
use App\Models\TipoIncidente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Categorías de incidente del alcance de la tesis (apartado 7.1) e incidentes de ejemplo.
 */
class IncidenteSeeder extends Seeder
{
    /** Categorías anteriores y la categoría de la tesis a la que corresponden. */
    private const EQUIVALENCIAS = [
        'Phishing' => 'Suplantación de identidad e ingeniería social',
        'Ingeniería social' => 'Suplantación de identidad e ingeniería social',
        'Malware' => 'Código malicioso',
        'Ransomware' => 'Código malicioso',
        'Denegación de servicio (DoS/DDoS)' => 'Afectación de la disponibilidad',
        'Fuga de datos' => 'Compromiso o fuga de información',
        'Vulnerabilidad' => 'Vulnerabilidad detectada',
    ];

    public function run()
    {
        $categorias = [
            ['nombre' => 'Suplantación de identidad e ingeniería social', 'descripcion' => 'Phishing, fraude por suplantación y manipulación de personas para obtener acceso o información.'],
            ['nombre' => 'Código malicioso', 'descripcion' => 'Virus, troyanos, ransomware y otro software malicioso.'],
            ['nombre' => 'Acceso no autorizado', 'descripcion' => 'Intentos o accesos exitosos a sistemas o información sin autorización.'],
            ['nombre' => 'Compromiso o fuga de información', 'descripcion' => 'Exposición, alteración o exfiltración de información.'],
            ['nombre' => 'Afectación de la disponibilidad', 'descripcion' => 'Pérdida o degradación de servicios: denegación de servicio, fallas de software o hardware, errores.'],
            ['nombre' => 'Uso inapropiado de recursos', 'descripcion' => 'Mal uso de los sistemas de información o incumplimiento de políticas.'],
            ['nombre' => 'Vulnerabilidad detectada', 'descripcion' => 'Debilidad técnica identificada sin explotación confirmada.'],
        ];

        foreach ($categorias as $c) {
            TipoIncidente::updateOrCreate(
                ['nombre' => $c['nombre']],
                ['slug' => Str::slug($c['nombre']), 'descripcion' => $c['descripcion'], 'activo' => true]
            );
        }

        // Las categorías anteriores se unifican con las de la tesis.
        foreach (self::EQUIVALENCIAS as $anterior => $nueva) {
            $tipoAnterior = TipoIncidente::where('nombre', $anterior)->first();
            if (! $tipoAnterior) continue;
            $idNueva = TipoIncidente::where('nombre', $nueva)->value('id');
            Incidente::where('tipo_incidente_id', $tipoAnterior->id)->update(['tipo_incidente_id' => $idNueva]);
            $tipoAnterior->delete();
        }

        if (Incidente::count() > 0) {
            return;
        }

        $tipo = TipoIncidente::pluck('id', 'nombre');
        $ejemplos = [
            ['Phishing masivo a cuentas institucionales', 'Suplantación de identidad e ingeniería social', 'adversario'],
            ['Ransomware en estación de trabajo', 'Código malicioso', 'adversario'],
            ['Acceso anómalo a servidor de archivos', 'Acceso no autorizado', 'adversario'],
            ['Caída de portal por falla de hardware', 'Afectación de la disponibilidad', 'no_adversario'],
            ['Posible fuga de base de datos', 'Compromiso o fuga de información', 'adversario'],
            ['Vulnerabilidad crítica en servidor web', 'Vulnerabilidad detectada', 'no_adversario'],
        ];

        foreach ($ejemplos as $i => [$titulo, $categoria, $origen]) {
            Incidente::create([
                'titulo' => $titulo,
                'descripcion' => 'Incidente de ejemplo generado por el seeder para pruebas del sistema.',
                'tipo_incidente_id' => $tipo[$categoria],
                'calificacion' => 'incidente',
                'origen' => $origen,
                'estado' => 'abierto',
                'reportado_por' => 'Mesa de ayuda',
                'detectado_en' => now()->subDays($i + 1),
            ]);
        }
    }
}
