import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def analyze_dependencies():
    async with engine.connect() as conn:
        print("\n=== SYSTEM-WIDE DEPENDENCY ANALYSIS (client_id) ===\n")

        # 1. Find ALL explicit Foreign Keys pointing to 'lead_clients'
        print("1. HARD DEPENDENCIES (Foreign Keys to lead_clients):")
        fk_query = text("""
            SELECT
                tc.table_name, 
                kcu.column_name, 
                ccu.table_name AS foreign_table_name,
                ccu.column_name AS foreign_column_name 
            FROM information_schema.table_constraints AS tc 
            JOIN information_schema.key_column_usage AS kcu
              ON tc.constraint_name = kcu.constraint_name
              AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage AS ccu
              ON ccu.constraint_name = tc.constraint_name
              AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY' 
              AND ccu.table_name = 'lead_clients';
        """)
        result = await conn.execute(fk_query)
        fks = result.fetchall()
        if not fks:
            print("  (None found)")
        for fk in fks:
            print(f"  - Table: {fk.table_name} | Column: {fk.column_name} -> lead_clients.id")

        # 2. Find ALL columns named 'client_id' (Implicit/Soft Dependencies)
        print("\n2. SOFT DEPENDENCIES (Columns named 'client_id' without explicit FK):")
        col_query = text("""
            SELECT table_name, data_type 
            FROM information_schema.columns 
            WHERE column_name = 'client_id' 
            AND table_schema = 'public'
            ORDER BY table_name;
        """)
        result = await conn.execute(col_query)
        cols = result.fetchall()
        
        # Filter out those already found in FKs
        fk_tables = [fk.table_name for fk in fks]
        soft_deps = [c for c in cols if c.table_name not in fk_tables]
        
        if not soft_deps:
            print("  (None found - All client_id columns have explicit FKs)")
        for col in soft_deps:
             print(f"  - Table: {col.table_name} (Type: {col.data_type}) [POTENTIAL ORPHAN RISK]")

if __name__ == "__main__":
    asyncio.run(analyze_dependencies())
