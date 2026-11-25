<?php

namespace Database\Seeders;

use App\Models\Incidente;
use App\Models\TipoIncidente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IncidenteSeeder extends Seeder
{
    public function run()
    {
        $tipos = [
            ['nombre' => 'Phishing', 'descripcion' => 'Correos o mensajes fraudulentos para robar credenciales.'],
            ['nombre' => 'Malware', 'descripcion' => 'Software malicioso: virus, troyanos, ransomware.'],
            ['nombre' => 'Ransomware', 'descripcion' => 'Cifrado de datos con exigencia de rescate.'],
            ['nombre' => 'Acceso no autorizado', 'descripcion' => 'Intrusiones o uso indebido de cuentas.'],
            ['nombre' => 'Denegación de servicio (DoS/DDoS)', 'descripcion' => 'Indisponibilidad de servicios por saturación.'],
            ['nombre' => 'Fuga de datos', 'descripcion' => 'Exposición o exfiltración de información sensible.'],
            ['nombre' => 'Vulnerabilidad', 'descripcion' => 'Falla de seguridad detectada sin explotación confirmada.'],
            ['nombre' => 'Ingeniería social', 'descripcion' => 'Manipulación de personas para obtener acceso.'],
        ];

        foreach ($tipos as $t) {
            TipoIncidente::updateOrCreate(
                ['nombre' => $t['nombre']],
                ['slug' => Str::slug($t['nombre']), 'descripcion' => $t['descripcion'], 'activo' => true]
            );
        }

        if (Incidente::count() > 0) {
            return;
        }

        $tipoIds = TipoIncidente::pluck('id')->toArray();
        $severidades = Incidente::SEVERIDADES;
        $estados = Incidente::ESTADOS;
        $titulos = [
            'Phishing masivo a cuentas institucionales',
            'Ransomware en estación de trabajo',
            'Acceso anómalo a servidor de archivos',
            'Caída de portal por tráfico anómalo',
            'Posible fuga de base de datos',
            'Malware detectado por antivirus',
        ];

        foreach ($titulos as $i => $titulo) {
            Incidente::create([
                'titulo' => $titulo,
                'descripcion' => 'Incidente de ejemplo generado por el seeder para pruebas del sistema.',
                'tipo_incidente_id' => $tipoIds[$i % count($tipoIds)],
                'severidad' => $severidades[$i % count($severidades)],
                'estado' => $estados[$i % count($estados)],
                'reportado_por' => 'Mesa de ayuda',
                'detectado_en' => now()->subDays($i + 1),
                'activos_afectados' => 'Servidor SRV-0' . ($i + 1),
                'impacto' => 'Impacto operativo moderado.',
            ]);
        }
    }
}
