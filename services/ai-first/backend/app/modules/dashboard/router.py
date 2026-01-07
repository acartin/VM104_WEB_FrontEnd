from fastapi import APIRouter
from app.contracts.ui_schema import UIAppShell

router = APIRouter()

@router.get("/app-init", response_model=UIAppShell)
async def app_init():
    """
    Returns the initial application shell structure: Sidebar + Initial Content.
    """
    return {
        "layout": "dashboard-shell",
        "sidebar": {
            "brand": "AI First",
            "items": [
                {"id": "dash", "label": "Dashboard", "icon": "ri-dashboard-2-line", "link": "/dashboard"},
                {"id": "clients", "label": "Clientes", "icon": "ri-user-line", "link": "/clients"},
                {"id": "sys", "label": "Sistema", "icon": "ri-settings-line", "subItems": [
                    {"id": "users", "label": "Usuarios", "link": "/users"},
                    {"id": "roles", "label": "Roles", "link": "/roles"}
                ]}
            ]
        },
        "content": [
            {
                "type": "grid",
                "components": [
                    {"type": "typography", "tag": "h2", "text": "Bienvenido al Panel de Control", "class": "mb-4"},
                    {"type": "card-metric", "label": "Estado del Sistema", "value": "Operativo", "color": "success"}
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
