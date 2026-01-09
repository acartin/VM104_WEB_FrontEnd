from sqlalchemy import text
from app.dal.database import engine
from .schemas import ClientRow, ClientCreate, ClientUpdate, ClientSimple
from typing import List, Optional
from uuid import UUID
import uuid

class ClientService:
    async def list_simple(self) -> List[ClientSimple]:
        query = text("SELECT id, name FROM lead_clients ORDER BY name")
        async with engine.connect() as conn:
            result = await conn.execute(query)
            rows = result.all()
            return [ClientSimple(id=row.id, name=row.name) for row in rows]

    async def list_clients(self) -> List[ClientRow]:
        query = text("""
            SELECT c.id, c.name, c.country_id, lc.name as country_name 
            FROM lead_clients c
            LEFT JOIN lead_countries lc ON c.country_id = lc.id
            ORDER BY c.name
        """)
        async with engine.connect() as conn:
            result = await conn.execute(query)
            rows = result.all()
            return [ClientRow(id=row.id, name=row.name, country_id=row.country_id or 0, country_name=row.country_name) for row in rows]

    async def get_client(self, client_id: UUID) -> Optional[ClientRow]:
        query = text("""
            SELECT c.id, c.name, c.country_id, lc.name as country_name 
            FROM lead_clients c
            LEFT JOIN lead_countries lc ON c.country_id = lc.id
            WHERE c.id = :id
        """)
        async with engine.connect() as conn:
            result = await conn.execute(query, {"id": client_id})
            row = result.fetchone()
            if row:
                return ClientRow(id=row.id, name=row.name, country_id=row.country_id or 0, country_name=row.country_name)
            return None

    async def create_client(self, client: ClientCreate) -> ClientRow:
        query = text("""
            INSERT INTO lead_clients (id, name, country_id)
            VALUES (:id, :name, :country_id)
            RETURNING id, name, country_id
        """)
        new_id = uuid.uuid4()
        async with engine.begin() as conn:
            result = await conn.execute(query, {"id": new_id, "name": client.name, "country_id": client.country_id})
            row = result.fetchone()
            # Fetch full row/name for consistent return, or just return basic
            return ClientRow(id=row.id, name=row.name, country_id=row.country_id)

    async def update_client(self, client_id: UUID, client: ClientUpdate) -> Optional[ClientRow]:
        updates = []
        params = {"id": client_id}
        
        if client.name:
            updates.append("name = :name")
            params["name"] = client.name
        
        if client.country_id is not None:
             updates.append("country_id = :country_id")
             params["country_id"] = client.country_id

        if not updates:
            return await self.get_client(client_id)
            
        query = text(f"""
            UPDATE lead_clients
            SET {", ".join(updates)}
            WHERE id = :id
            RETURNING id, name, country_id
        """)
        
        async with engine.begin() as conn:
            result = await conn.execute(query, params)
            row = result.fetchone()
            if row:
                return ClientRow(id=row.id, name=row.name, country_id=row.country_id)
            return None

    async def delete_client(self, client_id: UUID) -> bool:
        query = text("DELETE FROM lead_clients WHERE id = :id")
        async with engine.begin() as conn:
            await conn.execute(query, {"id": client_id})
            return True

service = ClientService()
