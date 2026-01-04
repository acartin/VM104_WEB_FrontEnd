<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear tabla de catálogo con UUID
        DB::statement("
            CREATE TABLE lead_contact_preferences (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                slug VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(100) NOT NULL,
                icon VARCHAR(50),
                color VARCHAR(30),
                active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW()
            )
        ");

        // 2. Modificar tabla lead_leads para añadir la relación
        DB::statement("
            ALTER TABLE lead_leads 
            ADD COLUMN contact_preference_id UUID REFERENCES lead_contact_preferences(id)
        ");

        // 3. Crear índice para optimizar la UI de Filament
        DB::statement("
            CREATE INDEX idx_leads_contact_preference ON lead_leads(contact_preference_id)
        ");

        // 4. Seed de datos iniciales
        DB::table('lead_contact_preferences')->insert([
            [
                'id' => DB::raw('gen_random_uuid()'),
                'slug' => 'none',
                'name' => 'Undefined',
                'icon' => 'heroicon-o-question-mark-circle',
                'color' => 'gray',
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'slug' => 'chat_msg',
                'name' => 'Chat / Messenger',
                'icon' => 'heroicon-o-chat-bubble-bottom-center-text',
                'color' => 'success',
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'slug' => 'voice_call',
                'name' => 'Voice Call',
                'icon' => 'heroicon-o-phone',
                'color' => 'info',
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'slug' => 'meeting_pending',
                'name' => 'Meeting Pending',
                'icon' => 'heroicon-o-calendar-days',
                'color' => 'warning',
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'slug' => 'meeting_confirmed',
                'name' => 'Meeting Confirmed',
                'icon' => 'heroicon-o-calendar-check',
                'color' => 'danger',
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'slug' => 'email_info',
                'name' => 'Email',
                'icon' => 'heroicon-o-envelope',
                'color' => 'gray',
            ],
            [
                'id' => DB::raw('gen_random_uuid()'),
                'slug' => 'video_call',
                'name' => 'Video Call',
                'icon' => 'heroicon-o-video-camera',
                'color' => 'primary',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE lead_leads DROP COLUMN IF EXISTS contact_preference_id");
        DB::statement("DROP TABLE IF EXISTS lead_contact_preferences");
    }
};
