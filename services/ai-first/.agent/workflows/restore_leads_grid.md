---
description: Restauración del Grid de Leads y Seller Workspace (V2 Clean)
---

# Restauración del Grid de Leads (V2)

Este flujo de trabajo guiará la creación limpia del `seller_workspace` en el backend y la configuración del `CustomLeadsGrid` en el frontend.

## Fase 1: Backend (Seller Workspace)

1. Crear la estructura del módulo `seller_workspace` con `__init__.py`.
   - `backend/app/dashboards/seller_workspace/__init__.py` (Vacío)
   - `backend/app/dashboards/seller_workspace/router.py` (Endpoint)
   - `backend/app/dashboards/seller_workspace/schema.py` (JSON UI)

2. Registrar el nuevo router en `backend/app/main.py`.

## Fase 2: Frontend (Custom Grid Engine)

3. Crear/Verificar el motor del grid en JS.
   - `frontend/renderer/engine/CustomLeadsGrid.js`

4. Registrar el componente en el sistema de hidratación.
   - `frontend/renderer/engine/registry.js`
   - `frontend/renderer/engine/hydration.js`

## Fase 3: Validación

5. Cache Busting en `index.html`.
6. Verificar carga del grid y funcionalidad de filtros.
