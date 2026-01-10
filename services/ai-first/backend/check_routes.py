from fastapi.testclient import TestClient
from app.main import app
import json

client = TestClient(app)

# Note: We need a valid token to skip 401, but we can check for 401 vs 404.
# A 401 means the route EXISTS but needs auth.
# A 404 means the route is NOT REGISTERED.

routes_to_check = [
    "/dashboard/client-admin",
    "/dashboard/client-user",
    "/leads",
    "/leads/me",
    "/campaigns"
]

print("--- ROUTE VERIFICATION ---")
for route in routes_to_check:
    response = client.get(route)
    print(f"Route: {route} | Status: {response.status_code}")
    if response.status_code == 404:
        print(f"  ERROR: {route} is NOT registered!")
    elif response.status_code == 422:
        print(f"  WARNING: {route} returned 422 (Validation Error - likely missing query params or user depends)")

# List all registered routes in app
print("\n--- ALL REGISTERED ROUTES ---")
for route in app.routes:
    if hasattr(route, "path"):
        print(f"[{','.join(route.methods)}] {route.path}")
