# 🏛️ Arquitectura de Sistema - VM 104 (Frontends)

Esta documentación describe la interconexión entre el Proxy Externo, el Host de Docker y los servicios de Frontend.

## 🗺️ Mapa de Red

El tráfico fluye desde el exterior hacia los servicios internos siguiendo esta cadena:

1. **Cliente Web** (Puerto 80/443)
2. **Nginx Proxy Manager (LXC .36)**: Gestiona SSL y certificados.
3. **VM 104 (192.168.0.34)**: Recibe el tráfico en puertos específicos.
4. **Contenedores Docker**: Procesan la solicitud.

### Mapeo de Puertos y Servicios

| Servicio | Puerto VM 104 | Contenedor Interno | Destino Final |
| :--- | :--- | :--- | :--- |
| **Static Web** | 8081 | `prd-web-static-datasyncsa-01` | Nginx (Alpine) |
| **Chat Client** | 8082 | `prd-web-chat-client-01` | Nginx (Alpine) |
| **Admin Console** | 8083 | `prd-web-admin-nginx-01` | Nginx + PHP 8.3 |

## 🔄 Flujo de Datos y Despliegue

La arquitectura se rige por la separación estricta entre el código de desarrollo y el entorno de producción servido.

### Patrón Repo-to-WWW
Para garantizar la integridad, los contenedores Nginx nunca apuntan a carpetas de desarrollo.

```text
[ Carpeta repo/ ] --( deploy.sh )--> [ Carpeta www/ ] <--( Mapeo Docker )--> [ Cliente ]

Desarrollo: Los cambios se suben/editan en services/<nombre>/repo/.

Sincronización: El script deploy.sh limpia y mueve los archivos a www/.

Servicio: Nginx sirve exclusivamente el contenido de www/.

Especificaciones de los Servicios
1. Servicios Estáticos (Nginx Puro)
Imagen: nginx:alpine

Configuración: /usr/share/nginx/html montado como Solo Lectura (ro).

2. Admin Console (Stack Desacoplado)
Servidor Web: Nginx actúa como puente.

Runtime: PHP-FPM procesa la lógica de Laravel.

Seguridad: El servidor web solo tiene acceso a la carpeta public/ de Laravel mediante el mapeo en /www/.

🔒 Seguridad y Permisos
Aislamiento: Red interna de Docker web-internal (driver bridge).

Propietario: Todos los archivos pertenecen a $USER:www-data.

Visibilidad: Solo los puertos 8081-8083 están expuestos a la red local; el acceso directo a los contenedores está restringido.