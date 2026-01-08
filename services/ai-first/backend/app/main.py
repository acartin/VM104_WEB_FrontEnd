from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
import os

# Import Feature Modules
from app.modules.dashboard.router import router as dashboard_router
from app.modules.clients.router import router as clients_router
from app.modules.countries.router import router as countries_router
from app.modules.prompts.router import router as prompts_router
from app.modules.auth.router import router as auth_router

app = FastAPI(title="Web IAFirst Operational API")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Permite cualquier origen (ajustar en prod)
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/health")
async def health_check():
    return {"status": "operational", "version": "1.0"}

# Include Feature Routers
app.include_router(dashboard_router, tags=["Dashboard"])
app.include_router(clients_router, tags=["Clients"])
app.include_router(countries_router, tags=["Countries (System)"])
app.include_router(prompts_router, tags=["AI Prompts"])
app.include_router(auth_router) # Tags are defined inside the router

# Mount Frontend (Must be last to avoid overriding API routes)
FRONTEND_PATH = os.path.abspath(os.path.join(os.path.dirname(__file__), "../../frontend"))

if os.path.exists(FRONTEND_PATH):
    app.mount("/", StaticFiles(directory=FRONTEND_PATH, html=True), name="frontend")
