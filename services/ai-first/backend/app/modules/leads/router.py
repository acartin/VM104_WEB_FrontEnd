from fastapi import APIRouter, Depends, Query, HTTPException
from typing import List, Optional
from uuid import UUID
from app.modules.auth.config import current_active_user
from app.modules.auth.models import User
from app.modules.contacts.service import service as contact_service
from app.contracts.ui_schema import WebIAFirstResponse

router = APIRouter()

@router.get("/", response_model=WebIAFirstResponse)
@router.get("", response_model=WebIAFirstResponse)
async def get_all_leads_view(
    user: User = Depends(current_active_user)
):
    """Returns the General Management view for Leads (for Admins)."""
    leads = await contact_service.get_my_leads(user.id)
    rows = _transform_leads_to_rows(leads)
    
    return {
        "layout": "dashboard-standard",
        "components": [
            {
                "type": "typography",
                "tag": "h2",
                "text": "Gestión Integral de Leads",
                "class": "mb-4"
            },
            {
                "type": "card",
                "size": "col-12",
                "components": [
                    {
                        "type": "grid-visual",
                        "label": "Todos los Leads de la Organización",
                        "properties": {
                            "data_url": "/leads/data",
                            "columns": [
                                {"key": "name", "label": "Nombre"},
                                {"key": "email", "label": "Email"},
                                {"key": "phone", "label": "Teléfono"},
                                {"key": "status", "label": "Estado", "type": "badge"},
                                {"key": "score", "label": "Score", "type": "number"},
                                {"key": "created", "label": "Fecha"}
                            ],
                            "actions": [
                                {"label": "Ver Detalle", "icon": "ri-eye-line", "action": "navigate", "url": "/leads/{id}"}
                            ]
                        },
                        "rows": rows
                    }
                ]
            }
        ],
        "permissions_required": ["leads.view"]
    }

@router.get("/data", response_model=List[dict])
async def list_leads_data(
    user: User = Depends(current_active_user)
):
    """Returns raw data for all leads (Placeholder for Admin)."""
    # For now, return same as 'me' until manage logic is ready
    leads = await contact_service.get_my_leads(user.id)
    return _transform_leads_to_rows(leads)

def _transform_leads_to_rows(leads):
    rows = []
    for l in leads:
        rows.append({
            "id": str(l['id']),
            "identity": {
                "name": l['full_name'] or "S/N",
                "score": l['score_total'] or 0,
                "color": l.get('prio_color') or "thermal-none"
            },
            "engagement": {
                "score": l.get('score_engagement', 0),
                "totalScore": l.get('score_total', 0),
                "label": l.get('eng_label') or "-",
                "icon": l.get('eng_icon') or "ri-message-3-line",
                "color": l.get('eng_color') or "thermal-none"
            },
            "finance": {
                "score": l.get('score_finance', 0),
                "totalScore": l.get('score_total', 0),
                "label": l.get('fin_label') or "-",
                "icon": l.get('fin_icon') or "ri-bank-line",
                "color": l.get('fin_color') or "thermal-none"
            },
            "timeline": {
                "score": l.get('score_timeline', 0),
                "totalScore": l.get('score_total', 0),
                "label": l.get('tim_label') or "-",
                "icon": l.get('tim_icon') or "ri-time-line",
                "color": l.get('tim_color') or "thermal-none"
            },
            "match": {
                "score": l.get('score_match', 0),
                "totalScore": l.get('score_total', 0),
                "label": l.get('mat_label') or "-",
                "icon": l.get('mat_icon') or "ri-home-4-line",
                "color": l.get('mat_color') or "thermal-none"
            },
            "info": {
                "score": l.get('score_info', 0),
                "totalScore": l.get('score_total', 0),
                "label": l.get('inf_label') or "-",
                "icon": l.get('inf_icon') or "ri-file-list-3-line",
                "color": l.get('inf_color') or "thermal-none"
            },
            "outcome": {
                "score": 0,
                "totalScore": l.get('score_total', 0),
                "label": l.get('out_label') or "PENDIENTE",
                "icon": l.get('out_icon') or "ri-flag-line",
                "color": l.get('out_color') or "thermal-none"
            },
            "workflow": {
                "score": 0,
                "totalScore": l.get('score_total', 0),
                "label": l.get('wf_label') or "ACTIVO",
                "icon": l.get('wf_icon') or "ri-git-branch-line",
                "color": l.get('wf_color') or "thermal-none"
            },
            "email": l['email'] or "-",
            "phone": l['phone'] or "-",
            "status": {
                "label": l['status'] or "Nuevo",
                "color": "primary" if l['status'] == 'Follow-up' else "info"
            },
            "created": l['created_at'].strftime("%Y-%m-%d") if l['created_at'] else "-"
        })
    return rows

@router.get("/me/data", response_model=List[dict])
async def list_my_leads_data(
    user: User = Depends(current_active_user)
):
    """Returns raw data for Client Side Grid."""
    result = await contact_service.get_my_leads(user.id)
    
    # Transform items but keep structure
    items = _transform_leads_to_rows(result)
    
    return items

@router.get("/me", response_model=WebIAFirstResponse)
async def get_my_leads(
    user: User = Depends(current_active_user)
):
    """Returns the CRM Workview for the current user."""
    result = await contact_service.get_my_leads(user.id)
    rows = _transform_leads_to_rows(result)



    return {
        "layout": "dashboard-standard",
        "components": [
            {
                "type": "typography",
                "tag": "h2",
                "text": "Mis Leads",
                "class": "mb-4"
            },
            {
                "type": "card",
                "size": "col-12",
                "components": [
                    {
                        "type": "custom-leads-grid",
                        "label": "Panel de Leads",
                        "properties": {
                            "data_url": "/leads/me/data",
                            "columns": [
                                {"id": "identity", "label": "Lead / Calificación", "type": "gauge-identity", "sortable": True, "width": "250px", "icon": "ri-shield-user-line"},
                                {"id": "engagement", "label": "Engagement", "type": "scoring-pillar", "sortable": True, "icon": "ri-message-3-line"},
                                {"id": "finance", "label": "Finance", "type": "scoring-pillar", "sortable": True, "icon": "ri-bank-line"},
                                {"id": "timeline", "label": "TimeLine", "type": "scoring-pillar", "sortable": True, "icon": "ri-time-line"},
                                {"id": "match", "label": "Match", "type": "scoring-pillar", "sortable": True, "icon": "ri-home-4-line"},
                                {"id": "info", "label": "Info", "type": "scoring-pillar", "sortable": True, "icon": "ri-file-list-3-line"},
                                {"id": "outcome", "label": "Outcome", "type": "scoring-pillar", "sortable": True, "icon": "ri-flag-line"},
                                {"id": "workflow", "label": "Workflow", "type": "scoring-pillar", "sortable": True, "icon": "ri-git-branch-line"}
                            ],
                            "actions": [
                                {"label": "Ver Perfil", "icon": "ri-eye-line", "action": "navigate", "url": "/leads/{id}"},
                                {"label": "Chat", "icon": "ri-chat-3-line", "action": "navigate", "url": "/leads/{id}/chat"}
                            ]
                        },
                    }
                ]
            }
        ],
        "permissions_required": ["leads.view"]
    }


from fastapi import HTTPException

@router.get("/{lead_id}", response_model=WebIAFirstResponse)
async def get_lead_detail(lead_id: UUID, user: User = Depends(current_active_user)):
    """
    Returns the detailed view for a single lead.
    """
    # Fetch lead data
    leads = await contact_service.get_my_leads(user.id)
    lead = next((l for l in leads if l['id'] == lead_id), None)
    
    if not lead:
        raise HTTPException(status_code=404, detail="Lead not found or not assigned.")

    return {
        "layout": "dashboard-standard",
        "components": [
            {
                "type": "layout-row",
                "components": [
                    {
                        "type": "layout-col",
                        "size": "col-xl-4",
                        "components": [
                            {
                                "type": "card",
                                "title": "Información del Lead",
                                "components": [
                                    {"type": "typography", "tag": "h4", "text": lead['full_name'], "class": "mb-1"},
                                    {"type": "typography", "tag": "p", "text": f"Status: {lead['status'] or 'Nuevo'}", "class": "text-muted mb-3"},
                                    {"type": "typography", "tag": "p", "text": f"Email: {lead['email'] or '-'}"},
                                    {"type": "typography", "tag": "p", "text": f"Tel: {lead['phone'] or '-'}"}
                                ]
                            },
                            {
                                "type": "card-metric",
                                "title": "Score de Calificación",
                                "value": str(lead['score_total'] or 0),
                                "icon": "ri-star-fill",
                                "color": "warning",
                                "label": "Basado en interacciones"
                            }
                        ]
                    },
                    {
                        "type": "layout-col",
                        "size": "col-xl-8",
                        "components": [
                            {
                                "type": "card",
                                "title": "Análisis e Indicadores",
                                "components": [
                                    {"type": "typography", "tag": "p", "text": "Este lead muestra un alto interés en servicios financieros basado en su última interacción."},
                                    {
                                        "type": "button-group",
                                        "buttons": [
                                            {
                                                "label": "Ir a la Conversación (Chat)",
                                                "icon": "ri-message-3-line",
                                                "class": "btn-primary",
                                                "action": "navigate",
                                                "url": f"/leads/{lead_id}/chat"
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        ],
        "permissions_required": ["leads.view"]
    }

@router.get("/{lead_id}/chat", response_model=WebIAFirstResponse)
async def get_lead_chat(lead_id: UUID, user: User = Depends(current_active_user)):
    """
    Returns the chat view for a single lead.
    """
    # Fetch lead data
    leads = await contact_service.get_my_leads(user.id)
    lead = next((l for l in leads if l['id'] == lead_id), None)
    
    if not lead:
        raise HTTPException(status_code=404, detail="Lead not found or not assigned.")

    return {
        "layout": "dashboard-standard",
        "components": [
            {
                "type": "typography",
                "tag": "h2",
                "text": f"Conversación con {lead['full_name']}",
                "class": "mb-4"
            },
            {
                "type": "card",
                "title": "Chat en Tiempo Real",
                "components": [
                    {
                        "type": "typography",
                        "tag": "p",
                        "text": "Aquí se integrará el componente de chat (Próximamente)."
                    }
                ]
            }
        ],
        "permissions_required": ["leads.view"]
    }

