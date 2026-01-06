# 🚀 Hommie Chatbot - Integración Simple con WordPress

## ✅ Solución SIMPLE con iframe

### Paso 1: Asegurar que widget.html funcione
El archivo `widget.html` ya está creado y listo para usar en iframe.

### Paso 2: Copiar código en WordPress

1. Abre el archivo **WIDGET-WORDPRESS-SIMPLE.html**
2. Copia TODO el contenido
3. En WordPress:
   - Instala el plugin **"Insert Headers and Footers"**
   - Ve a **Ajustes → Insert Headers and Footers**
   - Pega el código en **"Scripts in Footer"**
   - Guarda

### Paso 3: Personalizar tu client_id

Cambia esta línea en el código:
```html
src="https://bot.datasyncsa.com/widget.html?client_id=TEST123"
```

Por tu client_id real, por ejemplo:
```html
src="https://bot.datasyncsa.com/widget.html?client_id=WORDPRESS-CLIENTE-001"
```

---

## 📁 Archivos del sistema

### Archivos principales:
- `index.html` - Chatbot standalone (para bot.datasyncsa.com)
- `widget.html` - Widget embebible (para iframe)
- `config.js` - Configuración (API, webhooks, client_id)
- `core-api.js` - Lógica de comunicación con n8n
- `script.js` - Funcionalidad del chatbot
- `style.css` - Estilos

### Archivo para WordPress:
- `WIDGET-WORDPRESS-SIMPLE.html` - Código para copiar en WordPress

---

## 🎯 Cómo funciona

1. **WordPress** muestra un botón flotante
2. Al hacer clic, abre un iframe
3. El iframe carga `widget.html?client_id=XXX`
4. El widget recibe el `client_id` y lo usa en todas las conversaciones
5. El chatbot se comunica con n8n usando ese `client_id`

---

## ⚙️ Configurar servidor para iframe

Si ves el error `X-Frame-Options`, necesitas configurar tu servidor.

### Para Nginx:
```nginx
location /widget.html {
    add_header X-Frame-Options "ALLOWALL";
    # O específicamente:
    # add_header X-Frame-Options "ALLOW-FROM https://tusitio.com";
}
```

### Para Apache (.htaccess):
```apache
<Files "widget.html">
    Header always unset X-Frame-Options
</Files>
```

---

## 🎨 Personalización

### Cambiar posición del botón:
```css
#hommie-chatbot-toggle {
    bottom: 20px;
    left: 20px;  /* En vez de right */
}
```

### Cambiar tamaño del chat:
```css
#hommie-chatbot-iframe-wrapper {
    width: 500px;  /* Ancho */
    height: 700px; /* Alto */
}
```

### Cambiar color del botón:
```css
#hommie-chatbot-toggle {
    background: linear-gradient(135deg, #FF6B6B 0%, #FFE66D 100%);
}
```

---

## 🔧 Solución de problemas

### El iframe no se muestra:
1. Verifica que `widget.html` esté accesible: `https://bot.datasyncsa.com/widget.html`
2. Revisa la consola del navegador (F12) para errores
3. Asegúrate de que no haya `X-Frame-Options` bloqueando

### El client_id no se pasa:
1. Verifica la URL en el iframe tenga el parámetro: `?client_id=XXX`
2. Abre la consola en el iframe y verifica que aparezca: "Widget iniciado con client_id: XXX"

### El chatbot no responde:
1. Verifica que `config.js` tenga la URL correcta de n8n
2. Verifica que `core-api.js` esté cargando correctamente
3. Revisa la consola para errores de red

---

## 📝 Ventajas de este enfoque

✅ **Simple** - Solo un iframe  
✅ **Sin CORS** - No hay problemas de cross-origin  
✅ **Aislado** - El chatbot no interfiere con WordPress  
✅ **Personalizable** - client_id por URL  
✅ **Responsive** - Funciona en móvil y desktop  
✅ **Fácil de mantener** - Cambios solo en un lugar  

---

¡Listo! Tu chatbot debería funcionar perfectamente en WordPress. 🎉
