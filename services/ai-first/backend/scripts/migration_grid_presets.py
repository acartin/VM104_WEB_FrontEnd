import asyncio
import os
import sys
import json
from sqlalchemy import text
from sqlalchemy.ext.asyncio import create_async_engine

# Add parent directory to path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from app.config.settings import settings

async def migrate():
    engine = create_async_engine(settings.database_url)
    
    statements = [
        """
        CREATE TABLE IF NOT EXISTS lead_grid_presets (
            id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            user_id UUID NOT NULL REFERENCES auth_users(id) ON DELETE CASCADE,
            client_id UUID NOT NULL REFERENCES lead_clients(id) ON DELETE CASCADE,
            grid_id VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            icon VARCHAR(50) DEFAULT '📁',
            config JSONB NOT NULL,
            is_default BOOLEAN DEFAULT false,
            deleted_at TIMESTAMP,
            created_at TIMESTAMP DEFAULT NOW(),
            updated_at TIMESTAMP DEFAULT NOW()
        );
        """,
        "CREATE INDEX IF NOT EXISTS idx_presets_user_client_grid ON lead_grid_presets(user_id, client_id, grid_id);",
        "CREATE INDEX IF NOT EXISTS idx_presets_client_grid ON lead_grid_presets(client_id, grid_id);"
    ]
    
    print(f"Connecting to database...")
    async with engine.begin() as conn:
        for stmt in statements:
            print(f"Executing: {stmt[:50]}...")
            await conn.execute(text(stmt))
        print("Migration completed successfully.")
    
    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(migrate())
