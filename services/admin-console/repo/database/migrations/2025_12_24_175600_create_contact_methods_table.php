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
        DB::statement("DROP TABLE IF EXISTS crm_contact_methods CASCADE");
        DB::statement("
            CREATE TABLE crm_contact_methods (
                id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
                contact_id UUID NOT NULL,
                channel_id INTEGER NOT NULL,
                value VARCHAR(255) NOT NULL,
                label VARCHAR(50),
                is_primary BOOLEAN DEFAULT FALSE,
                updated_at TIMESTAMPTZ DEFAULT NOW(),
                CONSTRAINT crm_contact_methods_contact_id_fkey FOREIGN KEY (contact_id) REFERENCES crm_contacts(id) ON DELETE CASCADE,
                CONSTRAINT crm_contact_methods_channel_id_fkey FOREIGN KEY (channel_id) REFERENCES crm_communication_channels(id) ON DELETE CASCADE
            )
        ");

        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS crm_contact_methods_contact_channel_value_unique 
            ON crm_contact_methods (contact_id, channel_id, value)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS crm_contact_methods");
    }
};
