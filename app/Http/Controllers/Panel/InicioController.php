<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Incidente;
use App\Models\TipoIncidente;
use App\Models\Usuario;

class InicioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:panel.ver,admin');
    }

    public function inicio()
    {
        $estadisticas = [
            'total' => Incidente::count(),
            'abiertos' => Incidente::whereNotIn('estado', ['cerrado'])->count(),
            'criticos' => Incidente::where('severidad', 'critica')->whereNotIn('estado', ['cerrado'])->count(),
            'en_proceso' => Incidente::where('estado', 'en_proceso')->count(),
            'tipos' => TipoIncidente::count(),
            'administradores' => Usuario::role('Administrador')->count(),
        ];

        $porEstado = Incidente::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $porSeveridad = Incidente::selectRaw('severidad, count(*) as total')->groupBy('severidad')->pluck('total', 'severidad');
        $recientes = Incidente::with(['tipo', 'usuarioAsignado'])->orderByDesc('creado_en')->orderByDesc('id')->take(8)->get();

        return view('panel.inicio', compact('estadisticas', 'porEstado', 'porSeveridad', 'recientes'));
    }
}
