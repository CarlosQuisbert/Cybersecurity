<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\RegistroBitacora;

class BitacoraController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'permission:bitacora.ver,admin']);
    }

    public function listar()
    {
        $registros = RegistroBitacora::with('usuario:id,nombre,usuario')
            ->orderByDesc('creado_en')->orderByDesc('id')
            ->paginate(50);

        return view('panel.bitacora.listado', compact('registros'));
    }
}
