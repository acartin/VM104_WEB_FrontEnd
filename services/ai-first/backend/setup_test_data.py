import asyncio
from sqlalchemy import select, text
from app.dal.database import async_session_maker
from app.modules.auth.models import User, Role, ClientUser, LeadClient
from app.modules.auth.manager import UserManager
from app.modules.auth.db import get_user_db
from fastapi_users.db import SQLAlchemyUserDatabase
from app.modules.auth.schemas import UserCreate

# --- CONFIGURATION ---
ROLES = [
    {"name": "Client Administrator", "slug": "client-admin"},
    {"name": "Client User", "slug": "client-user"},
    {"name": "System User", "slug": "system-user"},
    {"name": "Super Administrator", "slug": "admin"} # Ensure existing
]

CLIENTS = [
    {"name": "Coca Cola"},
    {"name": "Pepsi"},
    {"name": "DataSync Systems"} # For System Users
]

USERS = [
    {
        "email": "cocacola-admin@cocacola.com", "name": "Coca Cola Admin", 
        "password": "holalola", "role": "client-admin", "client": "Coca Cola"
    },
    {
        "email": "cocacola-user@cocacola.com", "name": "Coca Cola User", 
        "password": "holalola", "role": "client-user", "client": "Coca Cola"
    },
    {
        "email": "pepsi-admin@pepsi.com", "name": "Pepsi Admin", 
        "password": "holalola", "role": "client-admin", "client": "Pepsi"
    },
    {
        "email": "pepsi-user@pepsi.com", "name": "Pepsi User", 
        "password": "holalola", "role": "client-user", "client": "Pepsi"
    },
    {
        "email": "system-user@datasyncsa.com", "name": "System User", 
        "password": "holalola", "role": "system-user", "client": "DataSync Systems"
    }
]

async def seed_data():
    async with async_session_maker() as session:
        print("🚀 Starting Data Seed...")
        
        # 1. Create Roles
        role_map = {} # slug -> Role Object
        for r in ROLES:
            stmt = select(Role).where(Role.slug == r["slug"])
            result = await session.execute(stmt)
            role = result.scalars().first()
            if not role:
                print(f"Creating Role: {r['name']}")
                role = Role(name=r['name'], slug=r['slug'])
                session.add(role)
            else:
                print(f"Role exists: {r['name']}")
            role_map[r["slug"]] = role
        await session.commit()
        
        # Refresh roles to get IDs
        for r in ROLES:
             stmt = select(Role).where(Role.slug == r["slug"])
             role_map[r["slug"]] = (await session.execute(stmt)).scalars().first()

        # 1.5. Ensure Default Country exists (for LeadClient constraint)
        stmt = text("SELECT id FROM lead_countries LIMIT 1")
        result = await session.execute(stmt)
        country_id = result.scalar()
        
        if not country_id:
            print("Creating Default Country (Costa Rica)...")
            stmt = text("INSERT INTO lead_countries (name, iso_code, updated_at) VALUES ('Costa Rica', 'CR', NOW()) RETURNING id")
            result = await session.execute(stmt)
            country_id = result.scalar()
            await session.commit()
        else:
            print(f"Using Default Country ID: {country_id}")

        # 2. Create Clients
        client_map = {} # name -> Client Object
        for c in CLIENTS:
            # Check by name using raw SQL as LeadClient might be legacy
            # Assuming 'name' column exists (we added it recently)
            stmt = select(LeadClient).where(LeadClient.name == c['name'])
            try:
                result = await session.execute(stmt)
                item = result.scalars().first()
            except Exception as e:
                item = None
            
            if not item:
                print(f"Creating Client: {c['name']}")
                import uuid
                # Use raw SQL to insert to avoid mapping issues if model is partial, and to pass country_id
                # Or update model definition? Better to use raw SQL for setup script to be safe against model drift.
                # Actually, let's try updating the object if SQLAlchemy allows unknown args (it doesn't usually).
                # BETTER: Use raw SQL insert for LeadClient to be 100% sure we hit all columns.
                new_id = uuid.uuid4()
                stmt = text("""
                    INSERT INTO lead_clients (id, name, country_id, created_at, updated_at)
                    VALUES (:id, :name, :cid, NOW(), NOW())
                """)
                await session.execute(stmt, {"id": new_id, "name": c['name'], "cid": country_id})
                # No session.add needed for raw SQL, but need to fetch it back to put in map
            else:
                 print(f"Client exists: {c['name']}")
            
            client_map[c['name']] = item # This might be None if just inserted, we refresh below
        await session.commit()
        await session.commit()

        # Refresh clients
        for c in CLIENTS:
            # We rely on the object being attached to session or re-query
             stmt = select(LeadClient).where(LeadClient.name == c['name'])
             client_map[c['name']] = (await session.execute(stmt)).scalars().first()

        # 3. Create Users & Link
        user_db = SQLAlchemyUserDatabase(session, User)
        user_manager = UserManager(user_db)
        
        from fastapi_users import exceptions
        
        for u in USERS:
            # Find User via Manager (raises UserNotExists if missing)
            existing = None
            try:
                existing = await user_manager.get_by_email(u['email'])
            except exceptions.UserNotExists:
                pass
            
            if not existing:
                print(f"Creating User: {u['email']}")
                user_in = UserCreate(
                    email=u['email'],
                    password=u['password'],
                    name=u['name'],
                    is_active=True,
                    is_verified=True
                )
                existing = await user_manager.create(user_in)
            else:
                print(f"User exists: {u['email']}")
                
            # Link to Client/Role
            target_client = client_map.get(u['client'])
            target_role = role_map.get(u['role'])
            
            if not target_client or not target_role:
                print(f"Warning: Client or Role not found for {u['email']}")
                continue
                
            # Check Link
            stmt = select(ClientUser).where(
                ClientUser.user_id == existing.id,
                ClientUser.client_id == target_client.id
            )
            link = (await session.execute(stmt)).scalars().first()
            
            if not link:
                print(f"Linking {u['email']} -> {target_client.name} as {target_role.slug}")
                link = ClientUser(
                    user_id=existing.id,
                    client_id=target_client.id,
                    role_id=target_role.id
                )
                session.add(link)
            else:
                print(f"Link exists for {u['email']}")
                # Optional: Update role if changed?
                if link.role_id != target_role.id:
                    link.role_id = target_role.id
                    session.add(link)
                    print(f"Updated role to {target_role.slug}")

        await session.commit()
        print("✅ Seed Complete!")

if __name__ == "__main__":
    asyncio.run(seed_data())
