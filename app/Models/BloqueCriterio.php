<?php

namespace App\Models;

/**
 * Bloque del segundo nivel de la jerarquía (impacto, activo y alcance, evolución,
 * respuesta y cumplimiento).
 */
class BloqueCriterio extends ModeloBase
{
    protected $table = 'bloques_criterio';

    public function criterios()
    {
        return $this->hasMany(Criterio::class, 'bloque_id')->orderBy('orden');
    }
}
