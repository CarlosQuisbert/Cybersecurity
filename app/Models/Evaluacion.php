<?php

namespace App\Models;

/**
 * Resultado de aplicar el modelo a un incidente: índice, nivel y el detalle
 * de cálculo, junto con la versión de configuración usada.
 */
class Evaluacion extends ModeloBase
{
    protected $table = 'evaluaciones';

    protected $casts = [
        'detalle' => 'array',
        'indice_riesgo' => 'float',
        'progresion_excluida' => 'boolean',
        'omision_inventario' => 'boolean',
    ];

    public function incidente()
    {
        return $this->belongsTo(Incidente::class, 'incidente_id');
    }

    public function configuracion()
    {
        return $this->belongsTo(ConfiguracionModelo::class, 'configuracion_id');
    }

    public function analista()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function valoraciones()
    {
        return $this->hasMany(Valoracion::class, 'evaluacion_id');
    }
}
