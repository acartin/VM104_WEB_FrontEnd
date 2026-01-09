from pydantic import BaseModel, Field
from typing import Optional
from uuid import UUID

class RoleBase(BaseModel):
    name: str = Field(..., description="Nombre legible del rol")
    slug: str = Field(..., description="Identificador único (ej: admin, user)")

class RoleCreate(RoleBase):
    pass

class RoleUpdate(BaseModel):
    name: Optional[str] = None
    slug: Optional[str] = None

class RoleRow(RoleBase):
    id: UUID

    class Config:
        from_attributes = True
