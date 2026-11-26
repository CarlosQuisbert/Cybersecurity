<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Incidente;
use App\Servicios\Bitacora;
use Illuminate\Http\Request;

/**
 * Inventario de activos valorado: insumo del criterio de criticidad del activo.
 */
class ActivoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'permission:activos.gestionar,admin']);
    }

    public function listar()
    {
        $activos = Activo::withCount('incidentes')->orderByDesc('creado_en')->orderByDesc('id')->get();

        // Incidentes evaluados con un activo que no figura en el inventario (omisión registrada).
        $omisiones = Incidente::whereNull('activo_id')->whereNotNull('evaluado_en')
            ->orderByDesc('evaluado_en')->get(['id', 'codigo', 'titulo', 'activos_afectados', 'evaluado_en']);

        return view('panel.activos.listado', compact('activos', 'omisiones'));
    }

    public function guardar(Request $request)
    {
        $activo = Activo::create($this->validar($request));
        Bitacora::registrar('activo.crear', 'activo', $activo->id, ['codigo' => $activo->codigo]);

        return response()->json($activo->loadCount('incidentes'), 201);
    }

    public function actualizar(Request $request, $id)
    {
        $activo = Activo::findOrFail($id);
        $anterior = $activo->criticidad;
        $activo->update($this->validar($request, $id));
        Bitacora::registrar('activo.editar', 'activo', $activo->id, ['codigo' => $activo->codigo, 'criticidad_anterior' => $anterior, 'criticidad' => $activo->criticidad]);

        return response()->json($activo->fresh()->loadCount('incidentes'));
    }

    public function eliminar($id)
    {
        $activo = Activo::findOrFail($id);
        if ($activo->incidentes()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: hay incidentes asociados. Puede desactivarlo.'], 422);
        }
        Bitacora::registrar('activo.eliminar', 'activo', $activo->id, ['codigo' => $activo->codigo]);
        $activo->delete();

        return response()->json('ok');
    }

    private function validar(Request $request, $id = null): array
    {
        $datos = $this->validate($request, [
            'codigo' => 'required|max:40|unique:activos,codigo' . ($id ? ",{$id}" : ''),
            'nombre' => 'required|max:255',
            'tipo' => 'nullable|max:60',
            'area' => 'nullable|max:255',
            'descripcion' => 'nullable',
            'criticidad' => 'required|integer|between:1,5',
            'activo' => 'nullable|boolean',
        ]);
        $datos['activo'] = $request->has('activo') ? (bool) $request->activo : true;

        return $datos;
    }
}
