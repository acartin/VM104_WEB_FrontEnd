import asyncio
from uuid import UUID
from app.modules.leads.router import get_my_leads
from app.modules.auth.models import User
import json

async def verify_leads_me():
    # User ID: e18b47ae-4067-420d-861b-7f732409ade4
    user = User(id=UUID('e18b47ae-4067-420d-861b-7f732409ade4'), email='cocacola-admin@cocacola.com')
    
    response = await get_my_leads(user=user)
    
    print("\n--- LEADS/ME ENDPOINT VERIFICATION ---")
    print(f"Layout: {response['layout']}")
    
    # Extract components
    components = response['components']
    grid_component = None
    for comp in components:
        if comp['type'] == 'card':
            for sub in comp['components']:
                if sub['type'] == 'grid-visual':
                    grid_component = sub
                    break
    
    if grid_component:
        print(f"Grid Title: {grid_component['title']}")
        print(f"Total Rows: {len(grid_component['rows'])}")
        for row in grid_component['rows']:
            print(f"- {row['name']} | Status: {row['status']['label']} | Score: {row['score']}")
    else:
        print("ERROR: Grid component not found in response.")

if __name__ == "__main__":
    asyncio.run(verify_leads_me())
