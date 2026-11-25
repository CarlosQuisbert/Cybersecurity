<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\HistorialIncidente;
use App\Models\Incidente;
use App\Models\TipoIncidente;
use App\Models\Usuario;
use Illuminate\Http\Request;

class IncidenteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->middleware('permission:incidentes.ver,admin')->only(['listar', 'mostrar']);
        $this->middleware('permission:incidentes.crear,admin')->only(['guardar']);
        $this->middleware('permission:incidentes.editar,admin')->only(['actualizar']);
        $this->middleware('permission:incidentes.eliminar,admin')->only(['eliminar']);
    }

    public function listar(Request $request)
    {
        $consulta = Incidente::with(['tipo', 'usuarioAsignado'])->orderByDesc('creado_en')->orderByDesc('id');

        if ($request->filled('estado')) $consulta->where('estado', $request->estado);
        if ($request->filled('severidad')) $consulta->where('severidad', $request->severidad);
        if ($request->filled('tipo_incidente_id')) $consulta->where('tipo_incidente_id', $request->tipo_incidente_id);
        if ($request->filled('buscar')) {
            $texto = $request->buscar;
            $consulta->where(function ($w) use ($texto) {
                $w->where('titulo', 'ilike', "%{$texto}%")
                  ->orWhere('codigo', 'ilike', "%{$texto}%")
                  ->orWhere('descripcion', 'ilike', "%{$texto}%");
            });
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($consulta->paginate(15));
        }

        $incidentes = Incidente::with(['tipo', 'usuarioAsignado'])->orderByDesc('creado_en')->orderByDesc('id')->get();
        $tipos = TipoIncidente::where('activo', true)->orderBy('nombre')->get();
        $usuarios = Usuario::orderBy('nombre')->get(['id', 'nombre', 'usuario']);

        return view('panel.incidentes.listado', compact('incidentes', 'tipos', 'usuarios'));
    }

    public function mostrar($id)
    {
        $incidente = Incidente::with(['tipo', 'usuarioAsignado', 'comentarios.autor', 'historial.responsable'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($incidente);
        }

        $tipos = TipoIncidente::where('activo', true)->orderBy('nombre')->get();
        $usuarios = Usuario::orderBy('nombre')->get(['id', 'nombre', 'usuario']);

        return view('panel.incidentes.detalle', compact('incidente', 'tipos', 'usuarios'));
    }

    public function guardar(Request $request)
    {
        $datos = $this->validate($request, $this->reglas(false));

        $datos['estado'] = $datos['estado'] ?? 'abierto';
        $datos['detectado_en'] = $datos['detectado_en'] ?? now();

        $incidente = Incidente::create($datos);

        HistorialIncidente::create([
            'incidente_id' => $incidente->id,
            'estado_anterior' => null,
            'estado_nuevo' => $incidente->estado,
            'cambiado_por' => auth('admin')->id(),
            'notas' => 'Incidente creado',
        ]);

        return response()->json($incidente->load(['tipo', 'usuarioAsignado']), 201);
    }

    public function actualizar(Request $request, $id)
    {
        $incidente = Incidente::findOrFail($id);
        $estadoAnterior = $incidente->estado;

        $datos = $this->validate($request, $this->reglas(true));

        $incidente->update($datos);

        if ($estadoAnterior !== $incidente->estado) {
            HistorialIncidente::create([
                'incidente_id' => $incidente->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $incidente->estado,
                'cambiado_por' => auth('admin')->id(),
                'notas' => $request->input('notas_estado'),
            ]);
        }

        return response()->json($incidente->fresh()->load(['tipo', 'usuarioAsignado']));
    }

    public function eliminar($id)
    {
        Incidente::findOrFail($id)->delete();

        return response()->json('ok');
    }

    /**
     * Reglas de validación; al actualizar, el estado es obligatorio.
     */
    private function reglas(bool $actualizando): array
    {
        return [
            'titulo' => 'required|max:255',
            'descripcion' => 'required',
            'tipo_incidente_id' => 'required|exists:tipos_incidente,id',
            'severidad' => 'required|in:' . implode(',', Incidente::SEVERIDADES),
            'estado' => ($actualizando ? 'required' : 'nullable') . '|in:' . implode(',', Incidente::ESTADOS),
            'reportado_por' => 'nullable|max:255',
            'asignado_a' => 'nullable|exists:usuarios,id',
            'detectado_en' => 'nullable|date',
            'activos_afectados' => 'nullable',
            'impacto' => 'nullable',
            'remediacion' => 'nullable',
        ];
    }
}
