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
        // Indexes dedicated for faster SORTING (ORDER BY)
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_score_engagement ON lead_leads(score_engagement)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_score_finance ON lead_leads(score_finance)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_score_timeline ON lead_leads(score_timeline)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_score_match ON lead_leads(score_match)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_score_info ON lead_leads(score_info)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_leads_created_at ON lead_leads(created_at)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_leads_score_engagement");
        DB::statement("DROP INDEX IF EXISTS idx_leads_score_finance");
        DB::statement("DROP INDEX IF EXISTS idx_leads_score_timeline");
        DB::statement("DROP INDEX IF EXISTS idx_leads_score_match");
        DB::statement("DROP INDEX IF EXISTS idx_leads_score_info");
        DB::statement("DROP INDEX IF EXISTS idx_leads_created_at");
    }
};
