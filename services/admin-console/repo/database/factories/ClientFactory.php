<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'slug' => $this->faker->slug(),
            'country_id' => \App\Models\Country::firstOrCreate(['iso_code' => 'US'], ['name' => 'United States'])->id,
        ];
    }
}
