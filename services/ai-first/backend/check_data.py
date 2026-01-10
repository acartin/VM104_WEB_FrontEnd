import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def check_data():
    async with engine.connect() as conn:
        # Check users
        res = await conn.execute(text("SELECT count(*) FROM auth_users"))
        print(f"Total Auth Users: {res.scalar()}")
        
        # Check roles
        res = await conn.execute(text("SELECT name, slug FROM auth_roles"))
        print("\nAvailable Roles:")
        for row in res.all():
            print(f"- {row.name} ({row.slug})")
            
        # Check lead_leads
        res = await conn.execute(text("SELECT count(*) FROM lead_leads"))
        print(f"\nTotal Leads: {res.scalar()}")
        
        # Check assignments in lead_leads
        res = await conn.execute(text("SELECT assigned_user_id, count(*) FROM lead_leads WHERE assigned_user_id IS NOT NULL GROUP BY assigned_user_id"))
        print("\nAssignments per User ID (in lead_leads):")
        for row in res.all():
            print(f"User {row[0]}: {row[1]} leads")
            
        # Check appointments
        res = await conn.execute(text("SELECT count(*) FROM lead_appointments"))
        print(f"Total Appointments: {res.scalar()}")
        
        # Check if any auth_user is assigned to leads
        res = await conn.execute(text("""
            SELECT u.id, u.email, count(l.id) 
            FROM auth_users u 
            JOIN lead_leads l ON u.id = l.assigned_user_id 
            GROUP BY u.id, u.email
        """))
        print("\nAuth Users with assigned leads:")
        for row in res.all():
            print(f"User {row.email} ({row.id}): {row[2]} leads")

if __name__ == "__main__":
    asyncio.run(check_data())
