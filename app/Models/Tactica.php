<?php

namespace App\Models;

/**
 * Táctica del catálogo empresarial de MITRE ATT&CK, agrupada por nivel de
 * progresión adversaria (escala j de la tesis).
 */
class Tactica extends ModeloBase
{
    public const ETAPAS = [
        1 => 'Preparación previa',
        2 => 'Acceso inicial',
        3 => 'Consolidación',
        4 => 'Expansión',
        5 => 'Consumación',
    ];

    protected $table = 'tacticas';

    protected $casts = ['activo' => 'boolean'];

    protected $appends = ['etapa'];

    public function getEtapaAttribute()
    {
        return self::ETAPAS[$this->nivel_progresion] ?? '-';
    }

    public function incidentes()
    {
        return $this->belongsToMany(Incidente::class, 'incidente_tactica', 'tactica_id', 'incidente_id');
    }
}
