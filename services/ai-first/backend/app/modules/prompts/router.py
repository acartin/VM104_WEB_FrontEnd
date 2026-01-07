from fastapi import APIRouter, HTTPException, Path
from typing import List
from uuid import UUID

from .schemas import PromptCreate, PromptUpdate, PromptRow
from .service import service

router = APIRouter(
    prefix="/prompts",
    tags=["AI Prompts"]
)

# --- DEBUG: FIXED CLIENT ID FOR TESTING ---
# In production, this would come from a dependency: get_current_user().client_id
TEST_CLIENT_ID = "019b4872-51f6-72d3-84c9-45183ff700d0"

# --- SDUI: Metadata for Frontend ---
@router.get("", response_model=dict)
async def get_ui_schema():
    """
    Returns the Server-Driven UI definition for the Prompts Grid.
    """
    # no data fetch here, grid will fetch data_url

    return {
        "layout": "dashboard-standard",
        "components": [
            {
                "type": "grid-visual",
                "label": "AI Prompts Management",
                "properties": {
                    "data_url": "/prompts/data",
                    "columns": [
                        {"key": "slug", "label": "Nombre Clave (Slug)", "type": "text"},
                        {"key": "prompt_text", "label": "Contenido del Prompt", "type": "text", "truncate": 50},
                        {"key": "is_active", "label": "Activo", "type": "badge", 
                         "badge_map": {"true": "success", "false": "danger"}}
                    ],
                    "actions": [
                        {
                            "label": "Editar", 
                            "action": "modal-form", 
                            "action_url": "/prompts/{id}",
                            "icon": "ri-pencil-fill",
                            "variant": "primary"
                        },
                        {
                            "label": "Eliminar", 
                            "action": "api-call", 
                            "method": "DELETE",
                            "action_url": "/prompts/{id}",
                            "confirm_message": "¿Estás seguro de eliminar este prompt?",
                            "icon": "ri-delete-bin-fill",
                            "variant": "danger"
                        }
                    ],
                    "header_actions": [
                        {
                            "label": "Nuevo Prompt", 
                            "action": "modal-form", 
                            "action_url": "/prompts",
                            "modal_title": "Crear Nuevo Prompt",
                            "color": "success",
                            "icon": "ri-add-line"
                        }
                    ],
                    "form_schema": [
                        {
                            "name": "slug", 
                            "label": "Slug / Nombre Único", 
                            "type": "text", 
                            "required": True, 
                            "min_length": 3,
                            "placeholder": "ej. bienvenida-cliente"
                        },
                        {
                            "name": "prompt_text", 
                            "label": "Instrucciones del Prompt", 
                            "type": "textarea", 
                            "required": True, 
                            "min_length": 10,
                            "rows": 8
                        },
                        {
                            "name": "is_active", 
                            "label": "Activo", 
                            "type": "switch", 
                            "default": True
                        }
                    ]
                }
            }
        ]
    }

# --- API: CRUD Endpoints ---

@router.get("/data", response_model=List[PromptRow])
async def list_data():
    return await service.list_prompts(TEST_CLIENT_ID)

@router.get("/{item_id}", response_model=PromptRow)
async def get_item(item_id: UUID):
    item = await service.get_prompt(TEST_CLIENT_ID, item_id)
    if not item:
        raise HTTPException(status_code=404, detail="Prompt not found")
    return item

@router.post("", response_model=PromptRow)
async def create_item(item: PromptCreate):
    return await service.create_prompt(TEST_CLIENT_ID, item)

@router.put("/{item_id}", response_model=PromptRow)
async def update_item(item_id: UUID, item: PromptUpdate):
    updated = await service.update_prompt(TEST_CLIENT_ID, item_id, item)
    if not updated:
        raise HTTPException(status_code=404, detail="Prompt not found")
    return updated

@router.delete("/{item_id}")
async def delete_item(item_id: UUID):
    success = await service.delete_prompt(TEST_CLIENT_ID, item_id)
    if not success:
        raise HTTPException(status_code=404, detail="Prompt not found")
    return {"status": "deleted"}
