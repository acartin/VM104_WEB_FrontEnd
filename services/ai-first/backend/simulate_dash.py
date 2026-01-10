import asyncio
from uuid import UUID
from app.modules.contacts.service import service as contact_service
from app.dashboards.client_user_dash.schema import get_client_user_schema
import json

async def simulate_dashboard():
    # User ID: e18b47ae-4067-420d-861b-7f732409ade4 (cocacola-admin)
    user_id = UUID('e18b47ae-4067-420d-861b-7f732409ade4')
    
    # 1. Fetch Data
    assigned_leads = await contact_service.get_my_leads(user_id, limit=20)
    appointments = await contact_service.get_my_appointments(user_id)
    
    stats = {
        "total_leads": len(assigned_leads),
        "actions_today": len(appointments),
    }
    
    top_leads_raw = sorted(assigned_leads, key=lambda x: x.get('score_total', 0) or 0, reverse=True)[:3]
    
    # This matches the mapper logic in router.py
    def map_lead_to_member(l):
        has_appt = any(a['lead_name'] == l['full_name'] for a in appointments)
        status_label = "URGENTE: Cita pendiente" if has_appt else f"Score: {l.get('score_total', 0)}"
        
        return {
            "id": str(l['id']),
            "name": l['full_name'] or "Lead Sin Nombre",
            "position": f"{status_label} | Status: {l.get('status', 'New')}",
            "is_active": True
        }

    my_leads_mapped = [map_lead_to_member(l) for l in assigned_leads]
    top_leads_mapped = [map_lead_to_member(l) for l in top_leads_raw]

    schema = get_client_user_schema(
        user_name="Coca Cola Admin",
        my_leads=my_leads_mapped,
        stats=stats,
        top_leads=top_leads_mapped
    )
    
    print("\n--- DASHBOARD DATA SUMMARY ---")
    print(f"Total Leads: {stats['total_leads']}")
    print(f"Actions Today (Appointments): {stats['actions_today']}")
    print(f"Top Leads Count: {len(top_leads_mapped)}")
    
    print("\n--- TOP LEADS WIDGET ---")
    for l in top_leads_mapped: # Iterate over top_leads_mapped as it now contains channels
        print(f"- {l['name']}: {l['position']}")
        print(f"  Channels: {l.get('channels', [])}")

if __name__ == "__main__":
    asyncio.run(simulate_dashboard())
