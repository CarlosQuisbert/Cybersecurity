<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Tactica;
use App\Servicios\Bitacora;
use Illuminate\Http\Request;

/**
 * Catálogo de tácticas ATT&CK como parámetro configurable: una nueva versión de la
 * taxonomía se carga aquí sin modificar el código fuente (apartado 2.4.2).
 */
class TacticaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin', 'permission:modelo.configurar,admin']);
    }

    public function listar()
    {
        $tacticas = Tactica::withCount('incidentes')->orderBy('orden')->get();

        return view('panel.tacticas.listado', compact('tacticas'));
    }

    public function guardar(Request $request)
    {
        $datos = $this->validar($request);
        $datos['orden'] = $datos['orden'] ?? (Tactica::max('orden') + 1);
        $tactica = Tactica::create($datos);
        Bitacora::registrar('tactica.crear', 'tactica', $tactica->id, $tactica->only(['identificador', 'nombre', 'nivel_progresion']));

        return response()->json($tactica->loadCount('incidentes'), 201);
    }

    public function actualizar(Request $request, $id)
    {
        $tactica = Tactica::findOrFail($id);
        $tactica->update($this->validar($request, $id));
        Bitacora::registrar('tactica.editar', 'tactica', $tactica->id, $tactica->only(['identificador', 'nombre', 'nivel_progresion']));

        return response()->json($tactica->fresh()->loadCount('incidentes'));
    }

    public function eliminar($id)
    {
        $tactica = Tactica::findOrFail($id);
        if ($tactica->incidentes()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: hay incidentes clasificados con esta táctica. Puede desactivarla.'], 422);
        }
        Bitacora::registrar('tactica.eliminar', 'tactica', $tactica->id, $tactica->only(['identificador', 'nombre']));
        $tactica->delete();

        return response()->json('ok');
    }

    private function validar(Request $request, $id = null): array
    {
        $datos = $this->validate($request, [
            'identificador' => 'required|max:12|unique:tacticas,identificador' . ($id ? ",{$id}" : ''),
            'nombre' => 'required|max:255',
            'objetivo' => 'nullable',
            'nivel_progresion' => 'required|integer|between:1,5',
            'orden' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
        ]);
        $datos['activo'] = $request->has('activo') ? (bool) $request->activo : true;

        return $datos;
    }
}
