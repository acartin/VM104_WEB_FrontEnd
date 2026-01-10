from fastapi.testclient import TestClient
from app.main import app
from app.modules.auth.config import current_active_user
from app.modules.auth.models import User
from uuid import UUID
import json

# Mock current_active_user to return our test user
async def mock_user():
    return User(
        id=UUID('e18b47ae-4067-420d-861b-7f732409ade4'),
        email='cocacola-admin@cocacola.com',
        is_active=True,
        tenants=[] # Not strictly needed for leads/me query but good to have
    )

app.dependency_overrides[current_active_user] = mock_user

client = TestClient(app)

print("--- TESTING /leads/me ---")
response = client.get("/leads/me")
print(f"Status Code: {response.status_code}")
if response.status_code == 200:
    data = response.json()
    # Print components and grid rows
    for comp in data.get('components', []):
        if comp.get('type') == 'card':
            for sub in comp.get('components', []):
                if sub.get('type') == 'grid-visual':
                    rows = sub.get('rows', [])
                    print(f"Grid 'rows' count: {len(rows)}")
                    if rows:
                        print("First row sample:", json.dumps(rows[0], indent=2))
    
    if not any(c.get('type') == 'card' for c in data.get('components', [])):
         print("No card component found in response.")
else:
    print("Response matches error or unexpected state.")

app.dependency_overrides = {}
