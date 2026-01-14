# Changelog - AI-First Service

Todas las versiones estables del servicio AI-First están documentadas aquí.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [v68-stable] - 2026-01-13

### Added
- **CustomGrid Component**: Grid personalizado con sorting y paginación client-side
- **Bootstrap Theme Integration**: Colores adaptables a Light/Dark mode
- **Database-Driven Colors**: Sistema de colores térmicos desde `lead_scoring_definitions`
- **Enhanced Sort Indicators**: Flechas grandes (20px, bold) con columna destacada
- **Default Sorting**: Grid ordena por `score_total DESC` al cargar

### Changed
- Reemplazado `grid-leads-control` legacy con `custom-leads-grid`
- Migrados colores de `thermal-*` a Bootstrap semantic (`danger`, `warning`, `success`, `secondary`)
- Mejorados indicadores visuales de sorting

### Removed
- Endpoint `/leads/beta` (47 líneas)
- Menú "Leads (BETA)" del sidebar
- CSS custom de colores térmicos (8 líneas)

### Technical Details
- **Files Modified**: `router.py`, `service.py`, `CustomGrid.js`, `Sidebar.js`, `menus.py`
- **Database Changes**: Migración de colores en `lead_scoring_definitions`
- **Lines Removed**: 48 total

---

## [Unreleased]

### Planned for v69
- Grid filters implementation
- Filter by score range
- Filter by column values
- Combined filters support

---

## Cómo Usar Este Archivo

**Solo actualizar cuando:**
- ✅ Haces un tag estable (`vXX-stable`)
- ✅ Completas una feature importante
- ✅ Haces un release

**NO actualizar para:**
- ❌ Commits WIP
- ❌ Fixes menores
- ❌ Refactorings internos
