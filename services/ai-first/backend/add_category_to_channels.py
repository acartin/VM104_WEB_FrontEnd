import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def add_category():
    async with engine.begin() as conn:
        print("Refactoring 'lead_communication_channels'...")
        
        # 1. Add category_id column
        print("Adding 'category_id' column...")
        await conn.execute(text("ALTER TABLE lead_communication_channels ADD COLUMN IF NOT EXISTS category_id INTEGER"))
        
        # 2. Add FK constraint
        print("Adding Foreign Key constraint...")
        try:
             await conn.execute(text("ALTER TABLE lead_communication_channels ADD CONSTRAINT fk_comm_channels_category FOREIGN KEY (category_id) REFERENCES lead_channel_categories(id) ON DELETE SET NULL"))
        except Exception as e:
            print(f"Constraint might already exist: {e}")

        print("Refactor complete.")

    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(add_category())
