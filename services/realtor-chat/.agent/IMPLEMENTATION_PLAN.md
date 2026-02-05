# 🗺️ IMPLEMENTATION PLAN: SDUI Bridge

Este documento detalla las fases de ejecución y la lógica técnica del sistema.

## 🚀 Hoja de Ruta (Roadmap)

### Fase 1: Cimientos (COMPLETADA ✅)
*   Infraestructura Docker (Bridge, Redis, Static).
*   Contratos SDUI Base y FastAPI Skeleton.
*   Renderer Core en Vanilla JS.
*   Primer Web Component: `property-card` (Lit).

### Fase 2: Inteligencia (EN CURSO 🏃‍♂️)
*   **Inference Bridge**: Conexión real con el núcleo de IA.
*   **Intent Router**: Lógica para decidir qué componente visual inyectar.
*   **Session Manager (Redis)**: Implementación de la persistencia de contexto efímero.
*   **Realtor Widgets**: Implementación de `PropertyGrid` y tarjetas con datos reales.

### 🧩 Arquitectura de Componentes Polymorphic

Para manejar la complejidad visual, el sistema se organiza en **Contratos (Backend)** y **Widgets (Frontend)**.

### 1. Mapa de Componentes (Widgets)

| Componente | Rol | Datos Clave (Contrato) |
| :--- | :--- | :--- |
| `ChatMessage` | Burbuja de texto base | `text`, `sender` |
| `PropertyCard` | Ficha premium de casa | `id`, `title`, `price`, `location`, `image_url` |
| `PropertyGrid` | Carrusel horizontal de casas | `Array<PropertyCard>` |
| `MapCard` | Mapa interactivo con POIs | `center`, `pois[]`, `zoom` |
| `MortgageCalculator` | Calculadora hipotecaria | `property_price`, `interest_rate`, `term` |
| `ActionMenu` | Botones de respuesta rápida | `options[]` (label + payload) |
| `PhotoCarousel` | Galería de imágenes pura | `images[]` |

### 2. Estructura de Folders Sugerida

#### **Backend (`backend/app/`)**
*   `schemas/ui.py`: Contratos Pydantic de cada componente. (Única fuente de verdad del esquema).
*   `transformer/`: Lógica para procesar datos de IA.
    *   `base.py`: Clase base del transformer.
    *   `realtor.py`: Generador de `PropertyCard` y `Grid`.
    *   `tools.py`: Generador de `Calculator` y `Maps`.
*   `session/`: Cache en Redis del estado de estos componentes.

#### **Frontend (`frontend/components/`)**
*   `base-chat/`: Burbujas de texto.
*   `realtor/`: `property-card.js`, `property-grid.js`.
*   `interactive/`: `mortgage-calc.js`, `action-menu.js`.
*   `media/`: `photo-carousel.js`, `map-widget.js`.

### 3. Flujo del Contrato SDUI (API Bridge)

El Bridge siempre responderá con un `SDUIResponse` que es un envoltorio de un array de componentes.

**Ejemplo de flujo**:
1.  **AI detecta intención**: "muéstrame opciones".
2.  **Transformer genera**:
    *   1x `ChatMessage` ("He encontrado estas opciones...")
    *   1x `PropertyGrid` (con 5 `PropertyCard` dentro).
    *   1x `ActionMenu` ("¿Quieres ver la ubicación?", "¿Calcular cuota?").

## 🔌 Integración Real: Core AI (192.168.0.32:8003)

El Bridge actuará como cliente del Motor de Inferencia centralizado.

### Destino de Inferencia
- **URL Base**: `http://192.168.0.32:8003/api/v1` (Confirmado por legado)
- **Endpoint Chat**: `POST /chat`
- **Nota**: Si hay problemas de conexión desde el contenedor, verificar red/firewall del Host 32.

### Contrato de Conexión (Bridge -> Core)
El Bridge debe propagar los metadatos capturados del lead:
```json
{
  "client_id": "...",
  "lead_id": "...",
  "conversation_id": "...",
  "queryText": "...",
  "utm_source": "...",
  "source_property_ref": "...",
  "landing_page_url": "..."
}
```

### Lógica del Transformer (La "Magia" del Bridge)
1. **Input**: Respuesta plana de la IA (`answer`, `sources`).
2. **Proceso**:
   - Mapear `answer` a un componente `ChatMessage`.
   - Si existen `sources` con datos de propiedad, generar `PropertyGrid` o `PropertyCard`.
   - Si la IA sugiere acciones (ej: "calcula tu cuota"), inyectar `ActionMenu`.
3. **Output**: `SDUIResponse` (Contrato maestro en `ui.py`).

---

## ⚙️ Lógica de Datos y Redis

### El Rol de Redis
Redis **no** guarda el chat. Guarda el "Contexto de Pantalla":
1.  **Mapeo de Índices**: Traduce "la segunda opción" al ID real de la base de datos.
2.  **Estado de Widgets**: Guarda progresos parciales (ej: datos en la calculadora) antes de enviarlos.
3.  **Cache Técnica**: Resultados de búsqueda pesados para scroll/filtros rápidos.

### Auditoría
Cada interacción que deba ser persistente **debe** pasar o ser reflejada en `lead_conversations` del sistema central.

## 🧪 Estrategia de Testing
*   **Unit**: Transformación JSON -> Componente.
*   **Integration**: Flujo Redis -> Bridge -> Front.
*   **Visual**: Renderizado de componentes Lit con diversos estados de datos.
