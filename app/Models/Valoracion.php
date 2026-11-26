<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Valoracion extends Model
{
    public $timestamps = false;

    protected $table = 'valoraciones';

    protected $guarded = [];

    public function escala()
    {
        return $this->belongsTo(Escala::class, 'escala_id');
    }
}
