<?php

namespace App\Models;

class Incident extends Model
{
    public const SEVERITIES = ['baja', 'media', 'alta', 'critica'];
    public const STATUSES = ['abierto', 'en_proceso', 'contenido', 'erradicado', 'recuperado', 'cerrado'];

    protected $fillable = [
        'code', 'title', 'description', 'incident_type_id',
        'severity', 'status', 'reported_by', 'assigned_to',
        'detected_at', 'contained_at', 'resolved_at', 'closed_at',
        'affected_assets', 'impact', 'remediation',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'contained_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($incident) {
            if (empty($incident->code)) {
                $incident->code = 'INC-' . date('Y') . '-' . str_pad(
                    (int) (static::where('code', 'like', 'INC-' . date('Y') . '-%')->count() + 1),
                    4, '0', STR_PAD_LEFT
                );
            }
        });

        static::updating(function ($incident) {
            if ($incident->isDirty('status')) {
                $incident->touchStatusDates();
            }
        });
    }

    public function touchStatusDates()
    {
        $now = now();
        switch ($this->status) {
            case 'contenido':
                if (empty($this->contained_at)) $this->contained_at = $now;
                break;
            case 'recuperado':
                if (empty($this->resolved_at)) $this->resolved_at = $now;
                break;
            case 'cerrado':
                if (empty($this->closed_at)) $this->closed_at = $now;
                break;
        }
    }

    public function type()
    {
        return $this->belongsTo(IncidentType::class, 'incident_type_id');
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->hasMany(IncidentComment::class)->orderByDesc('created_at');
    }

    public function histories()
    {
        return $this->hasMany(IncidentHistory::class)->orderByDesc('created_at');
    }
}
