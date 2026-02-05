from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.schemas.ui import SDUIResponse, ChatMessage, PropertyCard

app = FastAPI(title="Realtor Chat Polymorphic Bridge")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/health")
async def health_check():
    return {"status": "operational", "service": "realtor-chat-bridge"}

from app.core.inference_bridge import InferenceClient
from app.transformer.core import SDUITransformer
from app.session.manager import SessionManager

inference_client = InferenceClient()
transformer = SDUITransformer()
session_manager = SessionManager()

@app.post("/chat", response_model=SDUIResponse)
async def chat_interaction(query: dict):
    # 0. Identificar sesión (Frontend debe enviar 'client_id' o generamos uno)
    client_id = query.get("client_id", "guest-user")
    
    # 1. Recuperar contexto de Redis
    session_data = await session_manager.get_session(client_id)
    
    # Mezclar contexto entrante (si el frontend envía datos frescos) con el guardado
    # Importante: El frontend es la fuente de verdad de la INTENCIÓN, Redis del HISTORIAL.
    session_context = {
        "client_id": client_id,
        "conversation_id": session_data.get("conversation_id"),
        "lead_id": session_data.get("lead_id"),
        # Propagar UTMs si vienen en el query o ya estaban guardados
        "utm_source": query.get("utm_source", session_data.get("utm_source")),
    }

    # 0.5 Manejo de inicialización silenciosa (solo para branding)
    if query.get("is_init") or not query.get("text"):
        return transformer.transform({"answer": "", "sources": []}, "init", client_id)

    try:
        # 2. Llamar al Cerebro Real
        ai_response = await inference_client.chat(user_query=query.get('text', ''), session=session_context)
        
        # 2.5 Actualizar Memoria (Guardamos el conversation_id nuevo si cambió)
        new_conversation_id = ai_response.get("conversation_id")
        if new_conversation_id:
            await session_manager.update_session(client_id, {
                "conversation_id": new_conversation_id,
                "last_interaction": "now" # Timestamp placeholder
            })

        # 3. Transformación Polimórfica (La Magia)
        sdui_response = transformer.transform(ai_response, str(new_conversation_id), client_id)
        
        return sdui_response

    except Exception as e:
        return SDUIResponse(
            session_id="error",
            components=[
                ChatMessage(text=f"Error conectando con el cerebro: {str(e)}", sender="bot")
            ]
        )

@app.get("/")
async def root():
    return {"message": "Realtor Chat SDUI Bridge is running"}
