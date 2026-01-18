from typing import List, Optional
from pydantic import BaseModel
from app.contracts.ui_schema import UIComponent as DashboardComponent, WebIAFirstResponse


class ClientUserDashboardSchema(BaseModel):
    layout: str
    components: List[DashboardComponent]
    debug_data: Optional[dict] = None  # Added for debugging lead data in console


from app.modules.leads.router import LEADS_GRID_CONFIG_FULL

def get_seller_workspace_schema(user_id: str) -> ClientUserDashboardSchema:
    # 1. Configuración del Grid (Importada de la Fuente de Verdad)
    grid_config = LEADS_GRID_CONFIG_FULL.copy() # Copia para evitar mutaciones no deseadas
    # grid_id se mantiene como 'leads-me' para compartir Vistas Guardadas


    # 2. Construcción de Componentes (Tabs)
    return ClientUserDashboardSchema(
        layout="dashboard-standard",
        components=[
            DashboardComponent(
                type="tabs",
                # Tabs.js expects 'items' array at root, not 'components'
                items=[
                    {
                        "id": "tab-overview", 
                        "label": "Inicio", 
                        "icon": "ri-home-4-line",
                        "active": True,
                        "content": [
                            DashboardComponent(
                                type="row",
                                class_="row mb-4",
                                components=[
                                    # Metric: Leads Nuevos Hoy
                                    DashboardComponent(
                                        type="col", class_="col-md-4",
                                        components=[
                                            DashboardComponent(
                                                type="card-metric",
                                                properties={
                                                    "title": "Nuevos Hoy", "value": "3", 
                                                    "icon": "ri-user-add-line", "color": "success", "trend": "Igual que ayer"
                                                }
                                            )
                                        ]
                                    ),
                                    # Metric: Tareas Pendientes
                                    DashboardComponent(
                                        type="col", class_="col-md-4",
                                        components=[
                                            DashboardComponent(
                                                type="card-metric",
                                                properties={
                                                    "title": "Tareas Pendientes", "value": "5", 
                                                    "icon": "ri-task-line", "color": "warning", "trend": "2 urgentes"
                                                }
                                            )
                                        ]
                                    ),
                                     # Metric: Mi Conversión
                                    DashboardComponent(
                                        type="col", class_="col-md-4",
                                        components=[
                                            DashboardComponent(
                                                type="card-metric",
                                                properties={
                                                    "title": "Mi Conversión", "value": "12%", 
                                                    "icon": "ri-percent-line", "color": "primary", "trend": "Buen trabajo"
                                                }
                                            )
                                        ]
                                    ),
                                ]
                            ),
                             DashboardComponent(
                                type="card", properties={"title": "Actividad Reciente"},
                                components=[
                                     DashboardComponent(
                                        type="typography", text="Aquí irá el timeline de tus interacciones...", tag="p", class_="text-muted"
                                    )
                                ]
                            )
                        ]
                    },
                    {
                        "id": "tab-leads", 
                        "label": "Mis Leads", 
                        "icon": "ri-user-star-line",
                        "content": [
                            DashboardComponent(
                                type="custom-leads-grid",
                                properties=grid_config
                            )
                        ]
                    }
                ]
            )
        ]
    )

def get_lead_detail_schema(user_id: str, lead_id: str, lead: dict) -> ClientUserDashboardSchema:
    """
    Returns the Schema for the Lead Detail View (Drill-down).
    """
    # Extract lead data with defaults
    full_name = lead.get('full_name') or 'Sin Nombre'
    email = lead.get('email') or 'Sin email'
    phone = lead.get('phone') or 'Sin teléfono'
    score_total = lead.get('score_total') or 0
    status_label = lead.get('status_label') or 'Nuevo'
    status_color = lead.get('status_color') or 'warning'
    
    # Calculate gauge color based on score (Matching frontend formatters.js)
    gauge_color = '#475569'
    if score_total >= 90: gauge_color = '#f06548'
    elif score_total >= 70: gauge_color = '#f7b84b'
    elif score_total >= 50: gauge_color = '#4b38b3'
    elif score_total >= 20: gauge_color = '#0ab39c'
    
    # Get initials for avatar
    initials = ''.join([n[0].upper() for n in full_name.split()[:2]]) if full_name != 'Sin Nombre' else 'L'
    return ClientUserDashboardSchema(
        layout="dashboard-standard",
        debug_data=lead,
        components=[
            # Link to return to main dashboard
            DashboardComponent(
                type="row", class_="mb-3",
                components=[
                    DashboardComponent(
                        type="typography", 
                        text=f"<a href='/dashboard' class='text-decoration-none text-muted'><i class='ri-arrow-left-line me-1'></i> Volver al Dashboard</a>", 
                        tag="div"
                    )
                ]
            ),
            # Banner Card (Profile Header)
            DashboardComponent(
                type="card",
                class_="mb-3",
                components=[
                    DashboardComponent(
                        type="row",
                        class_="align-items-center",
                        components=[
                            DashboardComponent(
                                type="col", class_="col-6",
                                components=[
                                    # Top area: Gauge + Name (horizontal)
                                    DashboardComponent(
                                        type="row", class_="align-items-center mb-3",
                                        components=[
                                            DashboardComponent(
                                                type="col", class_="col-auto",
                                                components=[
                                                    DashboardComponent(
                                                        type="gauge",
                                                        properties={
                                                            "value": score_total,
                                                            "size": 60,
                                                            "color": lead.get('prio_color')
                                                        }
                                                    )
                                                ]
                                            ),
                                            DashboardComponent(
                                                type="col", class_="col",
                                                components=[
                                                    DashboardComponent(type="typography", text=full_name, tag="h4", class_="mb-0")
                                                ]
                                            )
                                        ]
                                    ),
                                    # Details area
                                    DashboardComponent(type="typography", text=f"<p class='mb-2' style='color: #6c757d;'>{email} • {phone}</p>", tag="div"),
                                    DashboardComponent(type="typography", text=f"<span class='badge bg-{status_color}'>{status_label}</span>", tag="div", class_="mb-3"),
                                    DashboardComponent(
                                        type="button-group",
                                        properties={
                                            "buttons": [
                                                {"label": "Llamar", "icon": "ri-phone-line", "class": "btn-soft-success"},
                                                {"label": "Email", "icon": "ri-mail-line", "class": "btn-soft-warning"},
                                                {"label": "Más", "icon": "ri-more-2-fill", "class": "btn-ghost-secondary"}
                                            ]
                                        }
                                    )
                                ]
                            ),
                            DashboardComponent(
                                type="col", class_="col-6",
                                components=[
                                    # Columna vacía
                                ]
                            )
                        ]
                    )
                ]
            ),
            
            # Tabs (Información, Audit, Fuente)
            DashboardComponent(
                type="tabs",
                items=[
                    {
                        "id": "tab-info", "label": "Información", "icon": "ri-information-line", "active": True,
                        "content": [
                            DashboardComponent(
                                type="row",
                                components=[
                                    DashboardComponent(
                                        type="col", size=6,
                                        components=[
                                            DashboardComponent(
                                                type="card",
                                                title="Datos del Perfil",
                                                components=[
                                                    DashboardComponent(type="typography", text="<div class='p-4 text-center text-muted'>Contenido Columna 1</div>", tag="div")
                                                ]
                                            )
                                        ]
                                    ),
                                    DashboardComponent(
                                        type="col", size=6,
                                        components=[
                                            DashboardComponent(
                                                type="card",
                                                title="Actividad y Calificación",
                                                components=[
                                                    DashboardComponent(type="typography", text="<div class='p-4 text-center text-muted'>Contenido Columna 2</div>", tag="div")
                                                ]
                                            )
                                        ]
                                    )
                                ]
                            )
                        ]
                    },
                    {
                        "id": "tab-audit", "label": "Audit", "icon": "ri-file-list-3-line",
                        "content": [
                            DashboardComponent(
                                type="card",
                                components=[
                                    DashboardComponent(type="typography", text="<div class='p-5 text-center text-muted'><h4>Historial de Cambios</h4><p>El audit trail se mostrará aquí.</p></div>", tag="div")
                                ]
                            )
                        ]
                    },
                     {
                        "id": "tab-source", "label": "Fuente", "icon": "ri-links-line",
                        "content": [
                            DashboardComponent(
                                type="card",
                                components=[
                                    DashboardComponent(type="typography", text="<div class='p-5 text-center text-muted'><h4>Origen del Lead</h4><p>Información de la fuente se mostrará aquí.</p></div>", tag="div")
                                ]
                            )
                        ]
                    }
                ]
            )
        ]
    )
