from pydantic import BaseModel, Field
from typing import Optional
from uuid import UUID
from datetime import datetime

class PromptBase(BaseModel):
    """Shared properties"""
    slug: str = Field(..., min_length=3, description="Unique identifier/name for the prompt")
    prompt_text: str = Field(..., min_length=10, description="The actual prompt content")
    is_active: bool = Field(True, description="Whether this prompt is active")

class PromptCreate(PromptBase):
    """Schema for Creation - client_id is injected by backend"""
    pass

class PromptUpdate(BaseModel):
    """Schema for Update - all fields optional"""
    slug: Optional[str] = Field(None, min_length=3)
    prompt_text: Optional[str] = Field(None, min_length=10)
    is_active: Optional[bool] = Field(None)

class PromptRow(PromptBase):
    """Schema for Grid Display"""
    id: UUID
    updated_at: Optional[datetime]
    
    class Config:
        from_attributes = True
