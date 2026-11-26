<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesoBloque extends Model
{
    public $timestamps = false;

    protected $table = 'pesos_bloque';

    protected $guarded = [];

    public function bloque()
    {
        return $this->belongsTo(BloqueCriterio::class, 'bloque_id');
    }
}
