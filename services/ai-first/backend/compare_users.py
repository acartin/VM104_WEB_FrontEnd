import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def compare_users():
    async with engine.connect() as conn:
        print("--- NEW AUTH USERS ---")
        res = await conn.execute(text("SELECT id, email FROM auth_users"))
        new_users = {row.email: row.id for row in res.all()}
        for email, uid in new_users.items():
            print(f"{email}: {uid}")
            
        print("\n--- LEGACY LEAD USERS ---")
        res = await conn.execute(text("SELECT id, email FROM lead_users"))
        legacy_users = {row.email: row.id for row in res.all()}
        for email, uid in legacy_users.items():
            print(f"{email}: {uid}")
            
        print("\n--- MATCHING USERS (by Email) ---")
        for email in new_users:
            if email in legacy_users:
                match = "YES (same ID)" if new_users[email] == legacy_users[email] else "NO (different ID)"
                print(f"{email}: {match}")

if __name__ == "__main__":
    asyncio.run(compare_users())
