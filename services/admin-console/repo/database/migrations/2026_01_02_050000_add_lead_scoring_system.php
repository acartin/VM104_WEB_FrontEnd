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
            ALTER TABLE crm_leads 
            ADD COLUMN score_engagement INTEGER DEFAULT 0,
            ADD COLUMN score_finance INTEGER DEFAULT 0,
            ADD COLUMN score_timeline INTEGER DEFAULT 0,
            ADD COLUMN score_match INTEGER DEFAULT 0,
            ADD COLUMN score_info INTEGER DEFAULT 0,
            ADD COLUMN score_total INTEGER DEFAULT 0;
        ");

        // Función para calcular el score total (clamp 0-100)
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_calculate_lead_score() RETURNS TRIGGER AS $$
            BEGIN
                NEW.score_total := GREATEST(0, LEAST(100, 
                    NEW.score_engagement + 
                    NEW.score_finance + 
                    NEW.score_timeline + 
                    NEW.score_match + 
                    NEW.score_info
                ));
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // Trigger para cálculo automático
        DB::statement("
            CREATE TRIGGER trg_leads_scoring
            BEFORE INSERT OR UPDATE ON crm_leads
            FOR EACH ROW EXECUTE FUNCTION fn_calculate_lead_score();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS trg_leads_scoring ON crm_leads;");
        DB::statement("DROP FUNCTION IF EXISTS fn_calculate_lead_score();");
        DB::statement("
            ALTER TABLE crm_leads 
            DROP COLUMN IF EXISTS score_engagement,
            DROP COLUMN IF EXISTS score_finance,
            DROP COLUMN IF EXISTS score_timeline,
            DROP COLUMN IF EXISTS score_match,
            DROP COLUMN IF EXISTS score_info,
            DROP COLUMN IF EXISTS score_total;
        ");
    }
};
