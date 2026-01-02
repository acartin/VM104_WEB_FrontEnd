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
        DB::statement("DROP TABLE IF EXISTS crm_communication_channels CASCADE");
        DB::statement("
            CREATE TABLE crm_communication_channels (
                id SERIAL PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                icon VARCHAR(50),
                active BOOLEAN DEFAULT TRUE
            )
        ");

        // Seed some basic channels
        DB::table('crm_communication_channels')->insert([
            ['name' => 'Phone', 'icon' => 'heroicon-o-phone'],
            ['name' => 'Email', 'icon' => 'heroicon-o-envelope'],
            ['name' => 'WhatsApp', 'icon' => 'heroicon-o-chat-bubble-left-right'],
            ['name' => 'Telegram', 'icon' => 'heroicon-o-paper-airplane'],
            ['name' => 'LinkedIn', 'icon' => 'heroicon-o-link'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS crm_communication_channels");
    }
};
