from fastapi import APIRouter, HTTPException, Depends, Body
from app.contracts.ui_schema import WebIAFirstResponse
from .schemas import ClientCreate, ClientUpdate, ClientRow, ClientSimple
from .service import service
from typing import List
from app.modules.auth.dependencies import RoleChecker
from uuid import UUID
import json
import base64

# Security: Admin and Client Admins (System Users) can access
router = APIRouter(dependencies=[Depends(RoleChecker(["admin", "client-admin"]))])

# --- SERVER DRIVEN UI (SDUI) ---

@router.get("/clients", response_model=WebIAFirstResponse)
async def get_clients_view():
    """
    Returns the UI structure for the Clients Module.
    Defines the Grid Layout and allowed Actions.
    """
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
                            "source": "/countries/data", # MATCHES ModalForm.js Expectation
                            "required": True
                        }
                    ],
                    "actions": [
                        {
                            "type": "button",
                            "icon": "ri-edit-line",
                            "label": "Editar",
                            "action": "modal-form",
                            "action_url": "/clients/{id}", # GET then PUT
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
                                    "source": "/countries/data", # MATCHES ModalForm.js Expectation
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
