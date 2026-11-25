<?php

namespace App\Models;

use Spatie\Permission\Models\Permission;

/**
 * Permiso del sistema (extiende el modelo de spatie/laravel-permission).
 *
 * Las columnas "name" y "guard_name" están fijas en el paquete; el accesor
 * "nombre" expone ese dato en español.
 */
class Permiso extends Permission
{
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $appends = ['nombre'];

    public function getNombreAttribute()
    {
        return $this->attributes['name'] ?? null;
    }
}
