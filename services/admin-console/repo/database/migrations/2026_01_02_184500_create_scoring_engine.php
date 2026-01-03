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
        // 1. Crear la tabla de definiciones (El "Motor de Inteligencia")
        Schema::create('lead_scoring_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('criterion'); // engagement, finance, timeline, match, info, priority
            $table->integer('min_score')->default(0);
            $table->integer('max_score')->default(100);
            $table->string('label');
            $table->text('meaning');
            $table->string('icon')->nullable();
            $table->string('color');
            $table->boolean('is_active')->default(true);
            
            $table->index(['criterion', 'min_score', 'max_score']);
        });

        // 2. Poblar con la Matriz Oficial del documento
        $definitions = [
            // CRITERIO 1: INTERÉS Y ENGAGEMENT
            ['engagement', 24, 30, 'EXTREMO', 'Intención compra YA / Cita agendada', 'message-square-heart', 'thermal-extreme'],
            ['engagement', 18, 23, 'ALTO', 'Buscando activamente / Comparando', 'message-square-heart', 'thermal-high'],
            ['engagement', 12, 17, 'MODERADO', 'Interés informativo / Responde 3+ preg', 'message-square-heart', 'thermal-mid'],
            ['engagement', 6, 11, 'BAJO', 'Curioseando / Responde 1-2 preg', 'message-square-heart', 'thermal-low'],
            ['engagement', -20, 5, 'NULO', 'Rechazo explícito / Inactividad', 'message-square-heart', 'thermal-none'],

            // CRITERIO 2: CAPACIDAD FINANCIERA
            ['finance', 30, 30, 'SOBRE-CALIFICADO', 'Puede pagar Cash o 2x budget', 'banknote', 'thermal-finance-extreme'],
            ['finance', 25, 29, 'BIEN CALIFICADO', 'Ingresos cubren 40%+ del precio', 'landmark', 'thermal-finance-high'],
            ['finance', 15, 24, 'CALIFICADO JUSTO', 'Ingresos cubren 30-40%', 'landmark', 'thermal-mid'],
            ['finance', 5, 14, 'SUB-CALIFICADO', 'Ingresos cubren <30%', 'wallet', 'thermal-low'],
            ['finance', 0, 4, 'NO CALIFICADO', 'Sin ingresos o Deudas > Ingresos', 'wallet', 'thermal-none'],
            ['finance', -10, -1, 'EVASIVO', 'No dio info / Evade preguntas', 'wallet', 'thermal-none'],

            // CRITERIO 3: TIMELINE / URGENCIA
            ['timeline', 20, 20, 'INMEDIATO', 'Esta semana / Urgente', 'clock', 't-inmediato'],
            ['timeline', 18, 19, 'CALIENTE', 'Este mes', 'clock', 't-caliente'],
            ['timeline', 15, 17, 'TIBIO', '1 a 3 meses', 'clock', 't-tibio'],
            ['timeline', 10, 14, 'MEDIO PLAZO', '3 a 6 meses', 'clock', 't-medio'],
            ['timeline', 8, 9, 'INDEFINIDO', 'No sabe / Depende', 'clock', 't-indefinido'],
            ['timeline', 5, 7, 'LARGO PLAZO', '6 a 12 meses', 'clock', 't-largo'],
            ['timeline', 0, 4, 'FRÍO', '+1 año / Solo viendo', 'clock', 't-frio'],

            // CRITERIO 4: MATCH / INVENTARIO
            ['match', 13, 15, 'PERFECTO', 'Tenemos exactamente lo que busca', 'house-heart', 'thermal-extreme'],
            ['match', 10, 12, 'ALTO', 'Coincide en zona y presupuesto', 'house-heart', 'thermal-high'],
            ['match', 7, 9, 'MEDIO', 'Coincide solo en presupuesto', 'house-heart', 'thermal-mid'],
            ['match', 4, 6, 'BAJO', 'Busca zona fuera de inventario', 'house-heart', 'thermal-low'],
            ['match', 0, 3, 'SIN COINCIDENCIA', 'Requerimientos no disponibles', 'house-heart', 'thermal-none'],

            // CRITERIO 5: INFO / CALIDAD DE DATOS
            ['info', 5, 5, 'ÍNTEGRO', 'Perfil completo y verificado', 'message-square-text', 'thermal-extreme'],
            ['info', 3, 4, 'BUENO', 'Datos básicos verificados', 'message-square-text', 'thermal-high'],
            ['info', 1, 2, 'INCOMPLETO', 'Falta email o teléfono', 'message-square-text', 'thermal-mid'],
            ['info', -3, 0, 'SOSPECHOSO', 'Datos falsos / Evasivo', 'message-square-text', 'thermal-none'],

            // CATÁLOGO MAESTRO (PRIORIDAD)
            ['priority', 90, 100, 'HOT', 'Prioridad 1: Cierre inminente / Cita agendada.', 'gauge', 'thermal-extreme'],
            ['priority', 70, 89, 'WARM', 'Prioridad 2: Interés sólido / En seguimiento activo.', 'gauge', 'thermal-high'],
            ['priority', 50, 69, 'QUALIFIED', 'Prioridad 3: Prospecto filtrado con éxito.', 'gauge', 'thermal-mid'],
            ['priority', 0, 49, 'COLD', 'Prioridad 4: Seguimiento preventivo / Etapa inicial.', 'gauge', 'thermal-none'],
        ];

        foreach ($definitions as $def) {
            DB::table('lead_scoring_definitions')->insert([
                'criterion' => $def[0],
                'min_score' => $def[1],
                'max_score' => $def[2],
                'label' => $def[3],
                'meaning' => $def[4],
                'icon' => $def[5],
                'color' => $def[6],
                'is_active' => true,
            ]);
        }

        // 3. Evolucionar la función de PostgreSQL para que use el Catálogo
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_calculate_lead_score() RETURNS TRIGGER AS $$
            DECLARE
                def_row RECORD;
            BEGIN
                -- 1. Cálculo del Score Total (Clamp 0-100)
                NEW.score_total := GREATEST(0, LEAST(100, 
                    COALESCE(NEW.score_engagement, 0) + 
                    COALESCE(NEW.score_finance, 0) + 
                    COALESCE(NEW.score_timeline, 0) + 
                    COALESCE(NEW.score_match, 0) + 
                    COALESCE(NEW.score_info, 0)
                ));

                -- 2. Lookup automático de Iconografía y Color desde el Catálogo para los 5 pilares
                
                -- Engagement
                SELECT icon, color INTO NEW.eng_icon, NEW.eng_color 
                FROM lead_scoring_definitions 
                WHERE criterion = 'engagement' AND NEW.score_engagement BETWEEN min_score AND max_score LIMIT 1;

                -- Finance
                SELECT icon, color INTO NEW.fin_icon, NEW.fin_color 
                FROM lead_scoring_definitions 
                WHERE criterion = 'finance' AND NEW.score_finance BETWEEN min_score AND max_score LIMIT 1;

                -- Timeline
                SELECT label, color INTO NEW.timeline_label, NEW.timeline_color 
                FROM lead_scoring_definitions 
                WHERE criterion = 'timeline' AND NEW.score_timeline BETWEEN min_score AND max_score LIMIT 1;

                -- Match
                SELECT icon, color INTO NEW.match_icon, NEW.match_color 
                FROM lead_scoring_definitions 
                WHERE criterion = 'match' AND NEW.score_match BETWEEN min_score AND max_score LIMIT 1;

                -- Info
                SELECT icon, color INTO NEW.info_icon, NEW.info_color 
                FROM lead_scoring_definitions 
                WHERE criterion = 'info' AND NEW.score_info BETWEEN min_score AND max_score LIMIT 1;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        // 4. Actualizar todos los registros existentes para aplicar la nueva inteligencia
        DB::statement("UPDATE lead_leads SET score_total = score_total");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_scoring_definitions');
        
        // El trigger volvería a su estado anterior o se quedaría sin lookup
    }
};
