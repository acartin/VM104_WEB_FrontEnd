import asyncio
from sqlalchemy import select
from app.dal.database import async_session_maker
from app.modules.auth.models import User, Role, ClientUser, LeadClient

async def assign_tenant():
    async with async_session_maker() as session:
        # 1. Find User
        stmt = select(User).where(User.email == "acartina15@hotmail.com")
        result = await session.execute(stmt)
        user = result.scalars().first()
        
        if not user:
            print("ERROR: User 'acartina15@hotmail.com' not found!")
            return

        print(f"User Found: {user.email} ({user.id})")

        # 2. Find Client (Coca Cola) - Assuming LeadClient table has 'name' or similar
        # Since I haven't seen LeadClient definition fully, I'll list all if not sure.
        # But let's try to find by name assuming 'name' column exists or 'slug'.
        # Actually, LeadClient in auth/models.py only showed ID. Let's check schema again?
        # No, wait, LeadClient in auth/models.py was a partial definition.
        # I'll rely on raw SQL or try to reuse the model if it maps to the real table.
        # Let's assume the real table is 'lead_clients' and has a 'name' column.
        
        # Let's just list ALL clients first to be safe and pick Coca Cola.
        from sqlalchemy import text
        result = await session.execute(text("SELECT id, name FROM lead_clients WHERE name ILIKE '%Coca%'"))
        client_row = result.first()
        
        if not client_row:
            print("Client 'Coca Cola' NOT found. Listing all clients:")
            all_clients = await session.execute(text("SELECT id, name FROM lead_clients LIMIT 10"))
            for row in all_clients:
                print(f" - {row.name} ({row.id})")
            return

        client_id = client_row.id
        client_name = client_row.name
        print(f"Client Found: {client_name} ({client_id})")

        # 3. Find/Create Role 'admin'
        stmt = select(Role).where(Role.slug == "admin")
        result = await session.execute(stmt)
        role = result.scalars().first()
        
        if not role:
            print("Role 'admin' not found. Creating it...")
            role = Role(name="Administrator", slug="admin")
            session.add(role)
            await session.commit()
            await session.refresh(role)
            
        print(f"Role Ready: {role.name} ({role.id})")

        # 4. Assign
        # Check if already assigned
        stmt = select(ClientUser).where(
            ClientUser.user_id == user.id,
            ClientUser.client_id == client_id
        )
        result = await session.execute(stmt)
        existing = result.scalars().first()
        
        if existing:
            print("User is ALREADY assigned to this client.")
        else:
            link = ClientUser(
                user_id=user.id,
                client_id=client_id,
                role_id=role.id
            )
            session.add(link)
            await session.commit()
            print("✅ SUCCESS: User linked to Coca Cola!")

if __name__ == "__main__":
    asyncio.run(assign_tenant())
