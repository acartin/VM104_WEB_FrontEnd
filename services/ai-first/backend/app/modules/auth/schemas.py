import uuid
from fastapi_users import schemas
from typing import Optional, List
from pydantic import BaseModel

# Schema for the Pivot Table (ClientUser)
class ClientSimple(BaseModel):
    id: uuid.UUID
    name: Optional[str] = None
    
    class Config:
        from_attributes = True

class ClientUserRead(BaseModel):
    client_id: uuid.UUID
    role_id: uuid.UUID
    client: Optional[ClientSimple] = None
    
    class Config:
        from_attributes = True # Pydantic v2 support for ORM

class UserRead(schemas.BaseUser[uuid.UUID]):
    name: Optional[str] = None
    # We return the list of tenants (ClientUser)
    tenants: List[ClientUserRead] = [] 

class UserCreate(schemas.BaseUserCreate):
    name: Optional[str] = None

class UserUpdate(schemas.BaseUserUpdate):
    name: Optional[str] = None
