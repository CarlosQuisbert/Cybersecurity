<?php

namespace App\Models;

class Incidente extends ModeloBase
{
    public const ESTADOS = ['abierto', 'en_proceso', 'contenido', 'erradicado', 'recuperado', 'cerrado'];

    /** Calificación previa (apartado 2.1.3): solo los incidentes entran a la cola. */
    public const CALIFICACIONES = ['por_calificar', 'incidente', 'descartado'];

    /** Origen del incidente: la progresión adversaria solo aplica al adversario. */
    public const ORIGENES = ['adversario', 'no_adversario'];

    /** Niveles de prioridad de mayor a menor (apartado 3.3.1.6). */
    public const NIVELES_PRIORIDAD = ['critico', 'alto', 'medio', 'bajo'];

    protected $table = 'incidentes';

    protected $fillable = [
        'codigo', 'titulo', 'descripcion', 'tipo_incidente_id',
        'estado', 'reportado_por', 'asignado_a',
        'calificacion', 'origen', 'activo_id',
        'indice_riesgo', 'nivel_prioridad', 'progresion_nivel', 'evaluado_en',
        'detectado_en', 'contenido_en', 'resuelto_en', 'cerrado_en',
        'activos_afectados', 'impacto', 'remediacion',
    ];

    protected $casts = [
        'detectado_en' => 'datetime',
        'contenido_en' => 'datetime',
        'resuelto_en' => 'datetime',
        'cerrado_en' => 'datetime',
        'evaluado_en' => 'datetime',
        'indice_riesgo' => 'float',
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

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    public function tacticas()
    {
        return $this->belongsToMany(Tactica::class, 'incidente_tactica', 'incidente_id', 'tactica_id')->orderBy('orden');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'incidente_id')->orderByDesc('creado_en')->orderByDesc('id');
    }

    public function ultimaEvaluacion()
    {
        return $this->hasOne(Evaluacion::class, 'incidente_id')->latest('creado_en')->latest('id');
    }

    public function evidencias()
    {
        return $this->hasMany(EvidenciaIncidente::class, 'incidente_id')->orderByDesc('creado_en')->orderByDesc('id');
    }

    public function esAdversario(): bool
    {
        return $this->origen === 'adversario';
    }

    /**
     * Nivel de progresión: la táctica más avanzada evidenciada (Sadlek et al., 2025).
     */
    public function nivelProgresion(): ?int
    {
        $maximo = $this->tacticas->max('nivel_progresion');

        return $maximo ? (int) $maximo : null;
    }
}
