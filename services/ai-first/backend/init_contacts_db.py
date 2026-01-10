import asyncio
from app.dal.database import engine, Base
# Import models to ensure they are registered with Base.metadata
from app.modules.contacts.models import LeadContact, ContactChannel

async def init_contacts_db():
    async with engine.begin() as conn:
        print("Creating Contact Tables (LeadContact, ContactChannel)...")
        await conn.run_sync(Base.metadata.create_all)
        print("Contact Tables Created Successfully.")

    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(init_contacts_db())
