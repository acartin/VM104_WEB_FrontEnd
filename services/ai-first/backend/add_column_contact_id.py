import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def add_column():
    async with engine.begin() as conn:
        print("Checking if 'auth_users' needs 'contact_id'...")
        
        # 1. Check if column exists
        res = await conn.execute(text("SELECT column_name FROM information_schema.columns WHERE table_name='auth_users' AND column_name='contact_id'"))
        if res.fetchone():
            print("Column 'contact_id' already exists in 'auth_users'.")
        else:
            print("Adding 'contact_id' column to 'auth_users'...")
            # Add column
            await conn.execute(text("ALTER TABLE auth_users ADD COLUMN contact_id UUID"))
            # ADD FK
            print("Adding Foreign Key constraint...")
            await conn.execute(text("ALTER TABLE auth_users ADD CONSTRAINT fk_auth_users_contact_id FOREIGN KEY (contact_id) REFERENCES lead_contacts(id) ON DELETE SET NULL"))
            # Add Unique constraint (One Person -> One User)
            print("Adding Unique constraint...")
            await conn.execute(text("ALTER TABLE auth_users ADD CONSTRAINT uq_auth_users_contact_id UNIQUE (contact_id)"))
            
            print("Column added successfully.")

    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(add_column())
