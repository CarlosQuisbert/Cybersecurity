<?php

namespace App\Models;

class TipoIncidente extends ModeloBase
{
    protected $table = 'tipos_incidente';

    protected $fillable = ['nombre', 'slug', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function incidentes()
    {
        return $this->hasMany(Incidente::class, 'tipo_incidente_id');
    }
}
