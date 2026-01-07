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
            CREATE TABLE crm_client_user (
                id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
                client_id UUID NOT NULL,
                user_id UUID NOT NULL,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(),
                CONSTRAINT crm_client_user_crm_client_id_fkey FOREIGN KEY (client_id) REFERENCES crm_clients(id) ON DELETE CASCADE,
                CONSTRAINT crm_client_user_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS crm_client_user");
    }
};
