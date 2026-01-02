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
        // 1. Añadir columnas de metadatos visuales
        DB::statement("
            ALTER TABLE crm_leads 
            ADD COLUMN eng_icon VARCHAR(50) DEFAULT 'message-square-heart',
            ADD COLUMN eng_color VARCHAR(50) DEFAULT 'thermal-none',
            ADD COLUMN fin_icon VARCHAR(50) DEFAULT 'wallet',
            ADD COLUMN fin_color VARCHAR(50) DEFAULT 'thermal-none',
            ADD COLUMN timeline_label VARCHAR(50) DEFAULT 'FRÍO',
            ADD COLUMN timeline_color VARCHAR(50) DEFAULT 't-frio',
            ADD COLUMN match_icon VARCHAR(50) DEFAULT 'house-heart',
            ADD COLUMN match_color VARCHAR(50) DEFAULT 'thermal-none',
            ADD COLUMN info_icon VARCHAR(50) DEFAULT 'message-square-text',
            ADD COLUMN info_color VARCHAR(50) DEFAULT 'thermal-none';
        ");

        // 2. Actualizar la función del Trigger con toda la lógica de negocio visual
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_calculate_lead_score() RETURNS TRIGGER AS $$
            BEGIN
                -- Cálculo del Score Total (Clamp 0-100)
                NEW.score_total := GREATEST(0, LEAST(100, 
                    NEW.score_engagement + 
                    NEW.score_finance + 
                    NEW.score_timeline + 
                    NEW.score_match + 
                    NEW.score_info
                ));

                -- Lógica Engagement
                IF NEW.score_engagement >= 24 THEN NEW.eng_color := 'thermal-extreme';
                ELSIF NEW.score_engagement >= 18 THEN NEW.eng_color := 'thermal-high';
                ELSIF NEW.score_engagement >= 12 THEN NEW.eng_color := 'thermal-mid';
                ELSIF NEW.score_engagement >= 6 THEN NEW.eng_color := 'thermal-low';
                ELSE NEW.eng_color := 'thermal-none';
                END IF;

                -- Lógica Finance
                IF NEW.score_finance >= 30 THEN 
                    NEW.fin_icon := 'banknote'; NEW.fin_color := 'thermal-finance-extreme';
                ELSIF NEW.score_finance >= 25 THEN 
                    NEW.fin_icon := 'landmark'; NEW.fin_color := 'thermal-finance-high';
                ELSIF NEW.score_finance >= 15 THEN 
                    NEW.fin_icon := 'landmark'; NEW.fin_color := 'thermal-mid';
                ELSIF NEW.score_finance >= 5 THEN 
                    NEW.fin_icon := 'wallet'; NEW.fin_color := 'thermal-low';
                ELSE 
                    NEW.fin_icon := 'wallet'; NEW.fin_color := 'thermal-none';
                END IF;

                -- Lógica Timeline
                IF NEW.score_timeline >= 20 THEN NEW.timeline_label := 'INMEDIATO'; NEW.timeline_color := 't-inmediato';
                ELSIF NEW.score_timeline >= 18 THEN NEW.timeline_label := 'CALIENTE'; NEW.timeline_color := 't-caliente';
                ELSIF NEW.score_timeline >= 15 THEN NEW.timeline_label := 'TIBIO'; NEW.timeline_color := 't-tibio';
                ELSIF NEW.score_timeline >= 10 THEN NEW.timeline_label := 'MEDIO PLAZO'; NEW.timeline_color := 't-medio';
                ELSIF NEW.score_timeline >= 8 THEN NEW.timeline_label := 'INDEFINIDO'; NEW.timeline_color := 't-indefinido';
                ELSIF NEW.score_timeline >= 5 THEN NEW.timeline_label := 'LARGO PLAZO'; NEW.timeline_color := 't-largo';
                ELSE NEW.timeline_label := 'FRÍO'; NEW.timeline_color := 't-frio';
                END IF;

                -- Lógica Match
                IF NEW.score_match >= 13 THEN NEW.match_color := 'thermal-extreme';
                ELSIF NEW.score_match >= 10 THEN NEW.match_color := 'thermal-high';
                ELSIF NEW.score_match >= 7 THEN NEW.match_color := 'thermal-mid';
                ELSIF NEW.score_match >= 4 THEN NEW.match_color := 'thermal-low';
                ELSE NEW.match_color := 'thermal-none';
                END IF;

                -- Lógica Info/Calidad
                IF NEW.score_info >= 5 THEN NEW.info_color := 'thermal-extreme';
                ELSIF NEW.score_info >= 3 THEN NEW.info_color := 'thermal-high';
                ELSIF NEW.score_info >= 1 THEN NEW.info_color := 'thermal-mid';
                ELSE NEW.info_color := 'thermal-none';
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 3. Actualizar registros existentes para aplicar la nueva lógica
        DB::statement("UPDATE crm_leads SET score_total = score_total");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE crm_leads 
            DROP COLUMN IF EXISTS eng_icon, DROP COLUMN IF EXISTS eng_color,
            DROP COLUMN IF EXISTS fin_icon, DROP COLUMN IF EXISTS fin_color,
            DROP COLUMN IF EXISTS timeline_label, DROP COLUMN IF EXISTS timeline_color,
            DROP COLUMN IF EXISTS match_icon, DROP COLUMN IF EXISTS match_color,
            DROP COLUMN IF EXISTS info_icon, DROP COLUMN IF EXISTS info_color;
        ");
    }
};
