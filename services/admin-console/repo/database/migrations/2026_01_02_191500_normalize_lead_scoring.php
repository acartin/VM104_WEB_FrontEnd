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
        // 1. Añadir columnas de FK a lead_leads
        Schema::table('lead_leads', function (Blueprint $table) {
            $table->foreignId('eng_def_id')->nullable()->constrained('lead_scoring_definitions');
            $table->foreignId('fin_def_id')->nullable()->constrained('lead_scoring_definitions');
            $table->foreignId('timeline_def_id')->nullable()->constrained('lead_scoring_definitions');
            $table->foreignId('match_def_id')->nullable()->constrained('lead_scoring_definitions');
            $table->foreignId('info_def_id')->nullable()->constrained('lead_scoring_definitions');
            $table->foreignId('priority_def_id')->nullable()->constrained('lead_scoring_definitions');
        });

        // 2. Poblar los IDs basados en los scores actuales
        DB::statement("
            UPDATE lead_leads l
            SET 
                eng_def_id = (SELECT id FROM lead_scoring_definitions WHERE criterion = 'engagement' AND l.score_engagement BETWEEN min_score AND max_score LIMIT 1),
                fin_def_id = (SELECT id FROM lead_scoring_definitions WHERE criterion = 'finance' AND l.score_finance BETWEEN min_score AND max_score LIMIT 1),
                timeline_def_id = (SELECT id FROM lead_scoring_definitions WHERE criterion = 'timeline' AND l.score_timeline BETWEEN min_score AND max_score LIMIT 1),
                match_def_id = (SELECT id FROM lead_scoring_definitions WHERE criterion = 'match' AND l.score_match BETWEEN min_score AND max_score LIMIT 1),
                info_def_id = (SELECT id FROM lead_scoring_definitions WHERE criterion = 'info' AND l.score_info BETWEEN min_score AND max_score LIMIT 1),
                priority_def_id = (SELECT id FROM lead_scoring_definitions WHERE criterion = 'priority' AND l.score_total BETWEEN min_score AND max_score LIMIT 1)
        ");

        // 3. Eliminar las columnas redundantes de strings
        Schema::table('lead_leads', function (Blueprint $table) {
            $table->dropColumn([
                'eng_icon', 'eng_color', 
                'fin_icon', 'fin_color', 
                'timeline_label', 'timeline_color', 
                'match_icon', 'match_color', 
                'info_icon', 'info_color'
            ]);
        });

        // 4. Actualizar el motor de PostgreSQL para que trabaje con IDs
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_calculate_lead_score() RETURNS TRIGGER AS $$
            BEGIN
                -- 1. Cálculo del Score Total
                NEW.score_total := GREATEST(0, LEAST(100, 
                    COALESCE(NEW.score_engagement, 0) + 
                    COALESCE(NEW.score_finance, 0) + 
                    COALESCE(NEW.score_timeline, 0) + 
                    COALESCE(NEW.score_match, 0) + 
                    COALESCE(NEW.score_info, 0)
                ));

                -- 2. Lookup de los IDs de definición (El cerebro)
                NEW.eng_def_id := (SELECT id FROM lead_scoring_definitions WHERE criterion = 'engagement' AND NEW.score_engagement BETWEEN min_score AND max_score LIMIT 1);
                NEW.fin_def_id := (SELECT id FROM lead_scoring_definitions WHERE criterion = 'finance' AND NEW.score_finance BETWEEN min_score AND max_score LIMIT 1);
                NEW.timeline_def_id := (SELECT id FROM lead_scoring_definitions WHERE criterion = 'timeline' AND NEW.score_timeline BETWEEN min_score AND max_score LIMIT 1);
                NEW.match_def_id := (SELECT id FROM lead_scoring_definitions WHERE criterion = 'match' AND NEW.score_match BETWEEN min_score AND max_score LIMIT 1);
                NEW.info_def_id := (SELECT id FROM lead_scoring_definitions WHERE criterion = 'info' AND NEW.score_info BETWEEN min_score AND max_score LIMIT 1);
                NEW.priority_def_id := (SELECT id FROM lead_scoring_definitions WHERE criterion = 'priority' AND NEW.score_total BETWEEN min_score AND max_score LIMIT 1);

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversión omitida por brevedad, se requeriría recrear las columnas de strings
    }
};
