<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE lead_ai_prompts 
            ADD COLUMN deleted_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NULL;
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE lead_ai_prompts 
            DROP COLUMN IF EXISTS deleted_at;
        ");
    }
};
