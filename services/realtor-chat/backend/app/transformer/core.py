import logging
from typing import Dict, Any, List, Union
from app.schemas.ui import (
    SDUIResponse, ChatMessage, PropertyCard, PropertyGrid, 
    ActionMenu, MortgageCalculator, BaseComponent, BrandingConfig
)
from app.core.database import db_manager

# Logger config
logger = logging.getLogger("transformer")

class SDUITransformer:
    """
    El 'Transformer' es el corazón polimórfico del Bridge.
    Toma la respuesta cruda de la IA (texto + sources) y decide qué 
    componentes visuales (Cards, Grids, Mapas) se deben renderizar.
    """

    def transform(self, ai_response: Dict[str, Any], session_id: str, client_id: str = "default") -> SDUIResponse:
        """
        Convierte el payload del Inference Core en una respuesta SDUI estructurada.
        """
        components: List[BaseComponent] = []
        
        # 1. Extraer el Texto Base (Siempre hay un mensaje de chat)
        ai_text = ai_response.get("answer", "")
        # Fallback si viene vacío
        if not ai_text:
            ai_text = "Lo siento, no pude generar una respuesta."
            
        components.append(ChatMessage(text=ai_text, sender="bot"))

        # 2. Procesar Fuentes (Sources) - Aquí ocurre la magia de "Grounding"
        # Si la IA cita propiedades, las convertimos en Cards visuales.
        sources = ai_response.get("sources", [])
        property_cards = self._extract_properties_from_sources(sources)

        if property_cards:
            if len(property_cards) == 1:
                # Si es una sola, la mostramos directa
                components.append(property_cards[0])
                # Y quizás una calculadora para esa propiedad
                components.append(MortgageCalculator(property_price=property_cards[0].price))
            else:
                # Si son varias, usamos un Grid/Carrusel
                components.append(PropertyGrid(
                    title="Propiedades Relacionadas",
                    properties=property_cards
                ))

        # 3. Detectar Intenciones de Acción (Heurística simple por ahora)
        if "cita" in ai_text.lower() or "visita" in ai_text.lower():
            components.append(ActionMenu(
                options=[
                    {"label": "📅 Agendar Visita", "payload": "SCHEDULE_VISIT"},
                    {"label": "📞 Hablar con Asesor", "payload": "CALL_AGENT"}
                ]
            ))

        # 4. Configuración de Branding (Multi-tenant Real)
        branding = self._get_branding_for_client(client_id)

        return SDUIResponse(
            session_id=session_id,
            branding=branding,
            components=components
        )

    def _get_branding_for_client(self, client_id: str) -> BrandingConfig:
        """
        Retorna la configuración visual adaptada al cliente desde la DB.
        """
        db_brand = db_manager.get_branding(client_id)
        if not db_brand:
            return BrandingConfig()

        # Si tenemos branding en DB, mapeamos campos
        # lead_brand_configs: primary_color, secondary_color, project (como agent_name)
        return BrandingConfig(
            primary_color=db_brand.get("primary_color", "#4b38b3"),
            secondary_color=db_brand.get("secondary_color", "#6366f1"),
            agent_name=db_brand.get("project", db_brand.get("agent_name", "Hommie AI"))
        )

    def _extract_properties_from_sources(self, sources: List[Dict[str, Any]]) -> List[PropertyCard]:
        """
        Analiza los sources devueltos por RAG. Si encuentra metadatos de propiedades,
        crea los objetos PropertyCard correspondientes consultando la base de datos real.
        """
        cards = []
        for source in sources:
            metadata = source.get("metadata", {})
            
            # Buscamos el ID de la propiedad (puede venir como 'id' o 'external_prop_id')
            prop_id = metadata.get("id") or metadata.get("id_propiedad")
            
            if prop_id:
                # 🚀 CONSULTA REAL A LA DB
                prop_data = db_manager.get_property(prop_id)
                if prop_data:
                    try:
                        title = prop_data.get("title", "Propiedad Sugerida").replace("&#8211;", "-")
                        card = PropertyCard(
                            id=str(prop_data.get("id")),
                            title=title,
                            price=float(prop_data.get("price", 0)),
                            location=f"{prop_data.get('address_city', '')}, {prop_data.get('address_state', '')}".strip(", "),
                            image_url=prop_data['images'][0] if prop_data.get('images') else None,
                            tags=prop_data.get("features", {}).get("highlights", []) if isinstance(prop_data.get("features"), dict) else []
                        )
                        cards.append(card)
                    except Exception as e:
                        logger.warning(f"Error mapeando data de DB a PropertyCard: {e}")
                        continue
        
        return cards
