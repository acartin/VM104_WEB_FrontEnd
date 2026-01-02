<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeadStatus;

class LeadStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'New',
                'key' => 'new',
                'color' => 'gray',
                'order' => 10,
                'is_won' => false,
                'is_lost' => false,
            ],
            [
                'name' => 'Contacted',
                'key' => 'contacted',
                'color' => 'info',
                'order' => 20,
                'is_won' => false,
                'is_lost' => false,
            ],
            [
                'name' => 'Qualified',
                'key' => 'qualified',
                'color' => 'success',
                'order' => 30,
                'is_won' => false,
                'is_lost' => false,
            ],
            [
                'name' => 'Lost',
                'key' => 'lost',
                'color' => 'danger',
                'order' => 40,
                'is_won' => false,
                'is_lost' => true,
            ],
            [
                'name' => 'Won',
                'key' => 'won',
                'color' => 'success',
                'order' => 50,
                'is_won' => true,
                'is_lost' => false,
            ],
        ];

        foreach ($statuses as $status) {
            LeadStatus::firstOrCreate(['key' => $status['key']], $status);
        }
    }
}
