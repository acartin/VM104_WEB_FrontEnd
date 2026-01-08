import asyncio
from app.dal.database import engine, Base
from app.modules.auth.models import User, Role, ClientUser

async def init_models():
    async with engine.begin() as conn:
        print("Creating Auth Tables...")
        await conn.run_sync(Base.metadata.create_all)
        print("Auth Tables Created.")

    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(init_models())
