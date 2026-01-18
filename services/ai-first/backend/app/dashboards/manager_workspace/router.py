from fastapi import APIRouter, Depends, Request
from .schema import get_manager_workspace_schema, ManagerDashboardSchema

router = APIRouter()

@router.get("/manager", response_model=ManagerDashboardSchema)
async def get_manager_dashboard(request: Request):
    user_id = "current_manager"
    return get_manager_workspace_schema(user_id)
