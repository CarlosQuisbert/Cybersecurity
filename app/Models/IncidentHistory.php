<?php

namespace App\Models;

class IncidentHistory extends Model
{
    protected $fillable = ['incident_id', 'from_status', 'to_status', 'changed_by', 'notes'];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function changer()
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }
}
