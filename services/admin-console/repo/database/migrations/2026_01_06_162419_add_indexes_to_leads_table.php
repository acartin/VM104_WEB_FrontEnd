<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Is the migration transactional?
     *
     * @var bool
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Status + Score (Partial)
        // Optimization: Filter by Workflow Status, Sort by Score
        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_leads_status_score_total_active ON lead_leads(status_id, score_total DESC) WHERE deleted_at IS NULL');
        
        // Status + Created At (Partial)
        // Optimization: Filter by certain Statuses, Sort by Recent
        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_leads_status_created_at_active ON lead_leads(status_id, created_at DESC) WHERE deleted_at IS NULL');

        // Intent + Score (Partial)
        // Optimization: Filter by Intent (Contact Preference), Sort by Score
        DB::statement('CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_leads_intent_score_total_active ON lead_leads(contact_preference_id, score_total DESC) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_leads_status_score_total_active');
        DB::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_leads_status_created_at_active');
        DB::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_leads_intent_score_total_active');
    }
};
