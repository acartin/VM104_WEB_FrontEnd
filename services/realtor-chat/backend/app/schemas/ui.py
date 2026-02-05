from typing import List, Optional, Union, Dict
from pydantic import BaseModel, Field

class BaseComponent(BaseModel):
    type: str
    id: Optional[str] = None

class ChatMessage(BaseComponent):
    type: str = "chat"
    text: str
    sender: str  # "bot" or "user"

class PropertyCard(BaseComponent):
    type: str = "property-card"
    title: str
    price: float
    image_url: Optional[str] = None
    features: Dict[str, Union[int, float, str]] = {}
    tags: List[str] = []

class MortgageCalculator(BaseComponent):
    type: str = "mortgage-calculator"
    property_price: float
    default_interest: float = 8.5
    allow_custom_input: bool = True

class PropertyGrid(BaseComponent):
    type: str = "property-grid"
    title: str
    properties: List[PropertyCard]
    layout: str = "horizontal"

class PropertyMap(BaseComponent):
    type: str = "property-map"
    center: Dict[str, float]  # {"lat": ..., "lng": ...}
    zoom: int = 15
    pois: List[Dict[str, Union[float, str]]] = []
    interactive: bool = True

class ActionMenu(BaseComponent):
    type: str = "action-menu"
    title: Optional[str] = None
    options: List[Dict[str, str]]  # [{"label": "Ver Más", "payload": "HOUSE_123"}]

class PhotoCarousel(BaseComponent):
    type: str = "photo-carousel"
    images: List[str]
    show_thumbnails: bool = False

class BrandingConfig(BaseModel):
    primary_color: str = "#4b38b3"
    secondary_color: str = "#6366f1"
    logo_url: Optional[str] = None
    agent_name: str = "Hommie AI"

class SDUIResponse(BaseModel):
    session_id: str
    branding: Optional[BrandingConfig] = None
    components: List[Union[ChatMessage, PropertyCard, MortgageCalculator, PropertyGrid, PropertyMap, ActionMenu, PhotoCarousel]]
