<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripUser>
 */
class TripUserFactory extends Factory
{

    public function definition(): array
    {
        return [
            'user_id' => rand(1,20),
            'trip_id' => rand(1,100),
            'role_id' => rand(1,2)
        ];
    }
}
