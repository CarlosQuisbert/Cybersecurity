<?php

namespace App\Models;

/**
 * Activo del inventario valorado. Su criticidad (1-5) alimenta el criterio
 * "criticidad del activo" sin que el analista la estime.
 */
class Activo extends ModeloBase
{
    protected $table = 'activos';

    protected $casts = ['activo' => 'boolean'];

    public function incidentes()
    {
        return $this->hasMany(Incidente::class, 'activo_id');
    }
}
