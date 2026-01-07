
import psycopg2
import sys

def get_db_url():
    # Hardcoded from .env content
    return "postgresql://acartin:Toyota_15@192.168.0.31:5432/agentic"

def inspect_db():
    url = get_db_url()
    print(f"Connecting to: {url}")
    
    try:
        conn = psycopg2.connect(url)
        cursor = conn.cursor()
        
        # Get all tables
        cursor.execute("""
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public'
        """)
        tables = [row[0] for row in cursor.fetchall()]
        print("Tables found:", tables)
        
        # Inspect relevant tables
        targets = ['users', 'roles', 'model_has_roles', 'clients']
        for table in tables:
            # Check if table matches any target string
            if any(t in table for t in targets):
                print(f"\nSchema for {table}:")
                cursor.execute(f"""
                    SELECT column_name, data_type, is_nullable
                    FROM information_schema.columns 
                    WHERE table_name = '{table}'
                    ORDER BY ordinal_position
                """)
                columns = cursor.fetchall()
                for col in columns:
                    print(f"  - {col[0]} ({col[1]}) Nullable: {col[2]}")

        cursor.close()
        conn.close()

    except Exception as e:
        print(f"DB Error: {e}")

if __name__ == "__main__":
    inspect_db()
