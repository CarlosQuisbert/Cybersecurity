<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroBitacora extends Model
{
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $table = 'bitacora';

    protected $guarded = [];

    protected $casts = ['detalle' => 'array'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
