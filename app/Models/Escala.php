<?php

namespace App\Models;

/**
 * Escala ordinal de cinco niveles con criterios de asignación documentados.
 */
class Escala extends ModeloBase
{
    protected $table = 'escalas';

    public function criterio()
    {
        return $this->belongsTo(Criterio::class, 'criterio_id');
    }

    public function niveles()
    {
        return $this->hasMany(NivelEscala::class, 'escala_id')->orderBy('nivel');
    }
}
