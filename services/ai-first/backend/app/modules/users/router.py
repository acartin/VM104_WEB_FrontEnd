from fastapi import APIRouter, Depends, HTTPException, status
from typing import List, Optional
from uuid import UUID
import base64
import json

from app.modules.auth.config import current_active_user
from app.modules.auth.models import User
from app.modules.auth.dependencies import RoleChecker
from app.modules.users.schemas import UserRow, UserCreate, UserUpdate
from app.modules.users.service import service
from app.contracts.ui_schema import WebIAFirstResponse

router = APIRouter(prefix="/system/users", tags=["Users Management"])

@router.get("", response_model=WebIAFirstResponse)
async def get_ui_schema(user: User = Depends(RoleChecker(["admin"]))):
    # Base64 encoded schemas for the frontend actions
    form_fields = [
        {"name": "email", "label": "Email", "type": "text", "required": True},
        {"name": "name", "label": "Nombre Completo", "type": "text", "required": False},
        {"name": "password", "label": "Contraseña", "type": "password", "required": True},
        {"name": "is_active", "label": "Activo", "type": "switch", "required": False},
        {"name": "is_superuser", "label": "Superusuario (God Mode)", "type": "switch", "required": False},
        {
            "name": "role_id", 
            "label": "Rol", 
            "type": "select", 
            "source": "/system/users/roles/simple-list", 
            "required": True
        },
        {
            "name": "client_id", 
            "label": "Cliente (Tenant)", 
            "type": "select", 
            "source": "/clients/simple-list", 
            "required": True
        }
    ]
    
    # Edit schema (password optional)
    edit_form_fields = [f.copy() for f in form_fields]
    for f in edit_form_fields:
        if f["name"] == "password":
            f["required"] = False
            f["label"] = "Contraseña (dejar en blanco para no cambiar)"

    create_schema_b64 = base64.b64encode(json.dumps(form_fields).encode()).decode()
    edit_schema_b64 = base64.b64encode(json.dumps(edit_form_fields).encode()).decode()

    return {
        "layout": "dashboard-standard",
        "title": "Gestión de Usuarios",
        "components": [
            {
                "type": "grid-visual",
                "label": "Usuarios Registrados",
                "properties": {
                    "title": "Usuarios Registrados",
                    "id": "users_grid",
                    "data_url": "/system/users/data",
                    "columns": [
                        {"key": "email", "label": "Email", "sortable": True},
                        {"key": "name", "label": "Nombre"},
                        {"key": "role_name", "label": "Rol", "type": "badge", "badge_map": {"admin": "danger", "client-admin": "warning", "client-user": "primary"}},
                        {"key": "client_name", "label": "Cliente"},
                        {"key": "is_active", "label": "Estado", "type": "badge", "badge_map": {"true": "success", "false": "secondary"}}
                    ],
                    "actions": [
                        {
                            "label": "Editar",
                            "icon": "ri-pencil-line",
                            "action": "edit",
                            "url": "/system/users/{id}",
                            "schema": edit_schema_b64
                        },
                        {
                            "label": "Eliminar",
                            "icon": "ri-delete-bin-line",
                            "action": "delete",
                            "url": "/system/users/{id}",
                            "color": "danger"
                        }
                    ],
                    "header_actions": [
                        {
                            "label": "Nuevo Usuario",
                            "action": "modal-form",
                            "action_url": "/system/users",
                            "modal_title": "Crear Usuario",
                            "color": "success",
                            "icon": "ri-user-add-line",
                            "schema": create_schema_b64
                        }
                    ]
                }
            }
        ]
    }

@router.get("/data", response_model=List[UserRow])
async def list_data(user: User = Depends(RoleChecker(["admin"]))):
    return await service.list_users()

@router.get("/roles/simple-list")
async def list_roles(user: User = Depends(current_active_user)):
    return await service.list_roles()

@router.get("/{item_id}", response_model=UserRow)
async def get_item(item_id: UUID, user: User = Depends(RoleChecker(["admin"]))):
    item = await service.get_user(item_id)
    if not item:
        raise HTTPException(status_code=404, detail="User not found")
    return item

@router.post("", response_model=UserRow)
async def create_item(item: UserCreate, user: User = Depends(RoleChecker(["admin"]))):
    return await service.create_user(item)

@router.put("/{item_id}", response_model=UserRow)
async def update_item(item_id: UUID, item: UserUpdate, user: User = Depends(RoleChecker(["admin"]))):
    return await service.update_user(item_id, item)

@router.delete("/{item_id}")
async def delete_item(item_id: UUID, user: User = Depends(RoleChecker(["admin"]))):
    await service.delete_user(item_id)
    return {"status": "success"}
