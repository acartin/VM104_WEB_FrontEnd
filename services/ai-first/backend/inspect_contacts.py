import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def inspect_contacts_schema():
    async with engine.connect() as conn:
        print("\n=== SCHEMA INSPECTION: CONTACTS & CHANNELS ===\n")

        # 1. List of potentially relevant tables
        tables_to_check = [
            'lead_client_channels', 
            'lead_contacts', 
            'contacts', 
            'communication_channels',
            'lead_communication_channels'
        ]
        
        # Check actual existence
        query_tables = text("""
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public'
            AND (table_name LIKE '%contact%' OR table_name LIKE '%channel%');
        """)
        result = await conn.execute(query_tables)
        found_tables = [r.table_name for r in result.fetchall()]
        print(f"Found Tables: {found_tables}")

        # 2. Inspect Columns for found tables
        for table in found_tables:
            print(f"\nTABLE: {table}")
            col_query = text(f"""
                SELECT column_name, data_type 
                FROM information_schema.columns 
                WHERE table_name = '{table}'
            """)
            cols = await conn.execute(col_query)
            for c in cols:
                 print(f"  - {c.column_name}: {c.data_type}")

if __name__ == "__main__":
    asyncio.run(inspect_contacts_schema())
