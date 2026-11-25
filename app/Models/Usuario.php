<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Autenticable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Autenticable
{
    use HasFactory, Notifiable, HasRoles;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $table = 'usuarios';

    /**
     * Guard usado para verificar roles y permisos (el del panel de administración).
     * Spatie exige que la propiedad se llame "guard_name".
     *
     * @var string
     */
    protected $guard_name = 'admin';

    /**
     * Columna donde se guarda el token de "recordarme".
     *
     * @var string
     */
    protected $rememberTokenName = 'token_recordar';

    protected $fillable = [
        'nombre',
        'usuario',
        'identificacion',
        'contrasena',
    ];

    protected $hidden = [
        'contrasena',
        'token_recordar',
    ];

    /**
     * Laravel obtiene el hash de la contraseña a través de este método.
     */
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    /**
     * Genera un nombre de usuario a partir del nombre completo:
     * primer nombre + "." + primer apellido, en minúsculas y sin acentos,
     * con un sufijo numérico si ya existe.
     */
    public static function generarUsuario(string $nombre): string
    {
        $partes = preg_split('/\s+/', trim($nombre));

        $normalizar = function ($valor) {
            $valor = Str::of($valor)->ascii()->lower();
            return preg_replace('/[^a-z0-9]/', '', $valor);
        };

        $usuario = $normalizar($partes[0] ?? '');

        if (! empty($partes[1])) {
            $usuario .= '.' . $normalizar($partes[1]);
        }

        $base = $usuario;
        $sufijo = 1;

        while (static::where('usuario', $usuario)->exists()) {
            $sufijo++;
            $usuario = $base . $sufijo;
        }

        return $usuario;
    }
}
