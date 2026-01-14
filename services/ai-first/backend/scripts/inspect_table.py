import asyncio
from sqlalchemy import text
from sqlalchemy.ext.asyncio import create_async_engine
import sys
import os

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from app.config.settings import settings

async def check():
    engine = create_async_engine(settings.database_url)
    async with engine.connect() as conn:
        res = await conn.execute(text("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'lead_leads'"))
        columns = [r for r in res]
        print("Columns in 'lead_leads':")
        for col in columns:
            print(f"- {col[0]} ({col[1]})")
            
        res = await conn.execute(text("SELECT tc.table_name, kcu.column_name, ccu.table_name AS foreign_table_name, ccu.column_name AS foreign_column_name FROM information_schema.table_constraints AS tc JOIN communications_key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name='lead_leads'"))
        # Using a simpler query for foreign keys
        res = await conn.execute(text("""
            SELECT
                kcu.column_name, 
                ccu.table_name AS foreign_table_name,
                ccu.column_name AS foreign_column_name 
            FROM 
                information_schema.table_constraints AS tc 
                JOIN information_schema.key_column_usage AS kcu
                  ON tc.constraint_name = kcu.constraint_name
                  AND tc.table_schema = kcu.table_schema
                JOIN information_schema.constraint_column_usage AS ccu
                  ON ccu.constraint_name = tc.constraint_name
                  AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_name='lead_leads';
        """))
        fks = [r for r in res]
        print("\nForeign Keys in 'lead_leads':")
        for fk in fks:
            print(f"- {fk[0]} -> {fk[1]}({fk[2]})")
            
    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(check())
