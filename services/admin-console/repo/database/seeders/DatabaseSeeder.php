<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Admin User
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('super_admin');

        // Client User
        $cocacolito = User::factory()->create([
            'name' => 'Cocacolito Client',
            'email' => 'cocacolito@admin.com',
            'password' => Hash::make('password'),
            'job_title' => 'CEO',
            'available_status' => 'available',
        ]);
        $cocacolito->assignRole('client_admin');

        // Seed Reference Tables First
        $this->call([
            LeadStatusSeeder::class,
            LeadSourceSeeder::class,
        ]);

        // Client Tenant
        $client = Client::create([
            'name' => 'Coca Cola',
            'slug' => 'coca-cola',
        ]);

        $cocacolito->clients()->attach($client);
        
        // Seed Intelligence Data (Scores, Leads, Conversations)
        // This relies on the client existing
        $this->call(LeadIntelligenceSeeder::class);
    }
}
