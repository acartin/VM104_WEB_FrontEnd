from sqlalchemy import select
from app.dal.database import engine
from app.modules.auth.models import LeadClient
from pydantic import BaseModel
from uuid import UUID

class ClientSimple(BaseModel):
    id: UUID
    name: str

class ClientService:
    async def list_simple(self):
        query = select(LeadClient.id, LeadClient.name).order_by(LeadClient.name)
        async with engine.connect() as conn:
            result = await conn.execute(query)
            return [ClientSimple(id=row.id, name=row.name) for row in result]

service = ClientService()
