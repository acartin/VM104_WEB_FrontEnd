import asyncio
from sqlalchemy import text
from app.dal.database import engine
from uuid import UUID

async def diagnose_visibility():
    user_id = UUID('e18b47ae-4067-420d-861b-7f732409ade4')
    
    async with engine.connect() as conn:
        print(f"--- DIAGNOSING VISIBILITY FOR USER {user_id} ---")
        
        # 1. User Tenant Context
        user_tenant = await conn.execute(text("SELECT client_id FROM auth_client_user WHERE user_id = :uid"), {"uid": user_id})
        user_client_id = user_tenant.scalar()
        print(f"User Tenant Client ID: {user_client_id}")
        
        # 2. Leads assigned to the user
        leads_res = await conn.execute(text("""
            SELECT l.id, l.client_id, l.full_name, l.deleted_at
            FROM lead_leads l
            WHERE l.assigned_user_id = :uid
        """), {"uid": user_id})
        leads = leads_res.fetchall()
        print(f"Total leads assigned to user: {len(leads)}")
        
        if leads:
            print("\nSample Leads Data:")
            for l in leads[:5]:
                print(f"- ID: {l.id} | Name: {l.full_name} | Lead Client ID: {l.client_id} | Deleted: {l.deleted_at}")
                if str(l.client_id) != str(user_client_id):
                    print(f"  [!] MISMATCH: Lead Client ID ({l.client_id}) != User Client ID ({user_client_id})")

        # 3. Quick fix if needed: align client_id
        if leads and str(leads[0].client_id) != str(user_client_id):
            print("\nSuggesting alignment of lead.client_id with user.client_id...")

if __name__ == "__main__":
    asyncio.run(diagnose_visibility())
