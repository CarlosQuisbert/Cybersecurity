<?php

namespace App\Models;

class Incidente extends ModeloBase
{
    public const SEVERIDADES = ['baja', 'media', 'alta', 'critica'];
    public const ESTADOS = ['abierto', 'en_proceso', 'contenido', 'erradicado', 'recuperado', 'cerrado'];

    protected $table = 'incidentes';

    protected $fillable = [
        'codigo', 'titulo', 'descripcion', 'tipo_incidente_id',
        'severidad', 'estado', 'reportado_por', 'asignado_a',
        'detectado_en', 'contenido_en', 'resuelto_en', 'cerrado_en',
        'activos_afectados', 'impacto', 'remediacion',
    ];

    protected $casts = [
        'detectado_en' => 'datetime',
        'contenido_en' => 'datetime',
        'resuelto_en' => 'datetime',
        'cerrado_en' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($incidente) {
            if (empty($incidente->codigo)) {
                $incidente->codigo = 'INC-' . date('Y') . '-' . str_pad(
                    (int) (static::where('codigo', 'like', 'INC-' . date('Y') . '-%')->count() + 1),
                    4, '0', STR_PAD_LEFT
                );
            }
        });

        static::updating(function ($incidente) {
            if ($incidente->isDirty('estado')) {
                $incidente->registrarFechasDeEstado();
            }
        });
    }

    /**
     * Registra la fecha de contención, resolución o cierre según el nuevo estado.
     */
    public function registrarFechasDeEstado()
    {
        $ahora = now();
        switch ($this->estado) {
            case 'contenido':
                if (empty($this->contenido_en)) $this->contenido_en = $ahora;
                break;
            case 'recuperado':
                if (empty($this->resuelto_en)) $this->resuelto_en = $ahora;
                break;
            case 'cerrado':
                if (empty($this->cerrado_en)) $this->cerrado_en = $ahora;
                break;
        }
    }

    public function tipo()
    {
        return $this->belongsTo(TipoIncidente::class, 'tipo_incidente_id');
    }

    public function usuarioAsignado()
    {
        return $this->belongsTo(Usuario::class, 'asignado_a');
    }

    public function comentarios()
    {
        return $this->hasMany(ComentarioIncidente::class, 'incidente_id')->orderByDesc('creado_en')->orderByDesc('id');
    }

    public function historial()
    {
        return $this->hasMany(HistorialIncidente::class, 'incidente_id')->orderByDesc('creado_en')->orderByDesc('id');
    }
}
