<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE lead_ai_prompts (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                client_id UUID NULL REFERENCES crm_clients(id) ON DELETE CASCADE,
                slug VARCHAR(255) NOT NULL UNIQUE,
                prompt_text TEXT NOT NULL,
                is_active BOOLEAN DEFAULT true,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
                deleted_at TIMESTAMP WITHOUT TIME ZONE
            );

            CREATE TRIGGER update_lead_ai_prompts_updated_at
            BEFORE UPDATE ON lead_ai_prompts
            FOR EACH ROW
            EXECUTE FUNCTION update_updated_at_column();
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS lead_ai_prompts");
    }
};
