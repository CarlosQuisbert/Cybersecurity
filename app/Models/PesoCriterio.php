<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesoCriterio extends Model
{
    public $timestamps = false;

    protected $table = 'pesos_criterio';

    protected $guarded = [];

    public function criterio()
    {
        return $this->belongsTo(Criterio::class, 'criterio_id');
    }
}
