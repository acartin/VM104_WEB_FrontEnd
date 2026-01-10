from typing import List, Dict, Any

def get_client_user_schema(
    user_name: str,
    my_leads: List[Dict],
    stats: Dict[str, Any],
    top_leads: List[Dict] = []
):
    """
    Constructs the SDUI JSON for the Client User Dashboard (Seller).
    """
    return {
        "layout": "dashboard-standard",
        "components": [
            # 1. Page Banner
            {
                "type": "project-banner",
                "title": f"¡Hola, {user_name}!",
                "subtitle": "Aquí tienes un resumen de tu actividad y leads pendientes.",
                "tabs": [
                    {"label": "Overview", "active": True, "link": "/dashboard/client-user"},
                    {"label": "Mis Leads", "active": False, "link": "/leads/me"}
                ]
            },

            # 2. Key Metrics Row
            {
                "type": "grid",
                "components": [
                    {
                        "type": "card-metric",
                        "label": "Mis Leads Activos",
                        "value": stats.get("total_leads", 0),
                        "subValue": "En seguimiento",
                        "icon": "ri-team-line",
                        "color": "primary",
                        "size": "col-xl-4 col-md-4"
                    },
                    {
                        "type": "card-metric",
                        "label": "Acciones de Hoy",
                        "value": stats.get("actions_today", 0),
                        "subValue": "Llamadas / Mensajes",
                        "icon": "ri-calendar-check-line",
                        "color": "success",
                        "size": "col-xl-4 col-md-4"
                    },
                    {
                        "type": "card-metric",
                        "label": "Leads Urgentes",
                        "value": len(top_leads),
                        "subValue": "Requieren atención",
                        "icon": "ri-error-warning-line",
                        "color": "danger",
                        "size": "col-xl-4 col-md-4"
                    }
                ]
            },

            # 3. Main Content Row
            {
                "type": "grid",
                "components": [
                    # Left: Urgent Leads / Attention
                    {
                        "type": "card",
                        "size": "col-xl-8",
                        "components": [
                            {
                                "type": "contact-list-detailed",
                                "title": "Leads que Requieren Atención",
                                "members": top_leads
                            }
                        ]
                    },
                    # Right: Weekly Performance Chart (Placeholder for now)
                    {
                        "type": "card",
                        "size": "col-xl-4",
                        "title": "Mi Desempeño",
                        "components": [
                            {"type": "typography", "tag": "p", "text": "Progreso semanal de contactos.", "class": "text-muted mb-4"},
                            {"type": "typography", "tag": "h3", "text": "85%", "class": "text-success"},
                            {"type": "typography", "tag": "span", "text": "Meta mensual alcanzada", "class": "text-muted fs-12"}
                        ]
                    }
                ]
            },

            # 4. Full List Row
            {
                "type": "grid",
                "components": [
                    {
                        "type": "card",
                        "size": "col-12",
                        "components": [
                            {
                                "type": "member-list-card",
                                "title": "Todos Mis Leads Recientes",
                                "members": my_leads
                            }
                        ]
                    }
                ]
            }
        ],
        "permissions_required": ["dashboard.view"]
    }
