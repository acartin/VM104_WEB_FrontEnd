from pydantic_settings import BaseSettings
from typing import Optional

class Settings(BaseSettings):
    # Database Credentials matching .env keys
    db_host: str
    db_port: str = "5432"
    db_database: str
    db_username: str
    db_password: str
    db_connection: str = "pgsql" # Default from .env

    @property
    def database_url(self) -> str:
        """Constructs the Asyncpg connection URL from individual env vars."""
        # Using postgresql+asyncpg for async support
        return f"postgresql+asyncpg://{self.db_username}:{self.db_password}@{self.db_host}:{self.db_port}/{self.db_database}"

    class Config:
        # Assuming app is run from 'backend/' directory
        env_file = "../.env"
        env_file_encoding = "utf-8"
        # Map environment variables to case-insensitive fields automatically
        case_sensitive = False 

settings = Settings()
