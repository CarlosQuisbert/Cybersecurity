<?php

namespace App\Servicios;

use App\Models\RegistroBitacora;

/**
 * Registro de las acciones de cada usuario (RNF1) y de la cadena de custodia
 * de las evidencias.
 */
class Bitacora
{
    public static function registrar(string $accion, ?string $entidad = null, $entidadId = null, array $detalle = [], $usuarioId = null): RegistroBitacora
    {
        return RegistroBitacora::create([
            'usuario_id' => $usuarioId ?? auth('admin')->id(),
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'detalle' => $detalle ?: null,
            'ip' => request()->ip(),
        ]);
    }
}
