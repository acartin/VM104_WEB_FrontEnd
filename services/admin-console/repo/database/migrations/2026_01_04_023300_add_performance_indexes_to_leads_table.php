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
        // 1. Índices para los filtros de Workflow y Outcome
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_status_id ON lead_leads(status_id)");
        
        // 2. Índices para los nuevos filtros cualitativos (Scoring Definitions)
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_fin_def_id ON lead_leads(fin_def_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_timeline_def_id ON lead_leads(timeline_def_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_eng_def_id ON lead_leads(eng_def_id)");
        
        // 3. Índice para el Score Total (Sorting y Rango)
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_score_total ON lead_leads(score_total)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_leads_status_id");
        DB::statement("DROP INDEX IF EXISTS idx_leads_fin_def_id");
        DB::statement("DROP INDEX IF EXISTS idx_leads_timeline_def_id");
        DB::statement("DROP INDEX IF EXISTS idx_leads_eng_def_id");
        DB::statement("DROP INDEX IF EXISTS idx_leads_score_total");
    }
};
