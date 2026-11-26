<?php

namespace App\Models;

/**
 * Versión de la configuración del modelo: pesos, umbrales de prioridad,
 * capacidad operativa y versión de ATT&CK. Solo una está vigente; las
 * anteriores se conservan para explicar resultados pasados.
 */
class ConfiguracionModelo extends ModeloBase
{
    protected $table = 'configuraciones_modelo';

    protected $casts = [
        'vigente' => 'boolean',
        'umbral_critico' => 'float',
        'umbral_alto' => 'float',
        'umbral_medio' => 'float',
        'origen_pesos' => 'array',
    ];

    public static function vigente(): ?self
    {
        return static::where('vigente', true)->orderByDesc('version')->first();
    }

    public function pesosBloque()
    {
        return $this->hasMany(PesoBloque::class, 'configuracion_id');
    }

    public function pesosCriterio()
    {
        return $this->hasMany(PesoCriterio::class, 'configuracion_id');
    }

    public function autor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function umbrales(): array
    {
        return [
            'critico' => $this->umbral_critico,
            'alto' => $this->umbral_alto,
            'medio' => $this->umbral_medio,
        ];
    }

    /**
     * Pesos globales por código de criterio: peso del bloque × peso local.
     * Los bloques y los pesos locales de cada bloque se normalizan para que
     * sumen 1 (los valores publicados están redondeados a tres decimales).
     */
    public function pesosGlobales(): array
    {
        $this->loadMissing(['pesosBloque', 'pesosCriterio.criterio']);

        $pesoBloque = $this->pesosBloque->pluck('peso', 'bloque_id')->map(fn ($p) => (float) $p);
        $sumaBloques = $pesoBloque->sum() ?: 1;

        $porBloque = $this->pesosCriterio->groupBy(fn ($p) => $p->criterio->bloque_id);

        $globales = [];
        foreach ($porBloque as $bloqueId => $pesos) {
            $sumaLocal = $pesos->sum(fn ($p) => (float) $p->peso_local) ?: 1;
            foreach ($pesos as $p) {
                $globales[$p->criterio->codigo] =
                    (($pesoBloque[$bloqueId] ?? 0) / $sumaBloques) * ((float) $p->peso_local / $sumaLocal);
            }
        }

        return $globales;
    }
}
