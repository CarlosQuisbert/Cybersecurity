<?php

namespace App\Models;

class IncidentComment extends Model
{
    protected $fillable = ['incident_id', 'admin_id', 'comment', 'is_internal'];

    protected $casts = ['is_internal' => 'boolean'];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'admin_id');
    }
}
