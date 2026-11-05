<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->name;
        $identificacion = (string) $this->faker->unique()->numberBetween(1000000, 9999999);

        return [
            'name' => $name,
            'usuario' => User::generateUsuario($name),
            'identificacion' => $identificacion,
            'password' => bcrypt($identificacion),
            'remember_token' => Str::random(10),
        ];
    }
}
