<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run()
    {
        $usuarios = [
            [
                'usuario' => 'john.doe',
                'nombre' => 'John Doe',
                'identificacion' => '1234567',
            ],
            [
                'usuario' => 'desmond.ava',
                'nombre' => 'Desmond Ava',
                'identificacion' => '7654321',
            ],
        ];

        foreach ($usuarios as $usuario) {
            Usuario::updateOrCreate(
                ['usuario' => $usuario['usuario']],
                [
                    'nombre' => $usuario['nombre'],
                    'identificacion' => $usuario['identificacion'],
                    'contrasena' => bcrypt($usuario['identificacion']),
                ]
            );
        }
    }
}
