from fastapi import APIRouter, Depends, HTTPException
from fastapi.encoders import jsonable_encoder
from app.contracts.ui_schema import WebIAFirstResponse
from app.modules.auth.config import current_active_user
from app.modules.auth.models import User
from app.modules.clients.service import service as client_service
from app.modules.contacts.service import service as contact_service
from .schema import get_client_admin_schema
from uuid import UUID

router = APIRouter()

@router.get("/client-admin", response_model=WebIAFirstResponse)
async def get_client_admin_dashboard(current_user: User = Depends(current_active_user)):
    """
    Returns the High-Fidelity Dashboard for Client Admins.
    """
    if not current_user.tenants:
        raise HTTPException(status_code=403, detail="No client context assigned.")
    
    client_id = current_user.tenants[0].client_id
    
    # 1. Fetch Data
    client = await client_service.get_client(client_id)
    if not client:
         raise HTTPException(status_code=404, detail="Client not found")
         
    raw_contacts = await contact_service.get_contacts_by_client(client_id)
    stats = await client_service.get_client_stats(client_id)
    
    # 2. Map Contacts to Member Format
    members = []
    
    # Define Schemas (reused from clients/router.py)
    # Note: We need to pass client_id dynamically for creation
    create_schema_def = [
        {"name": "first_name", "label": "Nombre", "type": "text", "required": True},
        {"name": "last_name", "label": "Apellido", "type": "text", "required": True},
        {"name": "position", "label": "Cargo / Puesto", "type": "text"},
        {"name": "is_active", "label": "Estado Activo", "type": "switch", "value": True},
        {"name": "channels", "label": "Canales de Comunicación", "type": "repeater", "source": "/contacts/categories"},
        {"name": "client_id", "type": "hidden", "value": str(client_id)}
    ]
    
    edit_schema_def = [
        {"name": "first_name", "label": "Nombre", "type": "text", "required": True},
        {"name": "last_name", "label": "Apellido", "type": "text", "required": True},
        {"name": "position", "label": "Cargo / Puesto", "type": "text"},
        {"name": "is_active", "label": "Estado Activo", "type": "switch"},
        {"name": "channels", "label": "Canales de Comunicación", "type": "repeater", "source": "/contacts/categories"}
    ]

    import json
    import base64
    
    create_schema_b64 = base64.b64encode(json.dumps(create_schema_def).encode('utf-8')).decode('utf-8')
    edit_schema_b64 = base64.b64encode(json.dumps(edit_schema_def).encode('utf-8')).decode('utf-8')

    for c in raw_contacts:
        members.append({
            "id": str(c.id),
            "name": f"{c.first_name} {c.last_name or ''}",
            "position": c.position or "Contacto",
            "is_active": c.is_active,
            "avatar": "https://themesbrand.com/velzon/html/master/assets/images/users/user-dummy-img.jpg",
            "editSchema": edit_schema_b64,
            "convertSchema": base64.b64encode(json.dumps([
                {"name": "email", "label": "Email de Login", "type": "text", "required": True, "value": next((ch.value for ch in c.channels if '@' in (ch.value or "")), "")},
                {"name": "password", "label": "Contraseña", "type": "password", "required": True}
            ]).encode('utf-8')).decode('utf-8'),
            "channels": [
                {
                    **ch.model_dump(),
                    "category_icon": "ri-mail-line" if ch.type == 'email' else "ri-phone-line" if ch.type == 'phone' else "ri-whatsapp-line" if ch.type == 'whatsapp' else "ri-question-line",
                    "category_name": ch.type.capitalize() if ch.type else "Otro"
                } for ch in c.channels
            ] if c.channels else []
        })

    # 2.5 Fetch Documents
    raw_docs = await client_service.get_client_documents(client_id)
    documents = []
    for d in raw_docs:
        item = d.dict()
        if d.created_at:
            item['created_at'] = d.created_at.strftime("%d %b, %Y")
        documents.append(item)

    # 3. Return Schema
    create_date_str = client.created_at.strftime("%d %b, %Y") if client.created_at else "N/A"
    
    return get_client_admin_schema(
        client_name=client.name,
        create_date=create_date_str,
        status="Activo",   # Placeholder until column exists
        priority="Normal", # Placeholder until column exists
        contacts=members,
        stats=stats.dict(),
        create_contact_schema=create_schema_b64,
        documents=documents
    )
