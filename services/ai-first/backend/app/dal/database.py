from sqlalchemy.ext.asyncio import create_async_engine
from sqlalchemy import text
from app.config.settings import settings

# Initialize Async Engine
# echo=True prints SQL statements to console (useful for debugging "Zero ORM" compliance)
engine = create_async_engine(
    settings.database_url,
    echo=True, 
    future=True
)

async def get_db_connection():
    """
    Dependency to yield an async database connection.
    Usage in routes:
        conn = Depends(get_db_connection)
        await conn.execute(text("SELECT ..."))
    """
    async with engine.connect() as conn:
        yield conn
