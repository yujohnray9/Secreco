<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'first_name'  => fake()->firstName(),
            'last_name'   => fake()->lastName(),
            'email'       => fake()->unique()->safeEmail(),
            'password'    => static::$password ??= Hash::make('password'),
            'role'        => fake()->randomElement(['pta', 'cmi', 'viewer']),
            'institution' => 'Isabela State University - Echague',
            'designation' => 'Research Coordinator',
            'status'      => 'active',
        ];
    }
}
