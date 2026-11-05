<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentComment;
use Illuminate\Http\Request;

class IncidentCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->middleware('permission:incidents.view,admin')->only(['index']);
        $this->middleware('permission:incidents.edit,admin')->only(['store', 'destroy']);
    }

    public function index($incidentId)
    {
        $incident = Incident::findOrFail($incidentId);

        return response()->json($incident->comments()->with('author')->get());
    }

    public function store(Request $request, $incidentId)
    {
        $incident = Incident::findOrFail($incidentId);

        $data = $this->validate($request, [
            'comment' => 'required',
            'is_internal' => 'nullable|boolean',
        ]);

        $comment = IncidentComment::create([
            'incident_id' => $incident->id,
            'admin_id' => auth('admin')->id(),
            'comment' => $data['comment'],
            'is_internal' => $data['is_internal'] ?? true,
        ]);

        return response()->json($comment->load('author'), 201);
    }

    public function destroy($incidentId, $id)
    {
        IncidentComment::where('incident_id', $incidentId)->findOrFail($id)->delete();

        return response()->json('ok');
    }
}
