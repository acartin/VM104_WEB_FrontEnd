from fastapi import APIRouter
from app.contracts.ui_schema import WebIAFirstResponse

router = APIRouter()

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
