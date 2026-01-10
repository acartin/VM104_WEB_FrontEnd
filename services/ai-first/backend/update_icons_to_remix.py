import asyncio
from sqlalchemy import text
from app.dal.database import engine

async def update_icons():
    async with engine.begin() as conn:
        print("Updating Channel Category Icons to Remix...")
        
        updates = [
            ("WhatsApp", "ri-whatsapp-line"),
            ("Email", "ri-mail-line"),
            ("Phone", "ri-phone-line"),
            ("Telegram", "ri-telegram-line"),
            ("LinkedIn", "ri-linkedin-line"),
            ("Chat", "ri-chat-1-line"),
            ("Web", "ri-global-line"),
            ("Redes Sociales", "ri-share-line")
        ]
        
        for name, icon in updates:
            print(f"Updating {name} -> {icon}")
            await conn.execute(text("UPDATE lead_channel_categories SET icon = :icon WHERE name = :name"), {"icon": icon, "name": name})

        print("Icon Update Complete.")

    await engine.dispose()

if __name__ == "__main__":
    asyncio.run(update_icons())
