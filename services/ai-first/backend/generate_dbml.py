import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def generate_dbml():
    async with engine.connect() as conn:
        # 1. Get all tables
        tables_query = text("""
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            ORDER BY table_name;
        """)
        result = await conn.execute(tables_query)
        tables = [r.table_name for r in result.fetchall()]

        dbml_output = []
        
        # 2. Iterate tables to write definition
        for table in tables:
            dbml_output.append(f"Table {table} {{")
            
            # Columns
            col_query = text(f"""
                SELECT column_name, data_type, is_nullable
                FROM information_schema.columns 
                WHERE table_name = '{table}'
                ORDER BY ordinal_position;
            """)
            cols = await conn.execute(col_query)
            
            # PKs
            pk_query = text(f"""
                SELECT kcu.column_name
                FROM information_schema.table_constraints tc
                JOIN information_schema.key_column_usage kcu
                  ON tc.constraint_name = kcu.constraint_name
                  AND tc.table_schema = kcu.table_schema
                WHERE tc.constraint_type = 'PRIMARY KEY'
                  AND tc.table_name = '{table}';
            """)
            pks_res = await conn.execute(pk_query)
            pks = [r.column_name for r in pks_res.fetchall()]

            for col in cols.fetchall():
                settings = []
                if col.column_name in pks:
                    settings.append("pk")
                if col.is_nullable == 'NO':
                    settings.append("not null")
                
                settings_str = f" [{', '.join(settings)}]" if settings else ""
                
                # Clean type (e.g., 'character varying' -> 'varchar')
                curr_type = col.data_type
                if curr_type == 'character varying': curr_type = 'varchar'
                if curr_type == 'timestamp with time zone': curr_type = 'timestamp'
                
                dbml_output.append(f"  {col.column_name} {curr_type}{settings_str}")
            
            dbml_output.append("}\n")

        # 3. Relationships (Foreign Keys)
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
        """)
        fks_res = await conn.execute(fk_query)
        for fk in fks_res.fetchall():
            # Ref: posts.user_id > users.id 
            dbml_output.append(f"Ref: {fk.table_name}.{fk.column_name} > {fk.foreign_table_name}.{fk.foreign_column_name}")

        final_dbml = "\n".join(dbml_output)
        print(final_dbml)

        # Save to file locally inside container (optional, but printing captures it to tool output)
        with open("full_database.dbml", "w") as f:
            f.write(final_dbml)

if __name__ == "__main__":
    asyncio.run(generate_dbml())
