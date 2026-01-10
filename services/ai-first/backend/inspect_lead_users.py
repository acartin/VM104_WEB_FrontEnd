import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def inspect():
    async with engine.connect() as conn:
        res = await conn.execute(text("""
            SELECT column_name, data_type, is_nullable
            FROM information_schema.columns
            WHERE table_name = 'lead_users'
            ORDER BY ordinal_position;
        """))
        for row in res.all():
            print(f"{row.column_name}: {row.data_type} (Nullable: {row.is_nullable})")

if __name__ == "__main__":
    asyncio.run(inspect())
