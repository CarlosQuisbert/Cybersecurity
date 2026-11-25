<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    /**
     * Modelo asociado a esta fábrica.
     *
     * @var string
     */
    protected $model = Usuario::class;

    public function definition()
    {
        $nombre = $this->faker->name;
        $identificacion = (string) $this->faker->unique()->numberBetween(1000000, 9999999);

        return [
            'nombre' => $nombre,
            'usuario' => Usuario::generarUsuario($nombre),
            'identificacion' => $identificacion,
            'contrasena' => bcrypt($identificacion),
            'token_recordar' => Str::random(10),
        ];
    }
}
