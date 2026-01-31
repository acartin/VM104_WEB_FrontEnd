from fastapi import APIRouter, HTTPException, Depends, Body, Query
from app.contracts.ui_schema import WebIAFirstResponse
from .schemas import ClientCreate, ClientUpdate, ClientRow, ClientSimple
from .service import service
from typing import List
from app.modules.auth.dependencies import RoleChecker
from uuid import UUID
import json
import base64
from app.modules.auth.config import current_active_user
from app.modules.auth.models import User as AuthUser
import httpx
from fastapi import UploadFile, File, Form
from typing import Optional

# Security: Admin and Client Admins (System Users) can access
router = APIRouter(dependencies=[Depends(RoleChecker(["admin", "client-admin"]))])

# --- SERVER DRIVEN UI (SDUI) ---

@router.get("/clients", response_model=WebIAFirstResponse)
async def get_clients_view(current_user: AuthUser = Depends(current_active_user)):
    """
    Returns the UI structure for the Clients Module.
    - Admin: Returns Grid (List of Clients).
    - Client Admin: Returns Dashboard (Tabs) for their specific Client.
    """
    
    # 1. Super Admin Logic (Show Grid)
    if current_user.is_superuser:
        return {
            "layout": "dashboard-standard",
            "components": [
                {
                    "type": "grid-visual",
                    "label": "Gestión de Clientes",
                    "properties": {
                        "data_url": "/clients/data",
                        "primary_key": "id",
                        "columns": [
                            {"id": "name", "label": "Nombre del Cliente", "type": "text", "sortable": True},
                            {"id": "country_name", "label": "País", "type": "text", "sortable": True},
                            {"id": "id", "label": "ID", "type": "text", "sortable": True, "hidden": True}
                        ],
                        "enableFilters": True,
                        "filterConfig": {
                            "searchFields": ["name", "country_name"],
                            "filterableColumns": [
                                {"id": "country_name", "label": "País", "icon": "ri-earth-line"}
                            ]
                        },
                        "form_schema": [
                            {"name": "name", "label": "Nombre del Cliente", "type": "text", "required": True, "min_length": 2},
                            {
                                "name": "country_id", 
                                "label": "País", 
                                "type": "select", 
                                "source": "/countries/data", 
                                "required": True
                            }
                        ],
                        "actions": [
                            {
                                "type": "button",
                                "icon": "ri-edit-line",
                                "label": "Editar",
                                "action": "modal-form",
                                "action_url": "/clients/{id}", 
                                "modal_title": "Editar Cliente"
                            },
                            {
                                "type": "button",
                                "icon": "ri-delete-bin-line",
                                "label": "Eliminar",
                                "color": "danger",
                                "action": "api-call",
                                "method": "DELETE",
                                "action_url": "/clients/{id}",
                                "confirm_message": "¿Estás seguro de eliminar este cliente?"
                            },
                            {
                                "type": "button",
                                "icon": "ri-dashboard-line",
                                "label": "Gestionar",
                                "color": "info",
                                "action": "navigate",
                                "action_url": "/clients/{id}/dashboard"
                            }
                        ],
                        "header_actions": [
                            {
                                "type": "button",
                                "icon": "ri-add-line",
                                "label": "Nuevo Cliente",
                                "color": "success",
                                "action": "modal-form",
                                "action_url": "/clients",
                                "modal_title": "Nuevo Cliente",
                                "schema": [
                                    {"name": "name", "label": "Nombre del Cliente", "type": "text", "required": True, "min_length": 2},
                                    {
                                        "name": "country_id", 
                                        "label": "País", 
                                        "type": "select", 
                                        "source": "/countries/data", 
                                        "required": True
                                    }
                                ]
                            }
                        ]
                    }
                }
            ],
            "permissions_required": ["clients.view"]
        }

    # 2. Client Admin Logic (Show Dashboard directly)
    if current_user.tenants:
        # Assuming single tenant context for now
        client_id = current_user.tenants[0].client_id
        return await get_client_dashboard(client_id, current_user)

    # 3. Fallback (No tenant, not admin)
    raise HTTPException(status_code=403, detail="No client context assigned.")

# --- DATA API (CRUD) ---

@router.get("/clients/data", response_model=List[ClientRow])
async def list_clients_data():
    """Returns raw data for the Grid."""
    return await service.list_clients()

@router.get("/clients/simple-list", response_model=List[ClientSimple])
async def list_simple_clients():
    """Returns a simple ID/Name list for dropdowns."""
    return await service.list_simple()

@router.post("/clients", response_model=ClientRow)
async def create_client(client: ClientCreate):
    return await service.create_client(client)

@router.get("/clients/{client_id}", response_model=ClientRow)
async def get_client(client_id: UUID):
    """Used for populating Edit Modals"""
    item = await service.get_client(client_id)
    if not item:
        raise HTTPException(status_code=404, detail="Client not found")
    return item

@router.put("/clients/{client_id}", response_model=ClientRow)
async def update_client(client_id: UUID, client: ClientUpdate):
    item = await service.update_client(client_id, client)
    if not item:
        raise HTTPException(status_code=404, detail="Client not found")
    return item

@router.delete("/clients/{client_id}")
async def delete_client(client_id: UUID):
    success = await service.delete_client(client_id)
    if not success:
        raise HTTPException(status_code=404, detail="Client not found")
    return {"status": "deleted"}

@router.get("/clients/{client_id}/dashboard", response_model=WebIAFirstResponse)
async def get_client_dashboard(client_id: UUID, current_user: AuthUser = Depends(current_active_user)):
    """
    Returns the Tabs View for a specific Client.
    """
    client = await service.get_client(client_id)
    if not client:
        raise HTTPException(status_code=404, detail="Client not found")

    # Define Tabs
    tabs = []

    # 1. Overview (Everyone)
    tabs.append({
        "id": "overview",
        "label": "Resumen",
        "icon": "ri-pie-chart-line",
        "active": True,
        "content": [
                {"type": "typography", "variant": "p", "content": "Métricas generales próximamente."}
        ]
    })

    # 2. Contacts (Everyone authenticated for this client)
    # Note: Actions for contacts are already secured by backend, but we could hide "Create" button here if needed.
    tabs.append({
        "id": "contacts",
        "label": "Contactos",
        "icon": "ri-contacts-book-line",
        "content": [
            {
                "type": "grid-visual",
                "label": "Directorio de Contactos",
                "properties": {
                    "data_url": f"/contacts?client_id={client_id}",
                    "primary_key": "id",
                    "columns": [
                        {"id": "first_name", "label": "Nombre", "type": "text", "sortable": True},
                        {"id": "last_name", "label": "Apellido", "type": "text", "sortable": True},
                        {"id": "position", "label": "Posición", "type": "text"},
                        {"id": "is_active", "label": "Estado", "type": "badge", "badge_map": {"True": "success", "False": "danger"}}
                    ],
                    "header_actions": [
                        {
                            "type": "button",
                            "icon": "ri-user-add-line",
                            "label": "Nuevo Contacto",
                            "color": "primary",
                            "action": "modal-form",
                            "action_url": "/contacts",
                            "method": "POST",
                            "modal_title": "Crear Contacto",
                            "schema": [
                                {"name": "first_name", "label": "Nombre", "type": "text", "required": True},
                                {"name": "last_name", "label": "Apellido", "type": "text", "required": True},
                                {"name": "position", "label": "Cargo / Puesto", "type": "text"},
                                {"name": "is_active", "label": "Estado Activo", "type": "switch", "value": True},
                                {"name": "channels", "label": "Canales de Comunicación", "type": "repeater", "source": "/contacts/categories"},
                                {"name": "client_id", "type": "hidden", "value": str(client_id)}
                            ]
                        }
                    ],
                    "actions": [
                        {
                            "type": "button",
                            "icon": "ri-edit-line",
                            "label": "Editar",
                            "action": "modal-form",
                            "action_url": "/contacts/{id}",
                            "modal_title": "Editar Contacto",
                            "schema": [
                                {"name": "first_name", "label": "Nombre", "type": "text", "required": True},
                                {"name": "last_name", "label": "Apellido", "type": "text", "required": True},
                                {"name": "position", "label": "Cargo / Puesto", "type": "text"},
                                {"name": "is_active", "label": "Estado Activo", "type": "switch"},
                                {"name": "channels", "label": "Canales de Comunicación", "type": "repeater", "source": "/contacts/categories"}
                            ]
                        },
                        {
                            "type": "button",
                            "icon": "ri-delete-bin-line",
                            "label": "Eliminar",
                            "color": "danger",
                            "action": "delete",
                            "action_url": "/contacts/{id}",
                            "confirm_message": "¿Estás seguro de que deseas eliminar este contacto?"
                        }
                    ]
                }
            }
        ]
    })

    # 3. Prompts (Everyone)
    tabs.append({
        "id": "prompts",
        "label": "Prompts",
        "icon": "ri-robot-line",
        "content": [
            {"type": "typography", "variant": "p", "content": "Gestión de Prompts personalizados para este cliente (Próximamente)."}
        ]
    })

    # 4. Branding (Superuser ONLY)
    if current_user.is_superuser:
        tabs.append({
            "id": "branding",
            "label": "Branding",
            "icon": "ri-palette-line",
            "content": [
                {
                     "type": "grid-visual",
                     "label": "Configuración de Marcas (Branding)",
                     "properties": {
                         "title": "Proyectos y Marcas",
                         "id": "branding_grid",
                         "primary_key": "project",
                         "data_url": f"/brand-config/{client.id}/list",
                         "enableFilters": True,
                         "filterConfig": {
                             "searchPlaceholder": "Buscar por proyecto, color o fuente...",
                             "searchFields": ["project", "primary_color", "font_heading_name", "font_body_name"]
                         },
                         "columns": [
                             {"id": "project", "label": "Proyecto"},
                             {"id": "primary_color", "label": "Primario", "type": "color"},
                             {"id": "secondary_color", "label": "Secundario", "type": "color"},
                             {"id": "surface_color", "label": "Superficie", "type": "color"}
                         ],
                         "actions": [
                             {
                                 "type": "button",
                                 "label": "Editar",
                                 "icon": "ri-edit-line",
                                 "action": "modal-form",
                                 "action_url": f"/brand-config/{client.id}/item?project={{project}}",
                                 "modal_title": "Editar Configuración de Marca",
                                 "schema": BRAND_FORM_SCHEMA_B64
                             },
                             {
                                 "type": "button",
                                 "icon": "ri-delete-bin-line",
                                 "label": "Eliminar",
                                 "color": "danger",
                                 "action": "delete",
                                 "action_url": f"/brand-config/{client.id}?project={{project}}",
                                 "confirm_message": "¿Estás seguro de que deseas eliminar esta configuración de marca?"
                             }
                         ],
                         "header_actions": [
                             {
                                 "type": "button",
                                 "label": "Nueva Configuración",
                                 "icon": "ri-add-line",
                                 "color": "success",
                                 "action": "modal-form",
                                 "action_url": f"/brand-config/{client.id}",
                                 "modal_title": "Nueva Configuración de Marca",
                                 "schema": BRAND_FORM_SCHEMA_B64
                             }
                         ]
                     }
                }
            ]
        })

    return {
        "layout": "dashboard-standard",
        "components": [
            {
                "type": "typography",
                "variant": "h4",
                "content": f"Cliente: {client.name} <span class='badge bg-success ms-2'>Active</span>",
                "class": "mb-4"
            },
            {
                "type": "tabs",
                "items": tabs
            }
        ],
        "permissions_required": ["clients.view"]
    }

# --- BRAND CONFIG PROXY ---

BRAND_SERVICE_URL = "http://192.168.0.40:8000"

# Schema definition for Modal (Base64) - Moved here for reuse
import base64
import json

BRAND_FORM_FIELDS = [
    {"name": "project", "label": "Nombre del Proyecto", "type": "text", "required": True, "value": "default", "readonly": True},
    {
        "type": "group",
        "label": "Colores del Tema",
        "layout": "horizontal",
        "fields": [
            {"name": "primary_color", "label": "Primario", "type": "color", "required": True, "value": "#000000"},
            {"name": "secondary_color", "label": "Secundario", "type": "color", "required": False, "value": "#333333"},
            {"name": "surface_color", "label": "Superficie", "type": "color", "required": False, "value": "#F5F5F5"}
        ]
    },
    {"name": "font_heading_name", "label": "Fuente Títulos", "type": "select", "options": [{"label": "Inter", "value": "Inter"}, {"label": "Roboto", "value": "Roboto"}, {"label": "Open Sans", "value": "Open Sans"}, {"label": "Montserrat", "value": "Montserrat"}, {"label": "Playfair Display", "value": "Playfair Display"}], "required": True, "value": "Inter"},
    {"name": "font_body_name", "label": "Fuente Cuerpo", "type": "select", "options": [{"label": "Inter", "value": "Inter"}, {"label": "Roboto", "value": "Roboto"}, {"label": "Open Sans", "value": "Open Sans"}, {"label": "Lato", "value": "Lato"}], "required": True, "value": "Inter"},
    {"name": "border_radius", "label": "Radio de Borde", "type": "select", "options": [{"label": "Pequeño (2px)", "value": "2px"}, {"label": "Medio (4px)", "value": "4px"}, {"label": "Redondo (8px)", "value": "8px"}, {"label": "Full (99px)", "value": "99px"}], "required": False, "value": "4px"},
    {"name": "box_shadow_style", "label": "Sombra (Estilo)", "type": "select", "options": [{"label": "Ninguna", "value": "none"}, {"label": "Sutil", "value": "0 4px 6px -1px rgb(0 0 0 / 0.1)"}, {"label": "Elevada", "value": "0 10px 15px -3px rgb(0 0 0 / 0.1)"}], "required": False, "value": "none"},
    {"name": "logo_header", "label": "Logo Header", "type": "file", "accept": "image/*", "required": False},
    {"name": "logo_square", "label": "Logo Cuadrado", "type": "file", "accept": "image/*", "required": False},
    {"name": "banner_main", "label": "Banner Principal", "type": "file", "accept": "image/*", "required": False},
    {"name": "banner_promo", "label": "Banner Promocional", "type": "file", "accept": "image/*", "required": False}
]
BRAND_FORM_SCHEMA_B64 = base64.b64encode(json.dumps(BRAND_FORM_FIELDS).encode()).decode()


@router.get("/brand-config/{client_id}/list")
async def list_brand_configs(client_id: UUID, current_user: AuthUser = Depends(current_active_user)):
    """
    Returns a LIST of all brand configs for this client.
    Uses the all_projects parameter to get all configurations.
    """
    async with httpx.AsyncClient() as client:
        try:
            r = await client.get(f"{BRAND_SERVICE_URL}/brand-config/{client_id}/list")
            if r.status_code == 200:
                raw_data = r.json()
                
                # Desempaquetado inteligente (Compliance con esquemas RAG/List)
                if isinstance(raw_data, dict):
                    if "results" in raw_data: return raw_data["results"]
                    if "data" in raw_data: return raw_data["data"]
                    if "documents" in raw_data: return raw_data["documents"]
                    # Si es un objeto único con los datos directamente
                    if "project" in raw_data: return [raw_data]
                    return []
                
                if isinstance(raw_data, list):
                    return raw_data
                return []
            elif r.status_code == 404:
                return []
            else:
                print(f"Brand Service List Error: {r.status_code}")
                return []
        except Exception as e:
            print(f"Brand Service Connection Fail: {e}")
            return []

@router.get("/brand-config/{client_id}/item")
async def get_brand_config_item(
    client_id: UUID,
    project: str = Query("default"),
    current_user: AuthUser = Depends(current_active_user)
):
    """Get a specific brand configuration by project name"""
    async with httpx.AsyncClient() as client:
        try:
            url = f"{BRAND_SERVICE_URL}/brand-config/{client_id}?project={project}"
            r = await client.get(url)
            if r.status_code == 200:
                data = r.json()
                # Map _path fields to match form field names
                if 'logo_header_path' in data:
                    data['logo_header'] = data['logo_header_path']
                if 'logo_square_path' in data:
                    data['logo_square'] = data['logo_square_path']
                if 'banner_main_path' in data:
                    data['banner_main'] = data['banner_main_path']
                if 'banner_promo_path' in data:
                    data['banner_promo'] = data['banner_promo_path']
                return data
            elif r.status_code == 404:
                raise HTTPException(status_code=404, detail="Brand configuration not found")
            else:
                raise HTTPException(status_code=r.status_code, detail=f"Brand Service Error: {r.text}")
        except httpx.HTTPError as e:
            raise HTTPException(status_code=500, detail=f"Failed to fetch brand config: {str(e)}")



@router.delete("/brand-config/{client_id}")
async def delete_brand_config(
    client_id: UUID,
    project: str = Query("default"),
    current_user: AuthUser = Depends(current_active_user)
):
    """Delete a specific brand configuration"""
    if not current_user.is_superuser:
        raise HTTPException(status_code=403, detail="Only admins can delete branding")
    
    async with httpx.AsyncClient() as client:
        try:
            url = f"{BRAND_SERVICE_URL}/brand-config/{client_id}?project={project}"
            r = await client.delete(url)
            if r.status_code in [200, 204]:
                return {"status": "success", "message": f"Brand configuration '{project}' deleted"}
            elif r.status_code == 404:
                raise HTTPException(status_code=404, detail="Brand configuration not found")
            else:
                raise HTTPException(status_code=r.status_code, detail=f"Brand Service Error: {r.text}")
        except httpx.HTTPError as e:
            raise HTTPException(status_code=500, detail=f"Failed to delete brand config: {str(e)}")

@router.post("/brand-config/{client_id}")
@router.put("/brand-config/{client_id}/item")  # Support PUT for edit action
async def proxy_update_brand_config(
    client_id: UUID,
    project: str = Form("default"),
    primary_color: str = Form(...),
    secondary_color: str = Form(None),
    surface_color: str = Form(None),
    font_heading_name: str = Form(...),
    font_body_name: str = Form(...),
    border_radius: str = Form(...),
    box_shadow_style: str = Form(None),
    logo_header: Optional[UploadFile] = File(None),
    logo_square: Optional[UploadFile] = File(None),
    banner_main: Optional[UploadFile] = File(None),
    banner_promo: Optional[UploadFile] = File(None),
    current_user: AuthUser = Depends(current_active_user)
):
    if not current_user.is_superuser:
        raise HTTPException(status_code=403, detail="Only admins can configure branding")

    async with httpx.AsyncClient() as client:
        # Map Font Names to Google Fonts URLs
        FONT_URL_MAP = {
            "Inter": "https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap",
            "Roboto": "https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap",
            "Open Sans": "https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap",
            "Montserrat": "https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap",
            "Playfair Display": "https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap",
            "Lato": "https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap"
        }

        # 1. Update Config (PUT JSON)
        raw_payload = {
            "primary_color": primary_color,
            "secondary_color": secondary_color,
            "surface_color": surface_color,
            "font_heading_name": font_heading_name,
            "font_heading_url": FONT_URL_MAP.get(font_heading_name, ""),
            "font_body_name": font_body_name,
            "font_body_url": FONT_URL_MAP.get(font_body_name, ""),
            "border_radius": border_radius,
            "box_shadow_style": box_shadow_style
        }
        # Filter out empty strings or None to avoid validation errors (e.g. empty secondary_color is not a valid hex)
        config_payload = {k: v for k, v in raw_payload.items() if v}
        
        try:
            url = f"{BRAND_SERVICE_URL}/brand-config/{client_id}?project={project}"
            r_config = await client.put(url, json=config_payload)
            r_config.raise_for_status()
        except httpx.HTTPStatusError as e:
             print(f"UPSTREAM BRAND SERVICE ERROR: {e.response.text}") # LOG IT
             raise HTTPException(status_code=e.response.status_code, detail=f"Brand Service Error: {e.response.text}")
        except Exception as e:
             print(f"UPSTREAM CONNECTION FATAL: {str(e)}")
             raise HTTPException(status_code=500, detail=f"Failed to connect to Brand Service: {str(e)}")

        # 2. Upload Assets
        assets_to_upload = []
        if logo_header and logo_header.filename: assets_to_upload.append(('logo_header', logo_header))
        if logo_square and logo_square.filename: assets_to_upload.append(('logo_square', logo_square))
        if banner_main and banner_main.filename: assets_to_upload.append(('banner_main', banner_main))
        if banner_promo and banner_promo.filename: assets_to_upload.append(('banner_promo', banner_promo))

        for asset_type, file_obj in assets_to_upload:
            await file_obj.seek(0)
            # Ensure proper casting 
            files = {'file': (file_obj.filename, file_obj.file, file_obj.content_type)}
            try:
                url_asset = f"{BRAND_SERVICE_URL}/brand-config/{client_id}/assets/{asset_type}?project={project}"
                r_asset = await client.post(url_asset, files=files)
                r_asset.raise_for_status()
            except Exception as e:
                print(f"Asset upload failed: {e}")
                # Log but continue

    return {"status": "success", "message": f"Brand configuration for '{project}' updated"}
