from app.contracts.ui_schema import WebIAFirstResponse
from typing import List

def get_client_admin_schema(client_name: str, create_date: str, status: str, priority: str, contacts: List[dict], stats: dict, create_contact_schema: str, documents: List[dict]) -> dict:
    """
    Returns the High-Fidelity Project Overview SDUI Schema.
    Based on apps-projects-overview.html
    """
    overview_components = [
        {
            "type": "layout-row",
            "components": [
                {
                    "type": "layout-col",
                    "size": 8,
                    "components": [
                        {
                            "type": "card",
                            "title": "Resumen del Cliente",
                            "components": [
                                {
                                    "type": "typography", 
                                    "tag": "p", 
                                    "text": f"Gestión integral para {client_name}.",
                                    "class": "text-muted mb-4"
                                },
                                {
                                    "type": "layout-row",
                                    "components": [
                                        {"type": "layout-col", "size": 4, "components": [{"type": "card-metric", "label": "Total Leads", "value": str(stats['total_leads']), "color": "primary"}]},
                                        {"type": "layout-col", "size": 4, "components": [{"type": "card-metric", "label": "Propiedades", "value": str(stats['total_properties']), "color": "warning"}]}
                                    ]
                                }
                            ]
                        }
                    ]
                },
                {
                    "type": "layout-col",
                    "size": 4,
                    "components": [
                        {
                            "type": "contact-list-detailed",
                            "title": "Contactos Clave",
                            "members": contacts,
                            "createSchema": create_contact_schema
                        }
                    ]
                }
            ]
        }
    ]

    documents_components = [
        {
            "type": "file-grid",
            "title": "Repositorio Documental",
            "files": documents
        }
    ]

    return {
        "layout": "dashboard-project-overview",
        "properties": {
            "banner": {
                "title": client_name,
                "subtitle": "Panel de Administración",
                "avatar": "https://themesbrand.com/velzon/html/master/assets/images/companies/img-1.png",
                "status": status,
                "priority": priority,
                "create_date": create_date
            }
        },
        "tabs": [
            {"id": "project-overview", "active": True, "components": overview_components},
            {"id": "project-documents", "components": documents_components},
            {"id": "project-activities", "components": [{"type": "typography", "text": "Actividad reciente (Próximamente)."}]},
            {"id": "project-team", "components": [{"type": "typography", "text": "Vista detallada del equipo (Próximamente)."}]}
        ]
    }
