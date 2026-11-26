@php
    $etiquetas = ['critico' => 'Crítico', 'alto' => 'Alto', 'medio' => 'Medio', 'bajo' => 'Bajo'];
@endphp
<span class="badge badge-prioridad-{{ $nivel ?: 'ninguna' }}">{{ $nivel ? $etiquetas[$nivel] ?? $nivel : 'Sin evaluar' }}</span>
