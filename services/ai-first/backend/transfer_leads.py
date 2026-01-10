import asyncio
from uuid import UUID
from sqlalchemy import text
from app.dal.database import engine

async def transfer_all_leads():
    # User ID: e18b47ae-4067-420d-861b-7f732409ade4 (cocacola-admin@cocacola.com)
    target_user_id = UUID('e18b47ae-4067-420d-861b-7f732409ade4')
    
    async with engine.connect() as conn:
        print(f"Transferring all leads to User ID: {target_user_id}")
        
        # 1. Update all leads
        query = text("""
            UPDATE lead_leads 
            SET assigned_user_id = :uid,
                deleted_at = NULL
            WHERE deleted_at IS NULL OR assigned_user_id IS NOT NULL OR assigned_user_id IS NULL
        """)
        res = await conn.execute(query, {"uid": target_user_id})
        print(f"Updated {res.rowcount} leads.")
        
        # 2. Ensure shadow user exists in lead_users
        check_user = await conn.execute(text("SELECT id FROM lead_users WHERE id = :uid"), {"uid": target_user_id})
        if not check_user.fetchone():
            print("Creating shadow user in lead_users...")
            await conn.execute(text("""
                INSERT INTO lead_users (id, name, email)
                VALUES (:uid, 'Coca Cola Admin', 'cocacola-admin@cocacola.com')
            """), {"uid": target_user_id})
        
        await conn.commit()
        print("Transfer complete.")

if __name__ == "__main__":
    asyncio.run(transfer_all_leads())
