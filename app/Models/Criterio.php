<?php

namespace App\Models;

/**
 * Criterio del modelo multicriterio (tercer nivel de la jerarquía).
 */
class Criterio extends ModeloBase
{
    public const FUENTE_ANALISTA = 'analista';
    public const FUENTE_INVENTARIO = 'inventario';
    public const FUENTE_TACTICAS = 'tacticas';

    protected $table = 'criterios';

    protected $casts = ['solo_adversario' => 'boolean'];

    public function bloque()
    {
        return $this->belongsTo(BloqueCriterio::class, 'bloque_id');
    }

    public function escalas()
    {
        return $this->hasMany(Escala::class, 'criterio_id')->orderBy('orden');
    }
}
