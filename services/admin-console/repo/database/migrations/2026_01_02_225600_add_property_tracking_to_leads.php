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
        // 1. Add property tracking fields to lead_leads
        DB::statement("
            ALTER TABLE lead_leads
            ADD COLUMN source_property_ref VARCHAR(255),
            ADD COLUMN source_property_url TEXT,
            ADD COLUMN estimated_value DECIMAL(15,2),
            ADD COLUMN property_snapshot JSONB;
        ");

        // 2. Add external reference and URL to lead_properties
        DB::statement("
            ALTER TABLE lead_properties
            ADD COLUMN external_ref VARCHAR(255) UNIQUE,
            ADD COLUMN public_url TEXT;
        ");

        // 3. Create index for faster lookups on source_property_ref
        DB::statement("
            CREATE INDEX idx_leads_source_property_ref 
            ON lead_leads(source_property_ref) 
            WHERE source_property_ref IS NOT NULL;
        ");

        // 4. Create index on external_ref for property lookups
        DB::statement("
            CREATE INDEX idx_properties_external_ref 
            ON lead_properties(external_ref) 
            WHERE external_ref IS NOT NULL;
        ");

        // 5. Add comments for documentation
        DB::statement("
            COMMENT ON COLUMN lead_leads.source_property_ref IS 
            'Unique identifier of the property from external source (e.g., wp-12345, mls-abc-789). Used for deduplication in funnel analytics.';
        ");

        DB::statement("
            COMMENT ON COLUMN lead_leads.source_property_url IS 
            'Direct URL to the property listing. Captured at lead creation for historical reference.';
        ");

        DB::statement("
            COMMENT ON COLUMN lead_leads.estimated_value IS 
            'Property price snapshot at the moment of lead creation. Immutable even if property is deleted or price changes.';
        ");

        DB::statement("
            COMMENT ON COLUMN lead_leads.property_snapshot IS 
            'JSON snapshot of property details at lead creation time. Example: {\"title\": \"...\", \"address\": \"...\", \"bedrooms\": 3}';
        ");

        DB::statement("
            COMMENT ON COLUMN lead_properties.external_ref IS 
            'External reference ID from source system (WordPress post_id, MLS listing ID, etc.). Must be unique.';
        ");

        DB::statement("
            COMMENT ON COLUMN lead_properties.public_url IS 
            'Public URL where this property is listed. Used for lead attribution and click-through tracking.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes first
        DB::statement("DROP INDEX IF EXISTS idx_leads_source_property_ref;");
        DB::statement("DROP INDEX IF EXISTS idx_properties_external_ref;");

        // Remove columns from lead_leads
        DB::statement("
            ALTER TABLE lead_leads
            DROP COLUMN IF EXISTS source_property_ref,
            DROP COLUMN IF EXISTS source_property_url,
            DROP COLUMN IF EXISTS estimated_value,
            DROP COLUMN IF EXISTS property_snapshot;
        ");

        // Remove columns from lead_properties
        DB::statement("
            ALTER TABLE lead_properties
            DROP COLUMN IF EXISTS external_ref,
            DROP COLUMN IF EXISTS public_url;
        ");
    }
};
