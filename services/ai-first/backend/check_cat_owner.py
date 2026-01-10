import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def check_cat_owner():
    async with engine.connect() as conn:
        res = await conn.execute(text("SELECT tableowner FROM pg_tables WHERE tablename = 'lead_channel_categories'"))
        print(f"Owner: {res.scalar()}")

if __name__ == "__main__":
    asyncio.run(check_cat_owner())
