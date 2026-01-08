import asyncio
import contextlib
from app.dal.database import async_session_maker
from app.modules.auth.manager import UserManager
from app.modules.auth.db import get_user_db
from app.modules.auth.schemas import UserCreate
from app.modules.auth.models import User
from fastapi_users.password import PasswordHelper
from fastapi_users.db import SQLAlchemyUserDatabase

async def create_user():
    # 1. Manual Dependency Injection
    async with async_session_maker() as session:
        user_db = SQLAlchemyUserDatabase(session, User)
        user_manager = UserManager(user_db)
        
        # 2. Check if user exists
        try:
            user = await user_manager.get_by_email("acartina15@hotmail.com")
            print(f"User {user.email} already exists.")
            return
        except:
            pass # User not found, proceed
            
        # 3. Create User
        user_in = UserCreate(
            email="acartina15@hotmail.com",
            password="Techimi.15",
            is_active=True,
            is_superuser=True,
            is_verified=True,
            name="Alvaro Cartin"
        )
        
        created_user = await user_manager.create(user_in)
        print(f"Superuser created successfully: {created_user.email} (ID: {created_user.id})")

if __name__ == "__main__":
    asyncio.run(create_user())
