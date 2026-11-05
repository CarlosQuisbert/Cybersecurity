<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Guard used for role/permission checks (the admin panel's guard).
     *
     * @var string
     */
    protected $guard_name = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'usuario',
        'identificacion',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Builds a login username from a full name: primer nombre + "." + primer apellido,
     * normalized to lowercase ASCII, with a numeric suffix on collision.
     */
    public static function generateUsuario(string $name): string
    {
        $partes = preg_split('/\s+/', trim($name));

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
