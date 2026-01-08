import asyncio
from sqlalchemy import select, update
from app.dal.database import async_session_maker
from app.modules.auth.models import User

async def restore():
    async with async_session_maker() as session:
        stmt = select(User).where(User.email == "acartina15@hotmail.com")
        result = await session.execute(stmt)
        user = result.scalars().first()
        
        if user:
            print(f"Restoring Superuser for {user.email}")
            user.is_superuser = True
            await session.commit()
            print("Successfully restored is_superuser=True")
        else:
            print("User not found")

if __name__ == "__main__":
    asyncio.run(restore())
