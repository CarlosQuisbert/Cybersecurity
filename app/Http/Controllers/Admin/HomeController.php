<?php

namespace App\Http\Controllers\Admin;

use App\Models\Incident;
use App\Models\IncidentType;
use App\Models\User;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:dashboard.view,admin');
    }

    public function index()
    {
        $stats = [
            'total' => Incident::count(),
            'open' => Incident::whereNotIn('status', ['cerrado'])->count(),
            'critical' => Incident::where('severity', 'critica')->whereNotIn('status', ['cerrado'])->count(),
            'in_progress' => Incident::where('status', 'en_proceso')->count(),
            'types' => IncidentType::count(),
            'admins' => User::role('Administrador')->count(),
        ];

        $byStatus = Incident::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $bySeverity = Incident::selectRaw('severity, count(*) as total')->groupBy('severity')->pluck('total', 'severity');
        $recent = Incident::with(['type', 'assignedAdmin'])->orderByDesc('created_at')->take(8)->get();

        return view('admin.home', compact('stats', 'byStatus', 'bySeverity', 'recent'));
    }
}
