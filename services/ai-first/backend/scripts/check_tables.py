import asyncio
from sqlalchemy import text
from sqlalchemy.ext.asyncio import create_async_engine
import sys
import os

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from app.config.settings import settings

async def check():
    engine = create_async_engine(settings.database_url)
    async with engine.connect() as conn:
        res = await conn.execute(text("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"))
        tables = [r[0] for r in res]
        print("Existing tables in 'public' schema:")
        for t in sorted(tables):
            print(f"- {t}")
    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(check())
