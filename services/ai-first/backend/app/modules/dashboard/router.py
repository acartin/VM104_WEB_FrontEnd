from fastapi import APIRouter, Depends
from app.contracts.ui_schema import UIAppShell
from .menus import get_menu_for_role
from app.modules.auth.config import current_active_user
from app.modules.auth.models import User

router = APIRouter()

@router.get("/app-init", response_model=UIAppShell)
async def app_init(user: User = Depends(current_active_user)):
    """
    Returns the initial application shell structure: Sidebar + Initial Content.
    Dynamic based on User Role.
    """
    # 1. Determine Role
    # For MVP, we take the role from the FIRST tenant connection.
    # In future, this could come from a 'X-Tenant-ID' header context.
    from app.modules.auth.utils import get_current_role_slug
    current_role = get_current_role_slug(user)
    
    # 2. Get Menu
    menu_items = get_menu_for_role(current_role)
    
    return {
        "layout": "dashboard-shell",
        "sidebar": {
            "brand": "AI First",
            "items": menu_items
        },
        "content": [
            {
                "type": "grid",
                "components": [
                    {"type": "typography", "tag": "h2", "text": f"Bienvenido, {user.name or 'Usuario'}", "class": "mb-4"},
                    {"type": "card-metric", "label": "Tu Rol Actual", "value": current_role.upper(), "color": "info"}
                ]
            }
        ]
    }

# We can keep check-contract here if it's dashboard related, or move to a 'core' module. 
# For now, let's keep it here as it validates the shell components.
from app.contracts.ui_schema import WebIAFirstResponse

@router.get("/check-contract", response_model=WebIAFirstResponse)
async def validate_ia_guard():
    # Respuesta enriquecida para validación visual de Tema Velzon
    return {
        "layout": "dashboard-standard",
        "components": [
            {
                "type": "grid",
                "components": [
                    {"type": "typography", "tag": "h2", "text": "Tema Velzon Activo", "class": "mb-3"},
                    {"type": "typography", "tag": "p", "text": "Esta es una prueba de tipografía Inter con los colores del tema.", "color": "muted"},
                    
                    {"type": "button-group", "buttons": [
                        {"label": "Primary Action", "variant": "primary"},
                        {"label": "Success", "variant": "success"},
                        {"label": "Danger Zone", "variant": "danger"}
                    ]},

                    {"type": "card-metric", "label": "Validación Exitosa", "color": "success"},
                ]
            }
        ],
        "permissions_required": ["system.view"]
    }
