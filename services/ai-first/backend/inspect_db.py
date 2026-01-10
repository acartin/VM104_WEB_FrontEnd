import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def inspect():
    async with engine.connect() as conn:
        for table in ['lead_leads', 'lead_appointments']:
            print(f"\n--- Structure of {table} ---")
            query = text(f"""
                SELECT column_name, data_type, is_nullable
                FROM information_schema.columns
                WHERE table_name = '{table}'
                ORDER BY ordinal_position;
            """)
            result = await conn.execute(query)
            for row in result.all():
                print(f"{row.column_name}: {row.data_type} (Nullable: {row.is_nullable})")

if __name__ == "__main__":
    asyncio.run(inspect())
