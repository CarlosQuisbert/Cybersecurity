<?php

namespace App\Models;

class NivelEscala extends ModeloBase
{
    protected $table = 'niveles_escala';

    public function escala()
    {
        return $this->belongsTo(Escala::class, 'escala_id');
    }
}
