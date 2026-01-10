import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def update_contact_channels():
    async with engine.begin() as conn:
        print("Refactoring 'lead_contact_channels'...")
        
        # 1. Add category_id column
        print("Adding 'category_id' column...")
        await conn.execute(text("ALTER TABLE lead_contact_channels ADD COLUMN IF NOT EXISTS category_id INTEGER"))
        
        # 2. Add FK constraint
        print("Adding Foreign Key constraint...")
        try:
             await conn.execute(text("ALTER TABLE lead_contact_channels ADD CONSTRAINT fk_contact_channels_category FOREIGN KEY (category_id) REFERENCES lead_channel_categories(id) ON DELETE SET NULL"))
        except Exception as e:
            print(f"Constraint might already exist: {e}")

        # 3. Drop legacy channel_id column
        print("Dropping 'channel_id' column...")
        await conn.execute(text("ALTER TABLE lead_contact_channels DROP COLUMN IF EXISTS channel_id"))
        
        print("Refactor complete.")

    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(update_contact_channels())
