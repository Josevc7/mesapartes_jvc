# Directorio de Imágenes - Sistema Mesa de Partes DRTC

## Logo Institucional

Para que el logo aparezca correctamente en el sistema, sigue estos pasos:

### 1. Preparar el Logo

El logo debe cumplir con las siguientes especificaciones:

- **Nombre del archivo**: `logo-drtc.png`
- **Formato recomendado**: PNG con fondo transparente
- **Dimensiones recomendadas**:
  - Ancho: 120-200 px
  - Alto: 40-60 px
  - Relación de aspecto: Horizontal (landscape)
- **Tamaño máximo**: 100 KB
- **Resolución**: 72-144 DPI (para web)

### 2. Ubicación del Archivo

Coloca el archivo del logo en esta ubicación:
```
public/images/logo-drtc.png
```

### 3. Formatos Alternativos

Si prefieres usar otro formato, puedes usar:

- **SVG** (Recomendado para logos vectoriales):
  - Nombre: `logo-drtc.svg`
  - Ventaja: Escalable sin pérdida de calidad
  - Ideal para logos con pocos colores

- **JPG** (Solo si no hay transparencia):
  - Nombre: `logo-drtc.jpg`
  - Nota: Asegúrate de que el fondo coincida con el color naranja (#cc5500)

### 4. Verificar el Logo

Después de colocar el archivo:

1. Limpia la caché del navegador (Ctrl + F5)
2. Actualiza la página
3. El logo debe aparecer en el navbar superior junto al texto "Mesa de Partes DRTC"

### 5. Configuración Avanzada

Si necesitas ajustar el tamaño o posición del logo, edita:

**Archivo**: `public/css/logo-styles.css`

```css
.navbar-brand img {
    height: 40px;  /* Ajustar altura */
    width: auto;   /* Mantiene proporción */
    margin-right: 12px;  /* Espacio entre logo y texto */
}
```

### 6. Responsividad

El logo está configurado para adaptarse a diferentes tamaños de pantalla:

- **Desktop**: Logo completo + texto (40px altura)
- **Tablet**: Logo + texto reducido (38px altura)
- **Móvil**: Solo logo, sin texto (35px altura)

### 7. Ejemplo de Estructura de Archivos

```
public/
├── images/
│   ├── logo-drtc.png       ← Logo principal
│   ├── logo-drtc.svg       ← (Opcional) Logo vectorial
│   ├── favicon.ico         ← Favicon del navegador
│   └── README.md           ← Este archivo
```

### 8. Problemas Comunes

**El logo no aparece:**
- Verifica que el nombre del archivo sea exactamente `logo-drtc.png`
- Asegúrate de que el archivo esté en `public/images/`
- Limpia la caché del navegador (Ctrl + Shift + R)
- Verifica permisos del archivo (debe ser legible)

**El logo se ve borroso:**
- Usa una imagen de mayor resolución
- Considera usar formato SVG
- Asegúrate de que la imagen sea de buena calidad

**El logo es muy grande/pequeño:**
- Edita `public/css/logo-styles.css`
- Ajusta la propiedad `height` en `.navbar-brand img`

---

## Contacto

Para soporte técnico, contacta al administrador del sistema.
