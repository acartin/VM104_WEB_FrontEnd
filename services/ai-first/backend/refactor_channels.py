import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def refactor_channels():
    async with engine.begin() as conn:
        print("Refactoring 'lead_contact_channels'...")
        
        # 1. Add channel_id column
        print("Adding 'channel_id' column...")
        await conn.execute(text("ALTER TABLE lead_contact_channels ADD COLUMN IF NOT EXISTS channel_id INTEGER"))
        
        # 2. Add FK constraint
        print("Adding Foreign Key constraint...")
        # Check if constraint exists effectively or just try adding it. 
        # Since we are in dev/fix mode, we'll try adding.
        try:
             await conn.execute(text("ALTER TABLE lead_contact_channels ADD CONSTRAINT fk_contact_channels_channel FOREIGN KEY (channel_id) REFERENCES lead_communication_channels(id) ON DELETE SET NULL"))
        except Exception as e:
            print(f"Constraint might already exist: {e}")

        # 3. Drop type column
        print("Dropping 'type' column...")
        await conn.execute(text("ALTER TABLE lead_contact_channels DROP COLUMN IF EXISTS type"))
        
        print("Refactor complete.")

    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(refactor_channels())
