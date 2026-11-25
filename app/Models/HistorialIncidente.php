<?php

namespace App\Models;

class HistorialIncidente extends ModeloBase
{
    protected $table = 'historial_incidentes';

    protected $fillable = ['incidente_id', 'estado_anterior', 'estado_nuevo', 'cambiado_por', 'notas'];

    public function incidente()
    {
        return $this->belongsTo(Incidente::class, 'incidente_id');
    }

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'cambiado_por');
    }
}
