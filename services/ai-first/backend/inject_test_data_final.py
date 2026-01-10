import asyncio
import uuid
from datetime import datetime, timedelta
from sqlalchemy import text
from app.dal.database import engine

async def inject_complete_test_data():
    async with engine.begin() as conn:
        # 1. Select the target user from auth_users
        res = await conn.execute(text("SELECT id, email, name FROM auth_users WHERE email = 'cocacola-admin@cocacola.com'"))
        user = res.fetchone()
        if not user:
            print("Target user not found.")
            return
        
        user_id = user.id
        print(f"Targeting user: {user.email} ({user_id})")
        
        # 2. Check if user exists in lead_users, if not create shadow entry
        res_legacy = await conn.execute(text("SELECT id FROM lead_users WHERE id = :id"), {"id": user_id})
        if not res_legacy.fetchone():
            print(f"Creating shadow entry in lead_users for {user_id}")
            await conn.execute(text("""
                INSERT INTO lead_users (id, name, email, password, available_status, can_receive_leads)
                VALUES (:id, :name, :email, 'shadow_pass', 'online', true)
            """), {
                "id": user_id,
                "name": user.name or "Admin Coca",
                "email": user.email
            })
        
        # 3. Assign 5 leads to this user
        res_leads = await conn.execute(text("SELECT id FROM lead_leads LIMIT 5"))
        leads = res_leads.all()
        
        types = ['video_call', 'in_person', 'phone']
        
        for i, lead in enumerate(leads):
            lead_id = lead.id
            print(f"Assigning lead {lead_id} to user {user_id}")
            await conn.execute(text("""
                UPDATE lead_leads 
                SET assigned_user_id = :uid, 
                    score_total = :score,
                    status = 'Follow-up',
                    deleted_at = NULL
                WHERE id = :lid
            """), {"uid": user_id, "score": 85 + i, "lid": lead_id})
            
            # 4. Create appointments for some leads
            if i < 3:
                # Find a contact
                res_c = await conn.execute(text("SELECT id FROM lead_contacts LIMIT 1"))
                contact = res_c.fetchone()
                contact_id = contact.id if contact else uuid.uuid4()
                
                print(f"Creating appointment for lead {lead_id} type {types[i]}")
                await conn.execute(text("""
                    INSERT INTO lead_appointments (id, lead_id, contact_id, scheduled_at, meeting_type, status, duration_minutes, timezone)
                    VALUES (:id, :lid, :cid, :at, :type, 'Scheduled', 60, 'UTC')
                """), {
                    "id": uuid.uuid4(),
                    "lid": lead_id,
                    "cid": contact_id,
                    "at": datetime.now() + timedelta(days=i, hours=2),
                    "type": types[i]
                })
        
        print("\nSUCCESS: Test data injected.")

if __name__ == "__main__":
    asyncio.run(inject_complete_test_data())
