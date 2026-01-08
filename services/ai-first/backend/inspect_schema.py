import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def inspect_users():
    async with engine.connect() as conn:
        try:
            # Inspect lead_client_user
            query = text("""
                SELECT column_name, data_type, is_nullable
                FROM information_schema.columns
                WHERE table_name = 'lead_client_user';
            """)
            result = await conn.execute(query)
            print("--- LEAD_CLIENT_USER COLUMNS ---")
            for row in result:
                print(f"{row.column_name}: {row.data_type} (Nullable: {row.is_nullable})")
        except Exception as e:
            print(f"Error: {e}")

if __name__ == "__main__":
    asyncio.run(inspect_users())
