import asyncio
from app.dal.database import engine
from sqlalchemy import text
async def test():
    async with engine.connect() as conn:
        res = await conn.execute(text("SELECT * FROM lead_leads LIMIT 1"))
        print(res.keys())
if __name__ == "__main__":
    asyncio.run(test())
