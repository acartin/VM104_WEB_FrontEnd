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
        // 1. Rename users to crm_users
        DB::statement("ALTER TABLE users RENAME TO crm_users");

        // 2. Add triggers for updated_at to ensure model logic consistency
        $tables = ['crm_leads', 'crm_contact_methods', 'crm_integrations', 'crm_users'];
        foreach ($tables as $table) {
            DB::statement("
                DO $$
                BEGIN
                    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = '$table' AND column_name = 'updated_at') THEN
                        IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trg_upd_$table') THEN
                            EXECUTE 'CREATE TRIGGER trg_upd_' || '$table' || ' 
                                     BEFORE UPDATE ON ' || '$table' || ' 
                                     FOR EACH ROW 
                                     EXECUTE FUNCTION fn_crm_update_timestamp()';
                        END IF;
                    END IF;
                END $$;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse triggers
        $tables = ['crm_leads', 'crm_contact_methods', 'crm_integrations', 'crm_users'];
        foreach ($tables as $table) {
            DB::statement("DROP TRIGGER IF EXISTS trg_upd_$table ON $table");
        }

        // Rename crm_users back to users
        DB::statement("ALTER TABLE crm_users RENAME TO users");
    }
};
