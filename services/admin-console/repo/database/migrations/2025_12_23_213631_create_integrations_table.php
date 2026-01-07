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
        DB::statement("
            CREATE TABLE crm_integrations (
                id UUID PRIMARY KEY,
                client_id UUID NOT NULL,
                provider VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                credentials TEXT NOT NULL,
                status BOOLEAN DEFAULT TRUE,
                settings JSONB,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
                CONSTRAINT crm_integrations_crm_client_id_fkey FOREIGN KEY (client_id) REFERENCES crm_clients(id) ON DELETE CASCADE
            )
        ");
        
        DB::statement("CREATE INDEX crm_integrations_provider_index ON crm_integrations (provider)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS crm_integrations");
    }
};
