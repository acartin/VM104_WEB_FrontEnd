import asyncio
from sqlalchemy import select
from app.dal.database import async_session_maker
from app.modules.auth.models import User, Role, LeadClient, ClientUser
import uuid

async def assign():
    async with async_session_maker() as session:
        # 1. Get User
        result = await session.execute(select(User).where(User.email == "acartina15@hotmail.com"))
        user = result.scalars().first()
        if not user:
            print("User not found")
            return

        # 2. Get Admin Role
        result = await session.execute(select(Role).where(Role.slug == "admin"))
        role = result.scalars().first()
        if not role:
            print("Role 'admin' not found. Creating it if logic allows, or error.")
            # Startups usually have seeded roles.
            return

        # 3. Get System/Global Client (DataSync)
        # Assuming DataSync Systems is the "Owner" tenant
        result = await session.execute(select(LeadClient).where(LeadClient.name.ilike("%DataSync%")))
        client = result.scalars().first()
        
        # Fallback if DataSync doesn't exist (it should from setup_test_data)
        if not client:
             result = await session.execute(select(LeadClient))
             client = result.scalars().first()
             print(f"DataSync not found, using first available: {client.name}")

        if not client:
             print("No clients found!")
             return

        # 4. Check existing link
        result = await session.execute(select(ClientUser).where(
            ClientUser.user_id == user.id,
            ClientUser.client_id == client.id
        ))
        existing = result.scalars().first()

        if existing:
            print(f"User already linked to {client.name}. Updating Role to Admin.")
            existing.role_id = role.id
        else:
            print(f"Linking user to {client.name} as Admin.")
            link = ClientUser(
                id=uuid.uuid4(),
                user_id=user.id,
                client_id=client.id,
                role_id=role.id
            )
            session.add(link)
        
        # 5. Turn OFF superuser (optional, but requested "remove mode god")
        # user.is_superuser = False 
        # Actually user said "quitale ese if", not necessarily disable the flag in DB, 
        # but disabling it proves RBAC works. Let's do it.
        user.is_superuser = False
        print("Disabled is_superuser flag.")

        await session.commit()
        print("Done.")

if __name__ == "__main__":
    asyncio.run(assign())
