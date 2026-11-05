<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\IncidentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IncidentSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'Phishing', 'description' => 'Correos o mensajes fraudulentos para robar credenciales.'],
            ['name' => 'Malware', 'description' => 'Software malicioso: virus, troyanos, ransomware.'],
            ['name' => 'Ransomware', 'description' => 'Cifrado de datos con exigencia de rescate.'],
            ['name' => 'Acceso no autorizado', 'description' => 'Intrusiones o uso indebido de cuentas.'],
            ['name' => 'Denegación de servicio (DoS/DDoS)', 'description' => 'Indisponibilidad de servicios por saturación.'],
            ['name' => 'Fuga de datos', 'description' => 'Exposición o exfiltración de información sensible.'],
            ['name' => 'Vulnerabilidad', 'description' => 'Falla de seguridad detectada sin explotación confirmada.'],
            ['name' => 'Ingeniería social', 'description' => 'Manipulación de personas para obtener acceso.'],
        ];

        foreach ($types as $t) {
            IncidentType::updateOrCreate(
                ['name' => $t['name']],
                ['slug' => Str::slug($t['name']), 'description' => $t['description'], 'active' => true]
            );
        }

        if (Incident::count() > 0) {
            return;
        }

        $typeIds = IncidentType::pluck('id')->toArray();
        $severities = ['baja', 'media', 'alta', 'critica'];
        $statuses = ['abierto', 'en_proceso', 'contenido', 'erradicado', 'recuperado', 'cerrado'];
        $titles = [
            'Phishing masivo a cuentas institucionales',
            'Ransomware en estación de trabajo',
            'Acceso anómalo a servidor de archivos',
            'Caída de portal por tráfico anómalo',
            'Posible fuga de base de datos',
            'Malware detectado por antivirus',
        ];

        foreach ($titles as $i => $title) {
            Incident::create([
                'title' => $title,
                'description' => 'Incidente de ejemplo generado por el seeder para pruebas del sistema.',
                'incident_type_id' => $typeIds[$i % count($typeIds)],
                'severity' => $severities[$i % count($severities)],
                'status' => $statuses[$i % count($statuses)],
                'reported_by' => 'Mesa de ayuda',
                'detected_at' => now()->subDays($i + 1),
                'affected_assets' => 'Servidor SRV-0' . ($i + 1),
                'impact' => 'Impacto operativo moderado.',
            ]);
        }
    }
}
