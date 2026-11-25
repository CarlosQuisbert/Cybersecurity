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
                'usuario' => 'gabriela.casas',
                'nombre' => 'Gabriela Casas Cornejo',
                'identificacion' => '8341951',
            ],
            [
                'usuario' => 'carlos.quisbert',
                'nombre' => 'Carlos Quisbert Maquera',
                'identificacion' => '6768243',
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
