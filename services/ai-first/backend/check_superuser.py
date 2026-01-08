import asyncio
from sqlalchemy import select
from app.dal.database import async_session_maker
from app.modules.auth.models import User

async def check():
    async with async_session_maker() as session:
        stmt = select(User).where(User.email == "acartina15@hotmail.com")
        result = await session.execute(stmt)
        user = result.scalars().first()
        if user:
            print(f"User found: {user.email}")
            print(f"is_superuser: {user.is_superuser}")
            
            if not user.is_superuser:
                print("FIXING: Setting is_superuser = True")
                user.is_superuser = True
                await session.commit()
                print("FIXED.")
        else:
            print("User not found")

if __name__ == "__main__":
    asyncio.run(check())
