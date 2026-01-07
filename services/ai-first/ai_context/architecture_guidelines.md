# AI First - Architectural Guidelines

This document serves as the **Single Source of Truth** for architectural decisions and code standards.

## 1. Backend Architecture: Feature-Based Modules
We reject Layer-Based architecture (Services/Controllers/Routes separated) in favor of **Feature-Based Architecture**.

**Rule:** Every domain feature must reside in its own self-contained directory within `backend/app/modules/`.

### Structure
```
backend/app/modules/
└── [feature_name]/       <-- e.g., 'clients', 'dashboard'
    ├── router.py         <-- Defines Endpoints. Returns UI Schema.
    ├── service.py        <-- (Optional) Business Logic & Data Access.
    └── schemas.py        <-- (Optional) Pydantic Models specific to this feature.
```

### The Role of `main.py`
*   **Strictly Minimal**: `main.py` MUST only initialize the FastAPI app, configure Middleware (CORS), and `include_router` from modules.
*   **No Logic**: Never write business logic or endpoints directly in `main.py`.

## 2. Server-Driven UI (SDUI)
The Frontend is a "Dumb Renderer". The Backend is the "Brain".

**Rule:** The Frontend never decides *what* to render based on URL. It asks the Backend for the layout.

### Flow
1.  **Frontend Navigation**: User clicks a link (e.g., `/clients`).
2.  **Interceptor**: `main.js` intercepts the click.
3.  **Fetch**: Calls Backend API with the same path (e.g., `GET API/clients`).
4.  **Response**: Backend returns a JSON describing components (Grid, Cards, Typography).
5.  **Render**: `main.js` dynamically builds the HTML based on the JSON.

**Implication**: To create a new page, you create a Backend Endpoint. You do NOT touch Frontend Routing code.

## 3. Frontend Components & Velzon Theme
We use the **Official Velzon** assets and structure.

**Visual Consistency Rules:**
1.  **Native Assets**: Use `app.min.css`, `bootstrap.min.css`, and `icons.min.css` from the theme. avoid custom CSS unless critical.
2.  **DOM Structure**: Components (`AppShell.js`, `Sidebar.js`) MUST output HTML that matches the official Velzon `index.html` structure exactly.
3.  **Component Directory**:
    *   `components/layouts/`: Structural (Sidebar, Navbar).
    *   `components/forms/`: Inputs, Buttons.
    *   `components/grids/`: Tables, Lists.
    *   `components/cards/`: Metric Cards, Profile Cards.

## 4. Operational Workflows
*   **Adding a Feature**:
    1.  Create `backend/app/modules/[new_feature]/router.py`.
    2.  Define the UI Structure in the endpoint.
    3.  Register the router in `backend/app/main.py`.
    4.  Add the link to the Sidebar menu (in `dashboard/router.py`).

## 5. Security, Authentication & RBAC
In a Feature-Based Architecture, security should be applied at the **Router Level** or **Global/Include Level**, not inside every endpoint manually.

### Pattern A: Module-Level Security (Preferred for Auth)
Apply dependencies when including the router in `main.py`. This ensures *all* endpoints in that module are protected.

```python
# main.py
from app.auth.dependencies import get_current_user, require_role

app.include_router(
    clients_router, 
    prefix="/clients", 
    dependencies=[Depends(get_current_user), Depends(require_role("admin"))]
)
```

### Pattern B: Granular Security (Preferred for specific endpoints)
Apply dependencies directly in the module's `router.py` for mixed-access modules.

```python
# modules/dashboard/router.py
@router.get("/sensitive-data", dependencies=[Depends(require_role("superadmin"))])
async def get_sensitive_data():
    ...
```

### Middleware
Global middleware (CORS, Logging) belongs in `main.py`. Feature-specific middleware should be implemented as a Dependency.

## 6. Authentication Provider & Database Schema
**Strategic Decision**: Greenfield Implementation.

**Validator & Manager**: **`FastAPI Users`** (Library).

**Implementation Plan:**
1.  **Install**: `fastapi-users[sqlalchemy]`.
2.  **Schema**: Use the library's default highly-optimized schema (`user`, `oauth_account`).
3.  **Roles**: Implement Role-Based Access Control (RBAC) using the library's mixins.
4.  **No Legacy**: We do NOT connect to `lead_users`. We create new users in the new system.

**Why?**
*   **Speed**: Deploy standard, battle-tested auth in minutes.
*   **Security**: Zero compromise from trying to fit into legacy structures.
*   **Maintenance**: 100% Pythonic standard.
