import asyncio
from sqlalchemy import text
from app.dal.database import engine
from app.dal.database import engine
import uuid

async def migrate_legacy_channels():
    print("Starting Legacy Channel Migration...")
    
    async with engine.connect() as conn:
        # 1. Fetch all legacy channels
        # We need client_id, internal_name to group them
        result = await conn.execute(text("SELECT * FROM lead_client_channels"))
        legacy_rows = result.fetchall()
        
        print(f"Found {len(legacy_rows)} legacy channel records.")

        # 2. Group by (client_id, internal_name) -> "Person"
        # Map: (client_id, internal_name) -> List[row]
        grouped_contacts = {}
        
        for row in legacy_rows:
            # internal_name might be None, handle gracefully
            name_key = row.internal_name.strip() if row.internal_name else "Unknown Contact"
            key = (row.client_id, name_key)
            
            if key not in grouped_contacts:
                grouped_contacts[key] = []
            grouped_contacts[key].append(row)
            
        print(f"Indentified {len(grouped_contacts)} unique contacts to create.")

    # 3. Insert into new tables
    # using engine.begin() for transaction
    async with engine.begin() as conn:
        contacts_created = 0
        channels_created = 0
        
        for (client_id, full_name), rows in grouped_contacts.items():
            # Create Contact
            new_contact_id = uuid.uuid4()
            
            # Simple heuristic for name splitting
            parts = full_name.split(" ", 1)
            first_name = parts[0]
            last_name = parts[1] if len(parts) > 1 else ""
            
            await conn.execute(text("""
                INSERT INTO lead_contacts (id, client_id, first_name, last_name, created_at, updated_at)
                VALUES (:id, :cid, :fname, :lname, NOW(), NOW())
            """), {
                "id": new_contact_id, 
                "cid": client_id,
                "fname": first_name,
                "lname": last_name
            })
            contacts_created += 1
            
            # Create Channels for this contact
            for row in rows:
                # Map old channel_id (int) to type string if possible, or just use ID text
                # We assume a simplistic mapping or query query it.
                # simpler: just use row.channel_id as type for now or fetch map.
                # Let's fetch the channel name for better type
                
                # OPTIMIZATION: We do a subquery or just insert 'legacy_type_id'
                # Resolve Category ID from Legacy Channel ID
                # 1. Get Name from lead_communication_channels
                res = await conn.execute(text("SELECT name FROM lead_communication_channels WHERE id = :cid"), {"cid": row.channel_id})
                channel_name = res.scalar()
                
                # 2. Get ID from lead_channel_categories
                cat_id = None
                if channel_name:
                    res = await conn.execute(text("SELECT id FROM lead_channel_categories WHERE name = :name"), {"name": channel_name})
                    cat_id = res.scalar()
                
                if cat_id:
                    await conn.execute(text("""
                        INSERT INTO lead_contact_channels (id, contact_id, category_id, value, is_primary, created_at, updated_at)
                        VALUES (:id, :contact_id, :category_id, :value, :primary, NOW(), NOW())
                    """), {
                        "id": uuid.uuid4(),
                        "contact_id": new_contact_id,
                        "category_id": cat_id, 
                        "value": row.value,
                        "primary": False
                    })
                    channels_created += 1
                else:
                    print(f"Warning: Could not map legacy channel {row.channel_id} ({channel_name}) to a category.")

        print(f"MIGRATION COMPLETE.")
        print(f"Contacts Created: {contacts_created}")
        print(f"Channels Created: {channels_created}")

if __name__ == "__main__":
    asyncio.run(migrate_legacy_channels())
