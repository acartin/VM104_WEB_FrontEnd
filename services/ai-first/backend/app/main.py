from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

# Import Feature Modules
from app.modules.dashboard.router import router as dashboard_router
from app.modules.clients.router import router as clients_router

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
