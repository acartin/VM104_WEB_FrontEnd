<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            ['id' => 1, 'name' => 'Google SEO', 'icon' => 'brand-google'],
            ['id' => 2, 'name' => 'Google SEM', 'icon' => 'brand-google'],
            ['id' => 3, 'name' => 'Facebook Ads', 'icon' => 'brand-facebook'],
            ['id' => 4, 'name' => 'Instagram Ads', 'icon' => 'brand-instagram'],
            ['id' => 5, 'name' => 'LinkedIn Ads', 'icon' => 'brand-linkedin'],
            ['id' => 6, 'name' => 'TikTok Ads', 'icon' => 'brand-tiktok'],
            ['id' => 7, 'name' => 'QR Code Offline', 'icon' => 'qr-code'],
            ['id' => 8, 'name' => 'Email Campaign', 'icon' => 'mail'],
            ['id' => 9, 'name' => 'Whatsapp', 'icon' => 'brand-whatsapp'],
            ['id' => 10, 'name' => 'Website Direct', 'icon' => 'globe'],
            ['id' => 11, 'name' => 'Facebook Organic', 'icon' => 'brand-facebook'],
            ['id' => 12, 'name' => 'Instagram Organic', 'icon' => 'brand-instagram'],
            ['id' => 13, 'name' => 'Referral', 'icon' => 'users'],
            ['id' => 14, 'name' => 'Walk-in', 'icon' => 'building-store'],
            ['id' => 15, 'name' => 'Portal (Zillow/Idealista)', 'icon' => 'home'],
            ['id' => 16, 'name' => 'Other', 'icon' => 'dots-circle-horizontal'],
        ];

        foreach ($sources as $source) {
            DB::table('lead_sources')->updateOrInsert(
                ['id' => $source['id']],
                [
                    'name' => $source['name'],
                    'icon' => $source['icon'], // Asegurar que coincida con la col añadida recientemente
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
