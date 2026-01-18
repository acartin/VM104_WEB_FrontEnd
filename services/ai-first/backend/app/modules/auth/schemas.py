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

# Schema for Role
class RoleSimple(BaseModel):
    id: uuid.UUID
    name: str 
    slug: str
    
    class Config:
        from_attributes = True

class ClientUserRead(BaseModel):
    client_id: uuid.UUID
    role_id: uuid.UUID
    client: Optional[ClientSimple] = None
    role: Optional[RoleSimple] = None # Added Role details
    
    class Config:
        from_attributes = True # Pydantic v2 support for ORM

class UserRead(schemas.BaseUser[uuid.UUID]):
    name: Optional[str] = None
    contact_id: Optional[uuid.UUID] = None
    # We return the list of tenants (ClientUser)
    tenants: List[ClientUserRead] = [] 

class UserCreate(schemas.BaseUserCreate):
    name: Optional[str] = None
    contact_id: Optional[uuid.UUID] = None

class UserUpdate(schemas.BaseUserUpdate):
    name: Optional[str] = None
