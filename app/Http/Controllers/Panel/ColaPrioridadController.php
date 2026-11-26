<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionModelo;
use App\Models\Incidente;
use App\Servicios\MotorRiesgo;

/**
 * Cola de incidentes ordenada por índice de riesgo (RF7). Solo incluye los
 * eventos calificados como incidente que no están cerrados.
 */
class ColaPrioridadController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'permission:incidentes.ver,admin']);
    }

    public function listar()
    {
        $cola = self::cola();
        $configuracion = ConfiguracionModelo::vigente();
        $criticosAbiertos = $cola->where('nivel_prioridad', 'critico')->count();

        return view('panel.cola_prioridad.listado', compact('cola', 'configuracion', 'criticosAbiertos'));
    }

    public static function cola()
    {
        return Incidente::with(['tipo', 'usuarioAsignado', 'activo'])
            ->where('calificacion', 'incidente')
            ->where('estado', '!=', 'cerrado')
            ->get()
            ->sort(fn ($a, $b) => MotorRiesgo::comparar(self::claveOrden($a), self::claveOrden($b)))
            ->values();
    }

    private static function claveOrden(Incidente $i): array
    {
        return [
            'indice' => $i->indice_riesgo,
            'progresion' => $i->progresion_nivel,
            'antiguedad' => optional($i->detectado_en ?? $i->creado_en)->timestamp ?? 0,
        ];
    }
}
