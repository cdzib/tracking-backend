<?php

namespace Database\Factories;

use App\Models\Passenger;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PassengerFactory extends Factory
{
    protected $model = Passenger::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
