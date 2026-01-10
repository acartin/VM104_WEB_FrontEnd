from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
import os
from app.config.settings import settings

# Import Feature Modules
from app.dashboards.base_dash.router import router as base_dash_router
from app.dashboards.client_admin_dash.router import router as client_admin_router
from app.modules.clients.router import router as clients_router
from app.modules.countries.router import router as countries_router
from app.modules.prompts.router import router as prompts_router
from app.modules.auth.router import router as auth_router
from app.modules.users.router import router as users_router
from app.modules.roles.router import router as roles_router
from app.modules.contacts.router import router as contacts_router

app = FastAPI(title="Web IAFirst Operational API")

# Parse allowed origins (comma separated or single *)
origins = settings.allowed_origins.split(",") if "," in settings.allowed_origins else [settings.allowed_origins]

# Standard CORS configuration for development/production
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- Global Exception Handlers ---
from fastapi.exceptions import RequestValidationError, ResponseValidationError
from fastapi.responses import JSONResponse

@app.exception_handler(RequestValidationError)
async def validation_exception_handler(request, exc):
    return JSONResponse(
        status_code=422,
        content={"detail": "Request Validation Error", "errors": str(exc)},
    )

@app.exception_handler(ResponseValidationError)
async def response_validation_exception_handler(request, exc):
    # This captures the "contract violation" we just suffered
    return JSONResponse(
        status_code=500, 
        content={"detail": "Response Contract Violation", "errors": str(exc)},
    )

@app.get("/health")
async def health_check():
    return {"status": "operational", "version": "1.0"}

# Include Feature Routers
app.include_router(base_dash_router, tags=["Dashboard (Base)"]) # Root prefix for app-init
app.include_router(client_admin_router, prefix="/dashboard", tags=["Dashboard (Client Admin)"])
app.include_router(clients_router, tags=["Clients"])
app.include_router(countries_router, tags=["Countries (System)"])
app.include_router(prompts_router, tags=["AI Prompts"])
app.include_router(auth_router) # Tags are defined inside the router
app.include_router(users_router)
app.include_router(roles_router)
app.include_router(contacts_router, tags=["Contacts"])

# Mount Frontend (Must be last to avoid overriding API routes)
FRONTEND_PATH = os.path.abspath(os.path.join(os.path.dirname(__file__), "../../frontend"))

if os.path.exists(FRONTEND_PATH):
    app.mount("/", StaticFiles(directory=FRONTEND_PATH, html=True), name="frontend")
