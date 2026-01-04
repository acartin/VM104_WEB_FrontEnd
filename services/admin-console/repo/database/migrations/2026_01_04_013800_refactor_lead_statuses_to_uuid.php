<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Eliminar la restricción de llave foránea actual y limpiar datos
        DB::statement("ALTER TABLE lead_leads DROP COLUMN IF EXISTS status_id");
        DB::statement("DROP TABLE IF EXISTS lead_statuses CASCADE");

        // 2. Crear nueva tabla lead_statuses con UUID e Icono
        DB::statement("
            CREATE TABLE lead_statuses (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                key VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(50) NOT NULL,
                icon VARCHAR(50),
                color VARCHAR(20) DEFAULT 'gray',
                \"order\" INTEGER DEFAULT 0,
                created_at TIMESTAMPTZ DEFAULT NOW(),
                updated_at TIMESTAMPTZ DEFAULT NOW()
            )
        ");

        // 3. Re-añadir columna status_id a lead_leads como UUID
        DB::statement("
            ALTER TABLE lead_leads 
            ADD COLUMN status_id UUID REFERENCES lead_statuses(id)
        ");

        // 4. Seeder de estados operativos (Workflow Only)
        $statuses = [
            [
                'id' => Str::uuid(),
                'key' => 'new',
                'name' => 'New',
                'icon' => 'heroicon-o-sparkles',
                'color' => 'gray',
                'order' => 10,
            ],
            [
                'id' => Str::uuid(),
                'key' => 'action_required',
                'name' => 'Action Required',
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => 'warning',
                'order' => 20,
            ],
            [
                'id' => Str::uuid(),
                'key' => 'contacted',
                'name' => 'Contacted',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'color' => 'info',
                'order' => 30,
            ],
            [
                'id' => Str::uuid(),
                'key' => 'crm_sent',
                'name' => 'Sent to CRM',
                'icon' => 'heroicon-o-arrow-right-on-rectangle',
                'color' => 'success',
                'order' => 40,
            ],
            [
                'id' => Str::uuid(),
                'key' => 'discarded',
                'name' => 'Discarded',
                'icon' => 'heroicon-o-trash',
                'color' => 'danger',
                'order' => 50,
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('lead_statuses')->insert($status);
        }

        // 5. Asignar el estado 'new' a los leads existentes
        $newStatusId = DB::table('lead_statuses')->where('key', 'new')->value('id');
        if ($newStatusId) {
            DB::table('lead_leads')->update(['status_id' => $newStatusId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es posible revertir a bigint de forma segura sin pérdida de datos lógica,
        // pero podemos intentar recrear la estructura básica si fuera necesario.
        DB::statement("ALTER TABLE lead_leads DROP COLUMN IF EXISTS status_id");
        DB::statement("DROP TABLE IF EXISTS lead_statuses");
    }
};
