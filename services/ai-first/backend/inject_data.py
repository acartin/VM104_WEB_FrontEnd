import asyncio
import uuid
from datetime import datetime, timedelta
from sqlalchemy import text
from app.dal.database import engine

async def inject_test_data():
    async with engine.connect() as conn:
        # 1. Get a user
        res = await conn.execute(text("SELECT id, email FROM auth_users LIMIT 1"))
        user = res.fetchone()
        if not user:
            print("No users found to assign data.")
            return
        
        user_id = user.id
        print(f"Injecting data for user: {user.email} ({user_id})")
        
        # 2. Assign 5 leads to this user
        # We'll take the first 5 leads available
        res = await conn.execute(text("SELECT id FROM lead_leads LIMIT 5"))
        leads = res.all()
        
        for i, lead in enumerate(leads):
            lead_id = lead.id
            # Assign lead
            await conn.execute(text("""
                UPDATE lead_leads 
                SET assigned_user_id = :uid, 
                    score_total = :score,
                    status = 'Follow-up'
                WHERE id = :lid
            """), {"uid": user_id, "score": 80 + i, "lid": lead_id})
            
            # Create an appointment for some leads
            if i < 3:
                appt_id = uuid.uuid4()
                # Use contact_id if lead_leads had it, but lead_appointments has lead_id and contact_id.
                # In our schema, lead_leads has id and lead_appointments has lead_id.
                # Let's see if lead_leads has a contact_id linked (it doesn't seem to have it in the description, 
                # but auth_users has contact_id). 
                # Actually, lead_appointments has contact_id as NOT NULL. 
                # Let's check lead_contacts.
                res_c = await conn.execute(text("SELECT id FROM lead_contacts LIMIT 1"))
                contact = res_c.fetchone()
                contact_id = contact.id if contact else uuid.uuid4()
                
                await conn.execute(text("""
                    INSERT INTO lead_appointments (id, lead_id, contact_id, scheduled_at, meeting_type, status)
                    VALUES (:id, :lid, :cid, :at, :type, :status)
                """), {
                    "id": appt_id,
                    "lid": lead_id,
                    "cid": contact_id,
                    "at": datetime.now() + timedelta(days=i),
                    "type": "Video Call" if i % 2 == 0 else "Visit",
                    "status": "Scheduled"
                })
        
        await conn.commit()
        print("Test data injected successfully.")

if __name__ == "__main__":
    asyncio.run(inject_test_data())
