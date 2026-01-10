import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def update_categories():
    async with engine.begin() as conn:
        print("Refactoring 'lead_channel_categories'...")
        
        # 1. Add icon column
        print("Adding 'icon' column...")
        await conn.execute(text("ALTER TABLE lead_channel_categories ADD COLUMN IF NOT EXISTS icon VARCHAR"))
        
        print("Done.")

    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(update_categories())
