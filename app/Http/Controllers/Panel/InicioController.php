<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionModelo;
use App\Models\Incidente;

class InicioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:panel.ver,admin');
    }

    public function inicio()
    {
        $configuracion = ConfiguracionModelo::vigente();
        $enCola = Incidente::where('calificacion', 'incidente')->where('estado', '!=', 'cerrado');

        $estadisticas = [
            'total' => Incidente::count(),
            'abiertos' => (clone $enCola)->count(),
            'criticos' => (clone $enCola)->where('nivel_prioridad', 'critico')->count(),
            'sin_evaluar' => (clone $enCola)->whereNull('nivel_prioridad')->count(),
            'por_calificar' => Incidente::where('calificacion', 'por_calificar')->count(),
            'capacidad' => optional($configuracion)->capacidad_criticos ?? 3,
        ];

        $porEstado = Incidente::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $porPrioridad = collect(Incidente::NIVELES_PRIORIDAD)->mapWithKeys(fn ($n) => [$n => 0])
            ->merge((clone $enCola)->whereNotNull('nivel_prioridad')->selectRaw('nivel_prioridad, count(*) as total')
                ->groupBy('nivel_prioridad')->pluck('total', 'nivel_prioridad'));
        $cola = ColaPrioridadController::cola()->take(8);

        return view('panel.inicio', compact('estadisticas', 'porEstado', 'porPrioridad', 'cola', 'configuracion'));
    }
}
