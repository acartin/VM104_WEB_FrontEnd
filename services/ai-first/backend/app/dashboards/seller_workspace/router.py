from fastapi import APIRouter, Depends, Request, HTTPException
from uuid import UUID
from .schema import get_seller_workspace_schema, get_lead_detail_schema, ClientUserDashboardSchema
from app.modules.auth.config import current_active_user
from app.modules.auth.models import User
from app.modules.contacts.service import service as contact_service

router = APIRouter()

@router.get("/seller", response_model=ClientUserDashboardSchema)
async def get_seller_dashboard(request: Request, user: User = Depends(current_active_user)):
    return get_seller_workspace_schema(str(user.id))

@router.get("/leads/{lead_id}", response_model=ClientUserDashboardSchema)
async def get_lead_detail_dashboard(lead_id: UUID, user: User = Depends(current_active_user)):
    # Fetch real lead data
    leads = await contact_service.get_my_leads(user.id)
    lead = next((l for l in leads if l['id'] == lead_id), None)
    
    if not lead:
        raise HTTPException(status_code=404, detail="Lead not found or not assigned.")
    
    return get_lead_detail_schema(str(user.id), str(lead_id), lead)
