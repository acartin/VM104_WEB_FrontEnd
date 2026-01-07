from sqlalchemy import text
from app.dal.database import engine
from .schemas import PromptCreate, PromptUpdate, PromptRow
from typing import List, Optional
from uuid import UUID
from fastapi import HTTPException
from sqlalchemy.exc import IntegrityError

class PromptService:
    """
    Data Access Layer for AI Prompts.
    Tenant-Scoped: All operations require client_id.
    """

    async def list_prompts(self, client_id: str) -> List[PromptRow]:
        # Filter by client_id and ensure not soft-deleted
        query = text("""
            SELECT id, slug, prompt_text, is_active, updated_at 
            FROM lead_ai_prompts 
            WHERE client_id = :cid AND deleted_at IS NULL
            ORDER BY slug ASC
        """)
        async with engine.connect() as conn:
            result = await conn.execute(query, {"cid": client_id})
            return [
                PromptRow(
                    id=row.id, 
                    slug=row.slug, 
                    prompt_text=row.prompt_text, 
                    is_active=row.is_active, 
                    updated_at=row.updated_at
                ) for row in result
            ]

    async def get_prompt(self, client_id: str, prompt_id: UUID) -> Optional[PromptRow]:
        query = text("""
            SELECT id, slug, prompt_text, is_active, updated_at 
            FROM lead_ai_prompts 
            WHERE id = :id AND client_id = :cid AND deleted_at IS NULL
        """)
        async with engine.connect() as conn:
            result = await conn.execute(query, {"id": prompt_id, "cid": client_id})
            row = result.fetchone()
            if row:
                return PromptRow(
                    id=row.id, 
                    slug=row.slug, 
                    prompt_text=row.prompt_text, 
                    is_active=row.is_active, 
                    updated_at=row.updated_at
                )
            return None

    async def create_prompt(self, client_id: str, prompt: PromptCreate) -> PromptRow:
        query = text("""
            INSERT INTO lead_ai_prompts (client_id, slug, prompt_text, is_active, created_at, updated_at)
            VALUES (:cid, :slug, :text, :active, NOW(), NOW())
            RETURNING id, slug, prompt_text, is_active, updated_at
        """)
        params = {
            "cid": client_id,
            "slug": prompt.slug,
            "text": prompt.prompt_text,
            "active": prompt.is_active
        }
        
        async with engine.connect() as conn:
            try:
                result = await conn.execute(query, params)
                row = result.fetchone()
                await conn.commit()
                return PromptRow(
                    id=row.id, 
                    slug=row.slug, 
                    prompt_text=row.prompt_text, 
                    is_active=row.is_active, 
                    updated_at=row.updated_at
                )
            except IntegrityError:
                await conn.rollback()
                raise HTTPException(status_code=409, detail="A prompt with this slug already exists.")
            except Exception as e:
                print(f"CREATE ERROR: {e}")
                raise HTTPException(status_code=500, detail=str(e))

    async def update_prompt(self, client_id: str, prompt_id: UUID, prompt: PromptUpdate) -> Optional[PromptRow]:
        updates = []
        params = {"id": prompt_id, "cid": client_id}
        
        if prompt.slug:
            updates.append("slug = :slug")
            params["slug"] = prompt.slug
        
        if prompt.prompt_text:
            updates.append("prompt_text = :text")
            params["text"] = prompt.prompt_text
            
        if prompt.is_active is not None:
            updates.append("is_active = :active")
            params["active"] = prompt.is_active

        if not updates:
            return await self.get_prompt(client_id, prompt_id)

        updates.append("updated_at = NOW()")
        
        query_str = f"""
            UPDATE lead_ai_prompts
            SET {", ".join(updates)}
            WHERE id = :id AND client_id = :cid
            RETURNING id, slug, prompt_text, is_active, updated_at
        """
        
        async with engine.connect() as conn:
            try:
                result = await conn.execute(text(query_str), params)
                row = result.fetchone()
                await conn.commit()
                if row:
                    return PromptRow(
                        id=row.id, 
                        slug=row.slug, 
                        prompt_text=row.prompt_text, 
                        is_active=row.is_active, 
                        updated_at=row.updated_at
                    )
                return None
            except IntegrityError:
                await conn.rollback()
                raise HTTPException(status_code=409, detail="A prompt with this slug already exists.")
            except Exception as e:
                print(f"UPDATE ERROR: {e}")
                raise HTTPException(status_code=500, detail=str(e))

    async def delete_prompt(self, client_id: str, prompt_id: UUID) -> bool:
        # Soft Delete
        query = text("""
            UPDATE lead_ai_prompts 
            SET deleted_at = NOW() 
            WHERE id = :id AND client_id = :cid
        """)
        async with engine.connect() as conn:
            result = await conn.execute(query, {"id": prompt_id, "cid": client_id})
            await conn.commit()
            return result.rowcount > 0

service = PromptService()
