<?php

namespace App\Models;

class ComentarioIncidente extends ModeloBase
{
    protected $table = 'comentarios_incidente';

    protected $fillable = ['incidente_id', 'usuario_id', 'comentario', 'es_interno'];

    protected $casts = ['es_interno' => 'boolean'];

    public function incidente()
    {
        return $this->belongsTo(Incidente::class, 'incidente_id');
    }

    public function autor()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
