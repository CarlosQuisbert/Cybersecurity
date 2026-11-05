<?php

namespace App\Models;

class IncidentType extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }
}
