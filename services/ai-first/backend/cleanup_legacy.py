import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def cleanup_legacy():
    async with engine.begin() as conn:
        print("Cleaning up 'lead_communication_channels'...")
        
        # Drop category_id if exists
        print("Dropping 'category_id' column...")
        await conn.execute(text("ALTER TABLE lead_communication_channels DROP COLUMN IF EXISTS category_id"))
        
        print("Cleanup complete.")

    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(cleanup_legacy())
