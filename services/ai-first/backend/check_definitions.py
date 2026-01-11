import asyncio
import os
import sys

# Add backend to path for imports
sys.path.append(os.getcwd())

from app.dal.database import engine
from sqlalchemy import text

async def test():
    try:
        async with engine.connect() as conn:
            res = await conn.execute(text("SELECT criterion, min_score, max_score, label, icon, color FROM lead_scoring_definitions ORDER BY criterion, min_score"))
            rows = res.fetchall()
            print("CRITERION | RANGE | LABEL | ICON | COLOR")
            print("-" * 60)
            for r in rows:
                print(f"{r[0]} | {r[1]}-{r[2]} | {r[3]} | {r[4]} | {r[5]}")
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    asyncio.run(test())
