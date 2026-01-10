from fastapi import APIRouter, HTTPException, Depends, Body
from app.contracts.ui_schema import WebIAFirstResponse
from .schemas import ClientCreate, ClientUpdate, ClientRow, ClientSimple
from .service import service
from typing import List
from app.modules.auth.dependencies import RoleChecker
from uuid import UUID
import json
import base64
from app.modules.auth.config import current_active_user
from app.modules.auth.models import User as AuthUser

# Security: Admin and Client Admins (System Users) can access
router = APIRouter(dependencies=[Depends(RoleChecker(["admin", "client-admin"]))])

# --- SERVER DRIVEN UI (SDUI) ---

@router.get("/clients", response_model=WebIAFirstResponse)
async def get_clients_view(current_user: AuthUser = Depends(current_active_user)):
    """
    Returns the UI structure for the Clients Module.
    - Admin: Returns Grid (List of Clients).
    - Client Admin: Returns Dashboard (Tabs) for their specific Client.
    """
    
    # 1. Super Admin Logic (Show Grid)
    if current_user.is_superuser:
        return {
            "layout": "dashboard-standard",
            "components": [
                {
                    "type": "grid-visual",
                    "label": "Gestión de Clientes",
                    "properties": {
                        "data_url": "/clients/data",
                        "primary_key": "id",
                        "columns": [
                            {"key": "name", "label": "Nombre del Cliente", "type": "text", "sortable": True},
                            {"key": "country_name", "label": "País", "type": "text", "sortable": True},
                            {"key": "id", "label": "ID", "type": "text", "sortable": True, "hidden": True}
                        ],
                        "form_schema": [
                            {"name": "name", "label": "Nombre del Cliente", "type": "text", "required": True, "min_length": 2},
                            {
                                "name": "country_id", 
                                "label": "País", 
                                "type": "select", 
                                "source": "/countries/data", 
                                "required": True
                            }
                        ],
                        "actions": [
                            {
                                "type": "button",
                                "icon": "ri-edit-line",
                                "label": "Editar",
                                "action": "modal-form",
                                "action_url": "/clients/{id}", 
                                "modal_title": "Editar Cliente"
                            },
                            {
                                "type": "button",
                                "icon": "ri-delete-bin-line",
                                "label": "Eliminar",
                                "color": "danger",
                                "action": "api-call",
                                "method": "DELETE",
                                "action_url": "/clients/{id}",
                                "confirm_message": "¿Estás seguro de eliminar este cliente?"
                            },
                            {
                                "type": "button",
                                "icon": "ri-dashboard-line",
                                "label": "Gestionar",
                                "color": "info",
                                "action": "navigate",
                                "action_url": "/clients/{id}/dashboard"
                            }
                        ],
                        "header_actions": [
                            {
                                "type": "button",
                                "icon": "ri-add-line",
                                "label": "Nuevo Cliente",
                                "color": "success",
                                "action": "modal-form",
                                "action_url": "/clients",
                                "modal_title": "Nuevo Cliente",
                                "schema": [
                                    {"name": "name", "label": "Nombre del Cliente", "type": "text", "required": True, "min_length": 2},
                                    {
                                        "name": "country_id", 
                                        "label": "País", 
                                        "type": "select", 
                                        "source": "/countries/data", 
                                        "required": True
                                    }
                                ]
                            }
                        ]
                    }
                }
            ],
            "permissions_required": ["clients.view"]
        }

    # 2. Client Admin Logic (Show Dashboard directly)
    if current_user.tenants:
        # Assuming single tenant context for now
        client_id = current_user.tenants[0].client_id
        return await get_client_dashboard(client_id, current_user)

    # 3. Fallback (No tenant, not admin)
    raise HTTPException(status_code=403, detail="No client context assigned.")

# --- DATA API (CRUD) ---

@router.get("/clients/data", response_model=List[ClientRow])
async def list_clients_data():
    """Returns raw data for the Grid."""
    return await service.list_clients()

@router.get("/clients/simple-list", response_model=List[ClientSimple])
async def list_simple_clients():
    """Returns a simple ID/Name list for dropdowns."""
    return await service.list_simple()

@router.post("/clients", response_model=ClientRow)
async def create_client(client: ClientCreate):
    return await service.create_client(client)

@router.get("/clients/{client_id}", response_model=ClientRow)
async def get_client(client_id: UUID):
    """Used for populating Edit Modals"""
    item = await service.get_client(client_id)
    if not item:
        raise HTTPException(status_code=404, detail="Client not found")
    return item

@router.put("/clients/{client_id}", response_model=ClientRow)
async def update_client(client_id: UUID, client: ClientUpdate):
    item = await service.update_client(client_id, client)
    if not item:
        raise HTTPException(status_code=404, detail="Client not found")
    return item

@router.delete("/clients/{client_id}")
async def delete_client(client_id: UUID):
    success = await service.delete_client(client_id)
    if not success:
        raise HTTPException(status_code=404, detail="Client not found")
    return {"status": "deleted"}

@router.get("/clients/{client_id}/dashboard", response_model=WebIAFirstResponse)
async def get_client_dashboard(client_id: UUID, current_user: AuthUser = Depends(current_active_user)):
    """
    Returns the Tabs View for a specific Client.
    """
    client = await service.get_client(client_id)
    if not client:
        raise HTTPException(status_code=404, detail="Client not found")

    # Define Tabs
    tabs = []

    # 1. Overview (Everyone)
    tabs.append({
        "id": "overview",
        "label": "Resumen",
        "icon": "ri-pie-chart-line",
        "active": True,
        "content": [
                {"type": "typography", "variant": "p", "content": "Métricas generales próximamente."}
        ]
    })

    # 2. Contacts (Everyone authenticated for this client)
    # Note: Actions for contacts are already secured by backend, but we could hide "Create" button here if needed.
    tabs.append({
        "id": "contacts",
        "label": "Contactos",
        "icon": "ri-contacts-book-line",
        "content": [
            {
                "type": "grid-visual",
                "label": "Directorio de Contactos",
                "properties": {
                    "data_url": f"/contacts?client_id={client_id}",
                    "primary_key": "id",
                    "columns": [
                        {"key": "first_name", "label": "Nombre", "type": "text", "sortable": True},
                        {"key": "last_name", "label": "Apellido", "type": "text", "sortable": True},
                        {"key": "position", "label": "Posición", "type": "text"},
                        {"key": "is_active", "label": "Estado", "type": "badge", "badge_map": {"True": "success", "False": "danger"}}
                    ],
                    "header_actions": [
                        {
                            "type": "button",
                            "icon": "ri-user-add-line",
                            "label": "Nuevo Contacto",
                            "color": "primary",
                            "action": "modal-form",
                            "action_url": "/contacts",
                            "method": "POST",
                            "modal_title": "Crear Contacto",
                            "schema": [
                                {"name": "first_name", "label": "Nombre", "type": "text", "required": True},
                                {"name": "last_name", "label": "Apellido", "type": "text", "required": True},
                                {"name": "position", "label": "Cargo / Puesto", "type": "text"},
                                {"name": "is_active", "label": "Estado Activo", "type": "switch", "value": True},
                                {"name": "channels", "label": "Canales de Comunicación", "type": "repeater", "source": "/contacts/categories"},
                                {"name": "client_id", "type": "hidden", "value": str(client_id)}
                            ]
                        }
                    ],
                    "actions": [
                        {
                            "type": "button",
                            "icon": "ri-edit-line",
                            "label": "Editar",
                            "action": "modal-form",
                            "action_url": "/contacts/{id}",
                            "modal_title": "Editar Contacto",
                            "schema": [
                                {"name": "first_name", "label": "Nombre", "type": "text", "required": True},
                                {"name": "last_name", "label": "Apellido", "type": "text", "required": True},
                                {"name": "position", "label": "Cargo / Puesto", "type": "text"},
                                {"name": "is_active", "label": "Estado Activo", "type": "switch"},
                                {"name": "channels", "label": "Canales de Comunicación", "type": "repeater", "source": "/contacts/categories"}
                            ]
                        },
                        {
                            "type": "button",
                            "icon": "ri-delete-bin-line",
                            "label": "Eliminar",
                            "color": "danger",
                            "action": "delete",
                            "action_url": "/contacts/{id}",
                            "confirm_message": "¿Estás seguro de que deseas eliminar este contacto?"
                        }
                    ]
                }
            }
        ]
    })

    # 3. Prompts (Everyone)
    tabs.append({
        "id": "prompts",
        "label": "Prompts",
        "icon": "ri-robot-line",
        "content": [
            {"type": "typography", "variant": "p", "content": "Gestión de Prompts personalizados para este cliente (Próximamente)."}
        ]
    })

    # 4. Settings (Superuser ONLY)
    if current_user.is_superuser:
        tabs.append({
            "id": "settings",
            "label": "Configuración",
            "icon": "ri-settings-4-line",
            "content": [
                {"type": "typography", "variant": "h5", "content": "Configuración Avanzada (Solo Admin)"},
                {"type": "typography", "variant": "p", "content": "Aquí irían opciones de facturación, límites de tokens, etc."}
            ]
        })

    return {
        "layout": "dashboard-standard",
        "components": [
            {
                "type": "typography",
                "variant": "h4",
                "content": f"Cliente: {client.name} <span class='badge bg-success ms-2'>Active</span>",
                "class": "mb-4"
            },
            {
                "type": "tabs",
                "items": tabs
            }
        ],
        "permissions_required": ["clients.view"]
    }
