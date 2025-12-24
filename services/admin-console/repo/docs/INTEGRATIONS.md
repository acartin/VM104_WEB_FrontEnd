# Guía de Gestión de Integraciones y Proveedores

Este documento explica cómo gestionar los proveedores soportados en el módulo de Integraciones y Tokens.

## Ubicación del Código
La lógica de los proveedores está centralizada en un Enum de PHP:
`app/Enums/IntegrationProvider.php` 

Gracias a esto, cualquier cambio aquí se replica automáticamente en formularios, tablas y en ambos paneles (Admin y App).

## Cómo Agregar un Nuevo Proveedor

Para soportar una nueva plataforma (por ejemplo, **Pinterest**), sigue estos 3 pasos en el archivo `IntegrationProvider.php`:

### 1. Agregar el Case
Añade una nueva opción al enum. El valor (string) es lo que se guardará en la base de datos.

```php
enum IntegrationProvider: string implements HasLabel, HasColor
{
    case Meta = 'meta';
    case Google = 'google';
    case Amazon = 'amazon';
    case Pinterest = 'pinterest'; // <--- NUEVO
    // ...
```

### 2. Configurar la Etiqueta (getLabel)
Define cómo se verá el nombre en los formularios y tablas.

```php
public function getLabel(): ?string
{
    return match ($this) {
        self::Meta => 'Meta (Facebook/Instagram)',
        // ...
        self::Pinterest => 'Pinterest Ads', // <--- NUEVO
    };
}
```

### 3. Configurar el Color (getColor)
Define el color del "badge" que aparece en las tablas.
Colores disponibles: `primary`, `secondary`, `success`, `danger`, `warning`, `info`, `gray`.

```php
public function getColor(): string|array|null
{
    return match ($this) {
        self::Meta => 'info',
        // ...
        self::Pinterest => 'danger', // <--- NUEVO
    };
}
```

## Notas Técnicas
*   **Base de Datos**: No se requiere migración. La columna `provider` es un string simple.
*   **Credenciales**: Las credenciales se guardan encriptadas automáticamente gracias al cast del modelo `Integration`.
