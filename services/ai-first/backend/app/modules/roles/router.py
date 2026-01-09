from fastapi import APIRouter, Depends, HTTPException, status
from typing import List, Optional
from uuid import UUID
import base64
import json

from app.modules.auth.config import current_active_user
from app.modules.auth.models import User
from app.modules.auth.dependencies import RoleChecker
from app.contracts.ui_schema import WebIAFirstResponse
from .schemas import RoleRow, RoleCreate, RoleUpdate
from .service import service

router = APIRouter(prefix="/system/roles", tags=["Roles Management"], dependencies=[Depends(RoleChecker(["admin"]))])

@router.get("", response_model=WebIAFirstResponse)
async def get_roles_view(user: User = Depends(current_active_user)):
    # Define form schema for creation and editing
    form_fields = [
        {"name": "name", "label": "Nombre del Rol", "type": "text", "required": True},
        {"name": "slug", "label": "Slug (Identificador)", "type": "text", "required": True},
    ]
    
    role_schema_b64 = base64.b64encode(json.dumps(form_fields).encode()).decode()

    return {
        "layout": "dashboard-standard",
        "title": "Gestión de Roles",
        "components": [
            {
                "type": "grid-visual",
                "label": "Roles del Sistema",
                "properties": {
                    "title": "Roles del Sistema",
                    "id": "roles_grid",
                    "data_url": "/system/roles/data",
                    "columns": [
                        {"key": "name", "label": "Nombre del Rol", "sortable": True},
                        {"key": "slug", "label": "Identificador (Slug)", "sortable": True}
                    ],
                    "actions": [
                        {
                            "label": "Editar",
                            "icon": "ri-pencil-line",
                            "action": "edit",
                            "url": "/system/roles/{id}",
                            "schema": role_schema_b64
                        },
                        {
                            "label": "Eliminar",
                            "icon": "ri-delete-bin-line",
                            "action": "delete",
                            "url": "/system/roles/{id}",
                            "color": "danger"
                        }
                    ],
                    "header_actions": [
                        {
                            "label": "Nuevo Rol",
                            "action": "modal-form",
                            "action_url": "/system/roles",
                            "modal_title": "Crear Nuevo Rol",
                            "color": "success",
                            "icon": "ri-add-line",
                            "schema": role_schema_b64
                        }
                    ]
                }
            }
        ]
    }

@router.get("/data", response_model=List[RoleRow])
async def list_data():
    return await service.list_roles()

@router.get("/{role_id}", response_model=RoleRow)
async def get_item(role_id: UUID):
    role = await service.get_role(role_id)
    if not role:
        raise HTTPException(status_code=404, detail="Role not found")
    return role

@router.post("", response_model=RoleRow)
async def create_item(item: RoleCreate):
    return await service.create_role(item)

@router.put("/{role_id}", response_model=RoleRow)
async def update_item(role_id: UUID, item: RoleUpdate):
    updated = await service.update_role(role_id, item)
    if not updated:
        raise HTTPException(status_code=404, detail="Role not found")
    return updated

@router.delete("/{role_id}")
async def delete_item(role_id: UUID):
    await service.delete_role(role_id)
    return {"status": "success"}
