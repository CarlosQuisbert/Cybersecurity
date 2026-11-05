<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncidentType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IncidentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->middleware('permission:incident-types.manage,admin');
    }

    public function index()
    {
        $types = IncidentType::withCount('incidents')->orderBy('name')->get();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($types);
        }

        return view('admin.incident_types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'name' => 'required|max:100|unique:incident_types,name',
            'description' => 'nullable',
            'active' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['active'] = $request->has('active') ? (bool) $request->active : true;

        return response()->json(IncidentType::create($data), 201);
    }

    public function update(Request $request, $id)
    {
        $type = IncidentType::findOrFail($id);

        $data = $this->validate($request, [
            'name' => 'required|max:100|unique:incident_types,name,' . $id,
            'description' => 'nullable',
            'active' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $type->update($data);

        return response()->json($type->fresh());
    }

    public function destroy($id)
    {
        $type = IncidentType::findOrFail($id);

        if ($type->incidents()->count() > 0) {
            return response()->json(['message' => 'No se puede eliminar: tiene incidentes asociados.'], 422);
        }

        $type->delete();

        return response()->json('ok');
    }
}
