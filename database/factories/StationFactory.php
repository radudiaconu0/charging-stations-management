<?php

namespace Database\Factories;

use App\Models\Station;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class StationFactory extends Factory
{
    protected $model = Station::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'company_id' => Company::factory(),
            'address' => $this->faker->address,
        ];
    }
}
