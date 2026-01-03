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
        // 1. Rename Functions
        DB::statement("ALTER FUNCTION fn_crm_update_timestamp() RENAME TO fn_lead_update_timestamp");
        
        // Check if track_lead_status exists before renaming
        $exists = DB::select("SELECT 1 FROM pg_proc WHERE proname = 'fn_crm_track_lead_status'");
        if (!empty($exists)) {
            DB::statement("ALTER FUNCTION fn_crm_track_lead_status() RENAME TO fn_lead_track_lead_status");
        }

        // 2. Rename existing update triggers to match new table names
        $tables = [
            'lead_leads', 
            'lead_contact_methods', 
            'lead_integrations', 
            'lead_users'
        ];

        foreach ($tables as $table) {
            // Old trigger name was trg_upd_crm_... where crm_... was the old table name
            // Wait, looking at step 291: trg_upd_crm_leads on lead_leads
            $oldTriggerName = str_replace('lead_', 'trg_upd_crm_', $table);
            $newTriggerName = str_replace('lead_', 'trg_upd_lead_', $table);
            
            DB::statement("ALTER TRIGGER $oldTriggerName ON $table RENAME TO $newTriggerName");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse Function names
        DB::statement("ALTER FUNCTION fn_lead_update_timestamp() RENAME TO fn_crm_update_timestamp");
        
        $exists = DB::select("SELECT 1 FROM pg_proc WHERE proname = 'fn_lead_track_lead_status'");
        if (!empty($exists)) {
            DB::statement("ALTER FUNCTION fn_lead_track_lead_status() RENAME TO fn_crm_track_lead_status");
        }

        // Reverse Triggers
        $tables = [
            'lead_leads', 
            'lead_contact_methods', 
            'lead_integrations', 
            'lead_users'
        ];

        foreach ($tables as $table) {
            $oldTriggerName = str_replace('lead_', 'trg_upd_lead_', $table);
            $newTriggerName = str_replace('lead_', 'trg_upd_crm_', $table);
            
            DB::statement("ALTER TRIGGER $oldTriggerName ON $table RENAME TO $newTriggerName");
        }
    }
};
