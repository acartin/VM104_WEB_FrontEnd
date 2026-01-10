import asyncio
from uuid import UUID
from sqlalchemy import text
from app.dal.database import engine

async def diagnose():
    user_id = UUID('e18b47ae-4067-420d-861b-7f732409ade4')
    async with engine.connect() as conn:
        print(f"Checking leads for User ID: {user_id}")
        query = text("SELECT id, full_name, assigned_user_id, deleted_at FROM lead_leads WHERE assigned_user_id = :uid")
        res = await conn.execute(query, {"uid": user_id})
        rows = res.all()
        print(f"Total leads found (without filter): {len(rows)}")
        for r in rows:
            print(f"- {r.full_name} ({r.id}) | Deleted At: {r.deleted_at}")
            
        print("\n--- ACTIVE LEADS (deleted_at IS NULL) ---")
        query_active = text("SELECT id, full_name FROM lead_leads WHERE assigned_user_id = :uid AND deleted_at IS NULL")
        res_active = await conn.execute(query_active, {"uid": user_id})
        for r in res_active.all():
            print(f"- {r.full_name} ({r.id})")

if __name__ == "__main__":
    asyncio.run(diagnose())
