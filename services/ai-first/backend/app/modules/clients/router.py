from fastapi import APIRouter, Depends
from app.contracts.ui_schema import WebIAFirstResponse
from app.modules.auth.dependencies import RoleChecker

# Lock down: Only Super Admins can manage Tenants
router = APIRouter(dependencies=[Depends(RoleChecker(["admin"]))])

from .service import service, ClientSimple
from typing import List

@router.get("/simple-list", response_model=List[ClientSimple])
async def list_simple_clients():
    """Returns a simple ID/Name list for dropdowns."""
    return await service.list_simple()

@router.get("/clients", response_model=WebIAFirstResponse)
async def get_clients_view():
    """
    Returns the UI structure for the Clients Module.
    """
    return {
        "layout": "dashboard-standard",
        "components": [
            {
                "type": "grid",
                "components": [
                    {"type": "typography", "tag": "h2", "text": "Gestión de Clientes", "class": "mb-3"},
                    {"type": "card-metric", "label": "Total Clientes", "value": "1,240", "color": "primary"},
                    {"type": "card-metric", "label": "Nuevos Hoy", "value": "+12", "color": "success"},
                    {"type": "typography", "tag": "p", "text": "Aquí se cargará la tabla de clientes...", "class": "text-muted mt-4"}
                ]
            }
        ],
        "permissions_required": ["clients.view"]
    }
