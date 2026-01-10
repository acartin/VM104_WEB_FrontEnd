import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def check():
    async with engine.connect() as conn:
        res = await conn.execute(text("""
            SELECT conname, pg_get_constraintdef(oid) 
            FROM pg_constraint 
            WHERE conrelid = 'lead_appointments'::regclass 
              AND contype = 'c'
        """))
        for row in res.all():
            print(f"Constraint {row[0]}: {row[1]}")

if __name__ == "__main__":
    asyncio.run(check())
