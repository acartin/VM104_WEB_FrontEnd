<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Property;
use App\Models\Client;
use App\Models\User;
use App\Models\PropertyType;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'address_street' => $this->faker->streetAddress(),
            'address_city' => $this->faker->city(),
            'address_state' => $this->faker->state(),
            'address_zip' => $this->faker->postcode(),
            'location_lat' => $this->faker->latitude(),
            'location_lng' => $this->faker->longitude(),
            'bedrooms' => $this->faker->numberBetween(1, 6),
            'bathrooms' => $this->faker->randomFloat(1, 1, 4),
            'area_sqm' => $this->faker->numberBetween(50, 500),
            'price' => $this->faker->randomFloat(2, 100000, 2000000),
            'features' => $this->faker->words(5),
            'status' => $this->faker->randomElement(['available', 'sold', 'rented', 'reserved']),
        ];
    }
}
