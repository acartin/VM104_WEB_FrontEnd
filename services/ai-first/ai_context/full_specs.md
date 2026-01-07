# ESPECIFICACIÓN MAESTRA WEB IAFIRST (CAPÍTULOS 1-14)

## 1. MISIÓN Y PROPÓSITO
La capa web es estrictamente operacional. Prohibida la lógica creativa o heurística. El objetivo es baja latencia y ejecución determinista.

## 2. PRINCIPIOS ESTRUCTURALES
- **Backend Soberano:** El frontend es un terminal tonto (dumb terminal). No decide, solo obedece contratos.
- **Multitenancy Explícito:** El `cliente_id` es obligatorio en toda transacción y consulta SQL.
- **Seguridad Determinista:** Autorización binaria (Permitido/Denegado) resuelta siempre en Backend.

## 3. CAPA DE ACCESO A DATOS (DAL)
- **Cero ORM:** Prohibido el uso de abstracciones mágicas. Solo SQL explícito.
- **Lectura/Escritura Separada:** Uso de vistas SQL (`v_leads_grid`) para optimizar la lectura densa de datos.

## 4. CONTRATOS Y UI-GUARD
- Toda comunicación se valida mediante Pydantic v2.
- Si un componente no está en el catálogo, el backend lo elimina del JSON antes de enviarlo.

## 5. SISTEMA DE UI Y TEMA VELZON
- **UI Declarativa:** La interfaz se define como un árbol JSON.
- **Tokens de Diseño:** Uso obligatorio de tokens: primary, success, danger, warning, info.
- **Layouts:** Uso de sistema de 12 columnas basado en Velzon.
- **Catálogo Cerrado:** Solo se permiten componentes validados (card-metric, grid-visual, form-container, etc.).

## 6. PROHIBICIONES EXPLÍCITAS
- No lógica de negocio en Frontend.
- No consultas directas a tablas desde la UI.
- No estilos CSS ad-hoc.
- No saltarse la validación de cliente_id.
