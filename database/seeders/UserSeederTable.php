<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $users = [
            [
                'usuario' => 'john.doe',
                'name' => 'John Doe',
                'identificacion' => '1234567',
            ],
            [
                'usuario' => 'desmond.ava',
                'name' => 'Desmond Ava',
                'identificacion' => '7654321',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['usuario' => $user['usuario']],
                [
                    'name' => $user['name'],
                    'identificacion' => $user['identificacion'],
                    'password' => bcrypt($user['identificacion']),
                ]
            );
        }

    }

}
