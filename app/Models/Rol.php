<?php

namespace App\Models;

use Spatie\Permission\Models\Role;

/**
 * Rol del sistema (extiende el modelo de spatie/laravel-permission).
 *
 * Las columnas "name" y "guard_name" están fijas en el paquete; el accesor
 * "nombre" y la relación "permisos" exponen esos datos en español.
 */
class Rol extends Role
{
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $appends = ['nombre'];

    public function getNombreAttribute()
    {
        return $this->attributes['name'] ?? null;
    }

    public function permisos()
    {
        return $this->permissions();
    }
}
