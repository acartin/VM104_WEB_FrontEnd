import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def inspect():
    async with engine.connect() as conn:
        print("--- lead_communication_channels ---")
        res = await conn.execute(text("SELECT id, name FROM lead_communication_channels"))
        for row in res.fetchall():
            print(row)
            
        print("\n--- lead_channel_categories ---")
        res = await conn.execute(text("SELECT id, name FROM lead_channel_categories"))
        for row in res.fetchall():
            print(row)

if __name__ == "__main__":
    asyncio.run(inspect())
