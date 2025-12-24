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
        // Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        // Client User
        $cocacolito = User::factory()->create([
            'name' => 'Cocacolito Client',
            'email' => 'cocacolito@admin.com',
            'password' => Hash::make('password'),
        ]);

        // Client Tenant
        $client = Client::create([
            'name' => 'Coca Cola',
            'slug' => 'coca-cola',
        ]);

        $cocacolito->clients()->attach($client);
    }
}
