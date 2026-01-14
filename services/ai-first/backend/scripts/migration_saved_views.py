import asyncio
import os
import sys
from sqlalchemy import text
from sqlalchemy.ext.asyncio import create_async_engine

# Add parent directory to path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from app.dal.database import DATABASE_URL

async def migrate():
    engine = create_async_engine(DATABASE_URL)
    
    query = text("""
        CREATE TABLE IF NOT EXISTS saved_views (
            id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
            user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            client_id UUID NOT NULL REFERENCES clients(id) ON DELETE CASCADE,
            name VARCHAR(255) NOT NULL,
            icon VARCHAR(50) DEFAULT '📁',
            filters JSONB NOT NULL,
            is_default BOOLEAN DEFAULT false,
            deleted_at TIMESTAMP,
            created_at TIMESTAMP DEFAULT NOW(),
            updated_at TIMESTAMP DEFAULT NOW()
        );

        CREATE INDEX IF NOT EXISTS idx_saved_views_user_client ON saved_views(user_id, client_id);
        CREATE INDEX IF NOT EXISTS idx_saved_views_client ON saved_views(client_id);
    """)
    
    print(f"Connecting to database...")
    async with engine.begin() as conn:
        print("Executing migration...")
        await conn.execute(query)
        print("Migration completed successfully.")
    
    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(migrate())
