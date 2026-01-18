---
type: task
status: todo
---

# Restauración Limpia del Lead Grid

Este plan de ejecución define los pasos necesarios para implementar correctamente el `seller_workspace` y el `CustomLeadsGrid` en la nueva rama limpia `feature/leadgrid-details-v2`.

## 1. Backend: Estructura del Seller Workspace
- [ ] **Crear directorio del módulo**: `backend/app/dashboards/seller_workspace` <!-- id: 0 -->
- [ ] **Inicializar paquete**: Crear `__init__.py` vacío para evitar errores de importación. <!-- id: 1 -->
- [ ] **Implementar Router**: Crear `router.py` con el endpoint `/client-user` que sirva la configuración del Grid. <!-- id: 2 -->
- [ ] **Definir JSON Schema**: Crear `schema.py` con la estructura del dashboard (`layout: dashboard-standard`) y columnas del Grid. <!-- id: 3 -->
- [ ] **Registrar Router**: Incluir el nuevo router en `backend/app/main.py`. <!-- id: 4 -->

## 2. Frontend: Motor del Grid
- [ ] **Configurar Engine JS**: Verificar/Crear `CustomGrid.js` (o `CustomLeadsGrid.js`) con la lógica de renderizado. <!-- id: 5 -->
- [ ] **Verificar Filtros**: Asegurar que `GridFilters.js` está integrado. <!-- id: 6 -->
- [ ] **Registrar Componente**: Mapear `custom-leads-grid` en `registry.js`. <!-- id: 7 -->
- [ ] **Configurar Hidratación**: Asegurar que `hydration.js` instancia la clase correcta. <!-- id: 8 -->

## 3. Validación y Despliegue
- [ ] **Cache Busting**: Incrementar versión en `index.html` para forzar recarga limpia. <!-- id: 9 -->
- [ ] **Verificación Visual**: Confirmar que el Grid carga datos, filtros y sorting correctamente. <!-- id: 10 -->
