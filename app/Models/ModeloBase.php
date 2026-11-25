<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo base de la aplicación: todas las tablas propias usan
 * las columnas de auditoría "creado_en" y "actualizado_en".
 */
abstract class ModeloBase extends Model
{
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $guarded = [];
}
