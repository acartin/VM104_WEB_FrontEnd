import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def verify():
    async with engine.connect() as conn:
        print("Checking for new tables...")
        res = await conn.execute(text("SELECT table_name FROM information_schema.tables WHERE table_name IN ('lead_contacts', 'lead_contact_channels')"))
        tables = [r[0] for r in res.fetchall()]
        print(f"Found tables: {tables}")

if __name__ == "__main__":
    asyncio.run(verify())
