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
        // 1. Agregar campos UTM y tracking a lead_leads
        DB::statement("
            ALTER TABLE lead_leads
            ADD COLUMN utm_source VARCHAR(100),
            ADD COLUMN utm_medium VARCHAR(100),
            ADD COLUMN utm_campaign VARCHAR(255),
            ADD COLUMN utm_content VARCHAR(255),
            ADD COLUMN utm_term VARCHAR(255),
            ADD COLUMN click_id VARCHAR(255),
            ADD COLUMN click_id_type VARCHAR(50),
            ADD COLUMN referrer_url TEXT,
            ADD COLUMN landing_page_url TEXT,
            ADD COLUMN user_agent TEXT,
            ADD COLUMN ip_address VARCHAR(45)
        ");

        // 2. Agregar comentarios a las columnas
        DB::statement("COMMENT ON COLUMN lead_leads.utm_source IS 'Traffic source (google, facebook, instagram, etc.)'");
        DB::statement("COMMENT ON COLUMN lead_leads.utm_medium IS 'Marketing medium (cpc, paid, organic, email, etc.)'");
        DB::statement("COMMENT ON COLUMN lead_leads.utm_campaign IS 'Campaign identifier'");
        DB::statement("COMMENT ON COLUMN lead_leads.utm_content IS 'Ad content/variant identifier'");
        DB::statement("COMMENT ON COLUMN lead_leads.utm_term IS 'Paid search keywords'");
        DB::statement("COMMENT ON COLUMN lead_leads.click_id IS 'Platform click ID (gclid, fbclid, ttclid, etc.)'");
        DB::statement("COMMENT ON COLUMN lead_leads.click_id_type IS 'Type of click ID (gclid, fbclid, ttclid, etc.)'");
        DB::statement("COMMENT ON COLUMN lead_leads.referrer_url IS 'Full URL where the lead came from'");
        DB::statement("COMMENT ON COLUMN lead_leads.landing_page_url IS 'First page the lead visited'");
        DB::statement("COMMENT ON COLUMN lead_leads.user_agent IS 'Browser/device user agent string'");
        DB::statement("COMMENT ON COLUMN lead_leads.ip_address IS 'IP address of the lead (IPv4 or IPv6)'");

        // 3. Crear índices para búsquedas comunes
        DB::statement("CREATE INDEX idx_lead_leads_utm_source ON lead_leads(utm_source) WHERE utm_source IS NOT NULL");
        DB::statement("CREATE INDEX idx_lead_leads_utm_campaign ON lead_leads(utm_campaign) WHERE utm_campaign IS NOT NULL");
        DB::statement("CREATE INDEX idx_lead_leads_click_id ON lead_leads(click_id) WHERE click_id IS NOT NULL");

        // 4. Crear tabla lead_conversations
        DB::statement("
            CREATE TABLE lead_conversations (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                lead_id UUID NOT NULL,
                
                -- Platform identification
                platform VARCHAR(50) NOT NULL,
                conversation_id VARCHAR(255),
                
                -- Conversation data (JSONB for efficient storage and querying)
                messages JSONB NOT NULL DEFAULT '[]'::jsonb,
                
                -- Summary and analysis
                summary TEXT,
                sentiment VARCHAR(20),
                
                -- Timestamps
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ended_at TIMESTAMP,
                last_message_at TIMESTAMP,
                
                -- Statistics
                total_messages INTEGER DEFAULT 0,
                bot_messages INTEGER DEFAULT 0,
                lead_messages INTEGER DEFAULT 0,
                
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP,
                
                -- Foreign key
                CONSTRAINT fk_lead_conversations_lead
                    FOREIGN KEY (lead_id)
                    REFERENCES lead_leads(id)
                    ON DELETE CASCADE,
                
                -- Constraints
                CONSTRAINT chk_platform CHECK (platform IN ('whatsapp', 'telegram', 'webchat', 'messenger', 'instagram')),
                CONSTRAINT chk_sentiment CHECK (sentiment IS NULL OR sentiment IN ('positive', 'neutral', 'negative'))
            )
        ");

        // 5. Comentarios en la tabla
        DB::statement("COMMENT ON TABLE lead_conversations IS 'Stores complete chat conversations between bot/agents and leads'");
        DB::statement("COMMENT ON COLUMN lead_conversations.platform IS 'Chat platform: whatsapp, telegram, webchat, messenger, instagram'");
        DB::statement("COMMENT ON COLUMN lead_conversations.conversation_id IS 'External conversation ID from the platform'");
        DB::statement("COMMENT ON COLUMN lead_conversations.messages IS 'Complete conversation as JSON array of message objects'");
        DB::statement("COMMENT ON COLUMN lead_conversations.summary IS 'AI-generated summary of the conversation'");
        DB::statement("COMMENT ON COLUMN lead_conversations.sentiment IS 'Overall sentiment: positive, neutral, negative'");

        // 6. Índices para lead_conversations
        DB::statement("CREATE INDEX idx_lead_conversations_lead_id ON lead_conversations(lead_id)");
        DB::statement("CREATE INDEX idx_lead_conversations_platform ON lead_conversations(platform)");
        DB::statement("CREATE INDEX idx_lead_conversations_started_at ON lead_conversations(started_at)");
        DB::statement("CREATE INDEX idx_lead_conversations_conversation_id ON lead_conversations(conversation_id) WHERE conversation_id IS NOT NULL");
        
        // 7. Índice GIN para búsquedas en JSONB
        DB::statement("CREATE INDEX idx_lead_conversations_messages_gin ON lead_conversations USING GIN (messages)");

        // 8. Trigger para actualizar updated_at
        DB::statement("
            CREATE OR REPLACE FUNCTION update_lead_conversations_updated_at()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER trg_lead_conversations_updated_at
            BEFORE UPDATE ON lead_conversations
            FOR EACH ROW
            EXECUTE FUNCTION update_lead_conversations_updated_at();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop trigger and function
        DB::statement("DROP TRIGGER IF EXISTS trg_lead_conversations_updated_at ON lead_conversations");
        DB::statement("DROP FUNCTION IF EXISTS update_lead_conversations_updated_at()");
        
        // Drop table
        DB::statement("DROP TABLE IF EXISTS lead_conversations");
        
        // Drop indexes from lead_leads
        DB::statement("DROP INDEX IF EXISTS idx_lead_leads_utm_source");
        DB::statement("DROP INDEX IF EXISTS idx_lead_leads_utm_campaign");
        DB::statement("DROP INDEX IF EXISTS idx_lead_leads_click_id");
        
        // Remove columns from lead_leads
        DB::statement("
            ALTER TABLE lead_leads
            DROP COLUMN IF EXISTS utm_source,
            DROP COLUMN IF EXISTS utm_medium,
            DROP COLUMN IF EXISTS utm_campaign,
            DROP COLUMN IF EXISTS utm_content,
            DROP COLUMN IF EXISTS utm_term,
            DROP COLUMN IF EXISTS click_id,
            DROP COLUMN IF EXISTS click_id_type,
            DROP COLUMN IF EXISTS referrer_url,
            DROP COLUMN IF EXISTS landing_page_url,
            DROP COLUMN IF EXISTS user_agent,
            DROP COLUMN IF EXISTS ip_address
        ");
    }
};
