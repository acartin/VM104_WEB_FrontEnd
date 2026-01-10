import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def migrate_category_data():
    async with engine.begin() as conn:
        print("Migrating Icons and Types to Categories...")
        
        # We need to ensure we have categories for specific channels
        # Currently lead_communication_channels has: WhatsApp, Email, Phone etc.
        # lead_channel_categories has: Chat, Web, Email etc.
        
        # Strategy: Insert missing specific channels as categories
        
        res = await conn.execute(text("SELECT name, icon FROM lead_communication_channels"))
        legacy_channels = res.fetchall()
        
        for name, icon in legacy_channels:
             # Check if category exists
             exists = await conn.execute(text("SELECT id FROM lead_channel_categories WHERE name = :name"), {"name": name})
             cat_id = exists.scalar()
             
             if cat_id:
                 # Update icon
                 print(f"Updating icon for existing category: {name}")
                 await conn.execute(text("UPDATE lead_channel_categories SET icon = :icon WHERE id = :id"), {"icon": icon, "id": cat_id})
             else:
                 # Insert new category
                 print(f"Creating new category from legacy channel: {name}")
                 await conn.execute(text("INSERT INTO lead_channel_categories (name, icon) VALUES (:name, :icon)"), {"name": name, "icon": icon})

        print("Category Data Migration Complete.")

    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(migrate_category_data())
