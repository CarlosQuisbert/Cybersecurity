<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\IncidentType;
use App\Models\User;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->middleware('permission:incidents.view,admin')->only(['index', 'show']);
        $this->middleware('permission:incidents.create,admin')->only(['store']);
        $this->middleware('permission:incidents.edit,admin')->only(['update']);
        $this->middleware('permission:incidents.delete,admin')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Incident::with(['type', 'assignedAdmin'])->orderByDesc('created_at');

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('severity')) $query->where('severity', $request->severity);
        if ($request->filled('incident_type_id')) $query->where('incident_type_id', $request->incident_type_id);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('title', 'ilike', "%{$q}%")
                  ->orWhere('code', 'ilike', "%{$q}%")
                  ->orWhere('description', 'ilike', "%{$q}%");
            });
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($query->paginate(15));
        }

        $incidents = Incident::with(['type', 'assignedAdmin'])->orderByDesc('created_at')->take(200)->get();
        $types = IncidentType::where('active', true)->orderBy('name')->get();
        $admins = User::orderBy('name')->get(['id', 'name', 'usuario']);

        return view('admin.incidents.index', compact('incidents', 'types', 'admins'));
    }

    public function show($id)
    {
        $incident = Incident::with(['type', 'assignedAdmin', 'comments.author', 'histories.changer'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($incident);
        }

        $types = IncidentType::where('active', true)->orderBy('name')->get();
        $admins = User::orderBy('name')->get(['id', 'name', 'usuario']);

        return view('admin.incidents.show', compact('incident', 'types', 'admins'));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'required',
            'incident_type_id' => 'required|exists:incident_types,id',
            'severity' => 'required|in:baja,media,alta,critica',
            'status' => 'nullable|in:abierto,en_proceso,contenido,erradicado,recuperado,cerrado',
            'reported_by' => 'nullable|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'detected_at' => 'nullable|date',
            'affected_assets' => 'nullable',
            'impact' => 'nullable',
            'remediation' => 'nullable',
        ]);

        $data['status'] = $data['status'] ?? 'abierto';
        $data['detected_at'] = $data['detected_at'] ?? now();

        $incident = Incident::create($data);

        IncidentHistory::create([
            'incident_id' => $incident->id,
            'from_status' => null,
            'to_status' => $incident->status,
            'changed_by' => auth('admin')->id(),
            'notes' => 'Incidente creado',
        ]);

        return response()->json($incident->load(['type', 'assignedAdmin']), 201);
    }

    public function update(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $oldStatus = $incident->status;

        $data = $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'required',
            'incident_type_id' => 'required|exists:incident_types,id',
            'severity' => 'required|in:baja,media,alta,critica',
            'status' => 'required|in:abierto,en_proceso,contenido,erradicado,recuperado,cerrado',
            'reported_by' => 'nullable|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'detected_at' => 'nullable|date',
            'affected_assets' => 'nullable',
            'impact' => 'nullable',
            'remediation' => 'nullable',
        ]);

        $incident->update($data);

        if ($oldStatus !== $incident->status) {
            IncidentHistory::create([
                'incident_id' => $incident->id,
                'from_status' => $oldStatus,
                'to_status' => $incident->status,
                'changed_by' => auth('admin')->id(),
                'notes' => $request->input('status_notes'),
            ]);
        }

        return response()->json($incident->fresh()->load(['type', 'assignedAdmin']));
    }

    public function destroy($id)
    {
        Incident::findOrFail($id)->delete();

        return response()->json('ok');
    }
}
