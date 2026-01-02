<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Lead;
use App\Models\Client;
use App\Models\User;
use App\Models\LeadSource;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'declared_income' => $this->faker->randomFloat(2, 30000, 200000),
            'current_debts' => $this->faker->randomFloat(2, 0, 50000),
            'status' => $this->faker->randomElement(['new', 'contacted', 'qualified', 'lost', 'won']),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
