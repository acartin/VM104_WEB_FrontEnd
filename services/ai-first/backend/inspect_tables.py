import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def inspect_full_schema():
    target_tables = [
        "lead_users", 
        "lead_client_user", 
        "lead_clients", 
        "sessions",
        "filament_users" # Checking if this exists as user mentioned renaming
    ]
    
    # Also check wildcards like 'filament_%'
    
    async with engine.connect() as conn:
        # 1. Get List of all filament tables
        query_filament = text("SELECT table_name FROM information_schema.tables WHERE table_name LIKE 'filament_%'")
        res_filament = await conn.execute(query_filament)
        filament_tables = [r.table_name for r in res_filament]
        
        all_targets = target_tables + filament_tables
        
        print(f"--- ANALYZING TABLES: {all_targets} ---")

        for table in all_targets:
            try:
                print(f"\nTABLE: {table}")
                query = text(f"""
                    SELECT column_name, data_type, is_nullable
                    FROM information_schema.columns
                    WHERE table_name = '{table}';
                """)
                result = await conn.execute(query)
                rows = list(result)
                if not rows:
                    print("  (Table not found or empty definition)")
                for r in rows:
                    print(f"  - {r.column_name}: {r.data_type} ({'NULL' if r.is_nullable=='YES' else 'NOT NULL'})")
            except Exception as e:
                print(f"Error reading {table}: {e}")

if __name__ == "__main__":
    asyncio.run(inspect_full_schema())
