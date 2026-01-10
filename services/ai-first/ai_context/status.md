# ESTADO DEL PROYECTO (UPDATE 2026-01-10)

Estamos desarrollando un sistema CRM con arquitectura **SDUI (Server-Driven UI)** y modelo **Person-Centric**.

### Hitos Alcanzados

1.  **Vínculo Identidad-Acceso**: Modelo `User` -> `LeadContact` operativo.
2.  **Integridad de Datos**: Hard Delete y Reactivación Inteligente implementados.
3.  **Dashboards Aislados**:
    *   `client_admin_dash`: Visión gerencial.
    *   `client_user_dash`: Panel operativo para vendedores, verificado con datos reales.
4.  **Menús por Rol**: Configurados en `menus.py`.
5.  **Filtrado Operativo**: Nuevo módulo `leads` con endpoint `/leads/me` para visualización total de leads del vendedor.

### Filosofía de Trabajo (Cero Fricción)

1.  **"Mis Leads" es Sagrado**: Tanto para el Vendedor como para el Admin "Solopreneur", el menú principal es **"Mis Leads"** (`/leads/me`). Muestra solo lo que me toca atender hoy.
2.  **Admin = Orquestador**: Si el Administrador tiene equipo, usa su **Dashboard de Admin** para ver el "Big Picture" y gestionar a sus vendedores.
3.  **Crecimiento Orgánico**: El concepto escala sin fricción. Si empiezas solo, usas "Mis Leads". Si contratas a alguien, el sistema ya está listo para que tú supervises y ellos operen con el mismo lenguaje.
