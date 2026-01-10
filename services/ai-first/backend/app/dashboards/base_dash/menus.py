from typing import List, Dict

# 1. Definimos los bloques (LEGOs)
MENU_DASHBOARD = {"id": "dash", "label": "Dashboard", "icon": "ri-dashboard-2-line", "link": "/dashboard/base"}
MENU_CLIENTS   = {"id": "clients", "label": "Admin Clientes", "icon": "ri-building-line", "link": "/clients"}
MENU_PROMPTS   = {"id": "prompts", "label": "AI Prompts", "icon": "ri-robot-line", "link": "/prompts"}
MENU_COUNTRIES   = {"id": "items", "label": "Paises", "icon": "ri-map-pin-line", "link": "/countries"}
MENU_ADMIN_CLIENTS = {"id": "admin-clients", "label": "Admin", "icon": "ri-admin-line", "link": "/dashboard/client-admin"} # Updated

# Submenú Sistema
MENU_SYSTEM = {
    "id": "sys", 
    "label": "Sistema", 
    "icon": "ri-settings-line", 
    "subItems": [
        {"id": "users", "label": "Usuarios", "link": "/system/users"},
        {"id": "roles", "label": "Roles", "link": "/system/roles"},
        {"id": "countries", "label": "Países (Global)", "link": "/countries"}
    ]
}

# Fallback por seguridad (si el rol no existe o es null)
DEFAULT_MENU = [MENU_DASHBOARD]

# --- NUEVOS ROLES DE PRUEBA ---
ROLE_MENUS = {
    # 1. Super Admin (TÚ)
    "admin": [
        MENU_DASHBOARD,
        MENU_CLIENTS,    # Gestión de Inquilinos
        MENU_PROMPTS,
        MENU_SYSTEM
    ],

    # 2. System User (Datasync)
    # Rol técnico interno, ve estado del sistema pero no clientes
    "system-user": [
        MENU_DASHBOARD,
        {"id": "sys-status", "label": "Estado Servidores", "icon": "ri-server-line", "link": "/status"},
        {"id": "logs", "label": "Audit Logs", "icon": "ri-file-list-3-line", "link": "/logs"},
        MENU_SYSTEM
    ],
    
    # 3. Client Admin (Coca Cola Admin)
    # Ve su negocio y gestión básica
    "client-admin": [
        MENU_ADMIN_CLIENTS, # Points to /dashboard/client-admin
        {"id": "leads", "label": "Gestión de Leads", "icon": "ri-team-line", "link": "/leads"},
        {"id": "campaigns", "label": "Campañas", "icon": "ri-megaphone-line", "link": "/campaigns"}
    ],
    
    # 4. Client User (Coca Cola User)
    # Operativo puro
    "client-user": [
        MENU_DASHBOARD,
        MENU_ADMIN_CLIENTS, # Points to /clients -> Dashboard (Filtered)
        {"id": "my-leads", "label": "Mis Leads", "icon": "ri-user-star-line", "link": "/leads/me"},
        {"id": "tasks", "label": "Mis Tareas", "icon": "ri-task-line", "link": "/tasks"}
    ]
}

def get_menu_for_role(role_slug: str) -> List[Dict]:
    """Retorna la lista de menús permitidos para un rol."""
    return ROLE_MENUS.get(role_slug, DEFAULT_MENU)
