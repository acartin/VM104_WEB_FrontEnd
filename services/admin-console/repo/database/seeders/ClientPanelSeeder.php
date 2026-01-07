<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;
use App\Models\Lead;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Hash;

class ClientPanelSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Ensure Country Exists
        $country = \App\Models\Country::firstOrCreate(
            ['iso_code' => 'US'],
            ['name' => 'United States']
        );

        // 1. Get or Create Main Demo Client
        $client = Client::firstOrCreate(
            ['slug' => 'real-estate-demo'],
            [
                'name' => 'Real Estate Demo Agency',
                'country_id' => $country->id,
            ]
        );

        // 2. See Metadata (Property Types, Lead Sources)
        $propertyTypes = [
            'Apartment', 'House', 'Villa', 'Commercial', 'Land', 'Office'
        ];
        foreach ($propertyTypes as $type) {
            PropertyType::firstOrCreate(['name' => $type]);
        }

        $leadSources = [
            'Website' => 'organic', 
            'Facebook' => 'paid', 
            'Instagram' => 'paid', 
            'Referral' => 'referral', 
            'Walk-in' => 'direct',
            'Zillow' => 'portal'
        ];
        foreach ($leadSources as $name => $type) {
            LeadSource::firstOrCreate(['name' => $name], ['type' => $type]);
        }

        // 3. Create Team Structure
        
        // Manager User
        $manager = User::firstOrCreate(
            ['email' => 'manager@demo.com'],
            [
                'name' => 'Alice Manager',
                'password' => Hash::make('password'),
                'job_title' => 'Sales Manager',
                'available_status' => 'busy',
                'can_receive_leads' => true,
            ]
        );
        // Clean and attach client/role
        $manager->clients()->syncWithoutDetaching([$client->id]);
        $manager->assignRole('client_admin');

        // Sales Agents
        $agents = User::factory()->count(5)->create([
            'job_title' => 'Sales Agent',
            'can_receive_leads' => true,
            'password' => Hash::make('password'), // Uniform password for testing
        ]);

        foreach ($agents as $agent) {
            $agent->clients()->syncWithoutDetaching([$client->id]);
            $agent->assignRole('client_user');
        }
        
        // Support Staff (Cannot receive leads)
        $support = User::factory()->count(2)->create([
            'job_title' => 'Support Staff',
            'can_receive_leads' => false,
            'password' => Hash::make('password'),
        ]);
        
        foreach ($support as $staff) {
            $staff->clients()->syncWithoutDetaching([$client->id]);
            $staff->assignRole('client_user');
        }

        $allReceivers = $agents->push($manager);

        // 0. Ensure Currency Exists
        $currency = \App\Models\Currency::firstOrCreate(
            ['id' => 'USD'],
            ['symbol' => '$']
        );

        // 4. Seed Properties
        $types = PropertyType::all();
        
        Property::factory()
            ->count(50)
            ->state(function (array $attributes) use ($client, $types, $allReceivers, $currency) {
                return [
                    'client_id' => $client->id,
                    'property_type_id' => $types->random()->id,
                    'assigned_contact_id' => $allReceivers->random()->id,
                    'currency_id' => $currency->id,
                ];
            })
            ->create();

        // 5. Seed Leads
        $sources = LeadSource::all();
        $statuses = \App\Models\LeadStatus::all();

        Lead::factory()
            ->count(150)
            ->state(function (array $attributes) use ($client, $sources, $allReceivers, $currency, $statuses) {
                $status = $statuses->random();
                return [
                    'client_id' => $client->id,
                    'source_id' => $sources->random()->id,
                    'assigned_user_id' => rand(0, 10) > 2 ? $allReceivers->random()->id : null, // 20% unassigned
                    'financial_currency_id' => $currency->id,
                    'status_id' => $status->id,
                    'status' => $status->name, // Keep legacy in sync
                ];
            })
            ->create();

        $this->command->info('Client Panel populated for: ' . $client->name);
        $this->command->info('Manager Login: manager@demo.com / password');
    }
}
