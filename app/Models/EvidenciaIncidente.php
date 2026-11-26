<?php

namespace App\Models;

class EvidenciaIncidente extends ModeloBase
{
    protected $table = 'evidencias_incidente';

    protected $hidden = ['ruta'];

    public function incidente()
    {
        return $this->belongsTo(Incidente::class, 'incidente_id');
    }

    public function autor()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
