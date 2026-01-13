
import asyncio
import uuid
import random
import string
from sqlalchemy import text
from app.dal.database import engine

def random_string(length=8):
    return ''.join(random.choices(string.ascii_letters, k=length))

def random_phone():
    return f"+1-{random.randint(100,999)}-{random.randint(100,999)}-{random.randint(1000,9999)}"

async def seed_leads():
    print("🌱 Seeding 40 random leads...")
    async with engine.connect() as conn:
        # 1. Get a template lead to copy FKs from
        result = await conn.execute(text("SELECT * FROM lead_leads WHERE deleted_at IS NULL LIMIT 1"))
        template = result.mappings().one_or_none()
        
        if not template:
            print("❌ No existing leads found to use as template. Please create at least one lead manually.")
            return

        print(f"📋 Using template lead ID: {template['id']} (User: {template['assigned_user_id']})")
        
        # 2. Insert 40 clones
        for i in range(40):
            first_name = random.choice(["Juan", "Maria", "Pedro", "Ana", "Luis", "Carmen", "Jose", "Laura", "Carlos", "Sofia"])
            last_name = random.choice(["Gomez", "Perez", "Rodriguez", "Lopez", "Garcia", "Martinez", "Sanchez", "Fernandez", "Gonzalez", "Diaz"])
            full_name = f"{first_name} {first_name[0]}. {last_name} {random.randint(1, 99)}"
            email = f"{first_name.lower()}.{last_name.lower()}.{random_string(4)}@example.com"
            
            # Randomize scores slightly
            score_total = random.randint(10, 100)
            
            query = text("""
                INSERT INTO lead_leads (
                    id, client_id, assigned_user_id, source_id,
                    full_name, email, phone, status, 
                    score_total, score_engagement, score_finance, score_timeline, score_match, score_info,
                    eng_def_id, fin_def_id, timeline_def_id, match_def_id, info_def_id,
                    created_at, updated_at
                ) VALUES (
                    :id, :client_id, :assigned_user_id, :source_id,
                    :full_name, :email, :phone, :status,
                    :score_total, :score_engagement, :score_finance, :score_timeline, :score_match, :score_info,
                    :eng_def_id, :fin_def_id, :timeline_def_id, :match_def_id, :info_def_id,
                    NOW(), NOW()
                )
            """)
            
            await conn.execute(query, {
                "id": uuid.uuid4(),
                "client_id": template['client_id'],
                "assigned_user_id": template['assigned_user_id'],
                "source_id": template['source_id'], # Keep same source
                "full_name": full_name,
                "email": email,
                "phone": random_phone(),
                "status": random.choice(["New", "Follow-up", "Qualifying", "Closed"]),
                "score_total": score_total,
                "score_engagement": random.randint(0, 100),
                "score_finance": random.randint(0, 100),
                "score_timeline": random.randint(0, 100),
                "score_match": random.randint(0, 100),
                "score_info": random.randint(0, 100),
                # Copy FKs
                "eng_def_id": template['eng_def_id'],
                "fin_def_id": template['fin_def_id'],
                "timeline_def_id": template['timeline_def_id'],
                "match_def_id": template['match_def_id'],
                "info_def_id": template['info_def_id'],
            })
            
        await conn.commit()
        print("✅ 40 Leads inserted successfully!")

if __name__ == "__main__":
    asyncio.run(seed_leads())
