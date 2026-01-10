import asyncio
from uuid import UUID
from sqlalchemy import text
from app.dal.database import engine

async def final_check():
    user_id = UUID('e18b47ae-4067-420d-861b-7f732409ade4')
    async with engine.connect() as conn:
        res = await conn.execute(text("SELECT count(*) FROM lead_leads WHERE assigned_user_id = :uid"), {"uid": user_id})
        print(f"Leads assigned to user: {res.scalar()}")
        
        res = await conn.execute(text("""
            SELECT l.full_name, count(a.id) as appt_count
            FROM lead_leads l 
            LEFT JOIN lead_appointments a ON l.id = a.lead_id
            WHERE l.assigned_user_id = :uid
            GROUP BY l.full_name
        """), {"uid": user_id})
        print("\nLeads and their appointment counts:")
        for row in res.all():
            print(f"- {row.full_name}: {row.appt_count} appointments")

if __name__ == "__main__":
    asyncio.run(final_check())
