from fastapi import APIRouter, Depends, HTTPException
from app.contracts.ui_schema import WebIAFirstResponse
from app.modules.auth.config import current_active_user
from app.modules.auth.models import User
from app.modules.clients.service import service as client_service
from app.modules.contacts.service import service as contact_service
from .schema import get_client_user_schema
from uuid import UUID
import json
import base64

router = APIRouter()

@router.get("/client-user", response_model=WebIAFirstResponse)
async def get_client_user_dashboard(current_user: User = Depends(current_active_user)):
    """
    Returns the Operational Dashboard for Sellers (Client Users).
    """
    if not current_user.tenants:
        raise HTTPException(status_code=403, detail="No client context assigned.")
    
    client_id = current_user.tenants[0].client_id
    
    # 1. Fetch Data (Isolated to the current seller)
    assigned_leads = await contact_service.get_my_leads(current_user.id)
    appointments = await contact_service.get_my_appointments(current_user.id)
    
    # Simple Stats for the seller
    stats = {
        "total_leads": len(assigned_leads),
        "actions_today": len(appointments), # Real count of upcoming/today tasks
    }
    
    # Filter "Urgent" leads (e.g. highest score)
    top_leads_raw = sorted(assigned_leads, key=lambda x: x.get('score_total', 0) or 0, reverse=True)[:3]
    
    # Map to Member UI formats (Matching Velzon Card expectations)
    def map_lead_to_member(l):
        # Find if this lead has a pending appointment
        has_appt = any(a['lead_name'] == l['full_name'] for a in appointments)
        status_label = "URGENTE: Cita pendiente" if has_appt else f"Score: {l.get('score_total', 0)}"
        
        # Build clean channels list (avoiding Null pointers in JS)
        channels = []
        if l.get('email'):
            channels.append({
                "type": "email", 
                "value": l['email'], 
                "label": "Email", 
                "is_primary": True,
                "category_icon": "ri-mail-line",
                "category_name": "Email"
            })
        if l.get('phone'):
            channels.append({
                "type": "phone", 
                "value": l['phone'], 
                "label": "Tel", 
                "is_primary": False,
                "category_icon": "ri-phone-line",
                "category_name": "Teléfono"
            })

        return {
            "id": str(l['id']),
            "name": l['full_name'] or "Lead Sin Nombre",
            "position": f"{status_label} | Status: {l.get('status', 'New')}",
            "is_active": True,
            "avatar": f"https://api.dicebear.com/7.x/initials/svg?seed={l['full_name'] or 'Lead'}",
            "channels": channels
        }

    my_leads = [map_lead_to_member(l) for l in assigned_leads]
    top_leads = [map_lead_to_member(l) for l in top_leads_raw]

    # 3. Return Schema
    return get_client_user_schema(
        user_name=current_user.name or "Vendedor",
        my_leads=my_leads,
        stats=stats,
        top_leads=top_leads
    )
