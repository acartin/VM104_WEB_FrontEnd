<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE TABLE lead_knowledge_documents (
                id BIGSERIAL PRIMARY KEY,
                client_id UUID NOT NULL REFERENCES lead_clients(id) ON DELETE CASCADE,
                filename VARCHAR(255) NOT NULL,
                storage_path VARCHAR(255) NOT NULL,
                content_hash VARCHAR(64),
                sync_status VARCHAR(20) CHECK (sync_status IN ('PENDING', 'SYNCED', 'FAILED')),
                last_synced_at TIMESTAMP WITHOUT TIME ZONE,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW()
            );
        ");

        DB::statement("
            COMMENT ON TABLE lead_knowledge_documents IS 'Stores PDF documents for Knowledge Base with sync status to AI.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS lead_knowledge_documents CASCADE");
    }
};
