# 📸 Guía: Agregar Screenshots al README

Para que tu portfolio sea completamente visual, agrega capturas de pantalla de la aplicación. Aquí está cómo hacerlo profesionalmente.

---

## 🎯 Screenshots que necesitas

### 1. Página de inicio (Home)
- Muestra las dos opciones principales
- Debe verse el diseño dark + cyan accent
- **Nombre de archivo**: `screenshot_home.png`

### 2. Formulario de distribución
- Campo ID cable
- Campo Ciudad
- Botón de envío
- **Nombre de archivo**: `screenshot_form_distribucion.png`

### 3. Tabla de resultados
- Muestra los resultados del recorrido
- Visibles los splitters (div1, div2)
- Mínimo 10-15 filas
- **Nombre de archivo**: `screenshot_results.png`

---

## 🚀 Cómo capturar screenshots

### Windows + Chrome/Firefox

#### Opción A: Herramienta de captura nativa
```
1. Win + Shift + S
2. Selecciona área
3. Guarda en /docs/images/
```

#### Opción B: Chrome DevTools
```
1. F12 abre DevTools
2. Ctrl + Shift + P → "Screenshot"
3. Elige "Capture full page"
4. Guarda en /docs/images/
```

#### Opción C: Firefox Screenshot
```
1. Click derecho en página
2. "Tomar captura de página"
3. Guarda en /docs/images/
```

---

## 📁 Estructura de carpetas

```
Proyecto-real-telecomunicaciones/
├── docs/
│   ├── images/                          ← ← CREAR ESTA CARPETA
│   │   ├── screenshot_home.png
│   │   ├── screenshot_form_distribucion.png
│   │   ├── screenshot_results.png
│   │   └── diagram_architecture.png     (opcional: diagram exportado)
│   ├── ARQUITECTURA.md
│   ├── QUICKSTART.md
│   └── decisiones_tecnicas.md
├── README.md
└── ...
```

Comando para crear la carpeta:
```bash
mkdir docs\images
```

---

## 🎨 Cómo insertar screenshots en README

Después de tomar las capturas, edita `README.md` y agrega:

### En la sección "🖼️ Interfaz"

Reemplaza esto:

```markdown
## 🖼️ Interfaz

### Página de inicio
Dos opciones de recorrido disponibles:
```

Por esto:

```markdown
## 🖼️ Interfaz

### Página de inicio
Dos opciones de recorrido disponibles:

<div align="center">
  <img src="docs/images/screenshot_home.png" alt="Página inicio" width="800">
</div>

- **01 Distribución**: Recorre red de distribución cable-cable
- **02 Alimentación**: Recorre tramo de alimentación

### Formulario

<div align="center">
  <img src="docs/images/screenshot_form_distribucion.png" alt="Formulario" width="800">
</div>

Campos simples:
- ID del cable inicial (objectid)
- Ciudad (para filtrar proyectos de red)

### Resultado

<div align="center">
  <img src="docs/images/screenshot_results.png" alt="Tabla de resultados" width="800">
</div>

| Cable origen | Fil. origen | Fil. destino | Cable destino | TP origen | TP destino | Splitter |
|---|---|---|---|---|---|---|
| CBL-A | 1 | 13 | CBL-B | CTO-001 | CTO-002 | — |
| CBL-A | 2 | 14 | CBL-B | CTO-001 | CTO-002 | — |
```

---

## ⚙️ Optimización de imágenes

Para que las imágenes se carguen rápido en GitHub:

### Comprimir PNG

**Windows (online)**:
- Sube a: https://tinypng.com
- Descarga comprimido

**Windows (PowerShell)**:
```powershell
# Instala ImageMagick primero
choco install imagemagick

# Comprimir
magick convert screenshot_home.png -resize 1200x600 -quality 85 screenshot_home_optimized.png
```

### Tamaño recomendado

```
Ancho: 800-1200px
Altura: auto (proporcional)
Tamaño archivo: < 200KB cada una
Formato: PNG o JPG
```

---

## ✏️ Markdown avanzado para screenshots

### Con caption y link

```markdown
<div align="center">
  <figure>
    <img src="docs/images/screenshot_home.png" alt="Página de inicio" width="900">
    <figcaption><b>Fig. 1:</b> Página de inicio con selector de tipo de recorrido</figcaption>
  </figure>
</div>
```

### Con bordes y sombra (HTML)

```html
<div align="center">
  <img 
    src="docs/images/screenshot_home.png" 
    alt="Página de inicio"
    width="900"
    style="border: 1px solid #00d4aa; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,212,170,0.2);">
</div>
```

### Comparativa lado a lado

```markdown
<div align="center">
  <table>
    <tr>
      <td width="50%" align="center">
        <img src="docs/images/screenshot_form.png" width="100%">
        <b>Formulario</b>
      </td>
      <td width="50%" align="center">
        <img src="docs/images/screenshot_results.png" width="100%">
        <b>Resultados</b>
      </td>
    </tr>
  </table>
</div>
```

---

## 🎬 Video (Opcional)

Si quieres ir más allá, graba un GIF corto:

### Usando ScreenToGif (Windows)

1. Descarga: https://www.screentogif.com
2. Graba tu interacción (30-45 segundos)
3. Exporta como GIF
4. Inserta en README:

```markdown
<div align="center">
  <img src="docs/images/demo.gif" alt="Demo del flujo" width="800">
</div>
```

---

## ✅ Checklist final

- [ ] Carpeta `/docs/images/` creada
- [ ] 3 screenshots capturados
- [ ] Imágenes comprimidas
- [ ] URLs actualizadas en README
- [ ] README visto en GitHub (markdown renderiza bien)
- [ ] Tamaño total < 1MB

---

## 📍 Ubicación en README para agregar

Edita el README.md y en la sección "🖼️ Interfaz" (línea ~160 aprox) agrega los `<img>` tags.

**Referencia**: Ya tienes esta sección, solo reemplaza el contenido de texto por imágenes + descripción.

---

## 🖇️ Links útiles

- [Markdown + Images](https://docs.github.com/en/github-flavored-markdown/writing-on-github/getting-started-with-writing-and-formatting-on-github)
- [TinyPNG](https://tinypng.com) - Compresor online
- [ScreenToGif](https://www.screentogif.com) - Grabar pantalla
- [Paste as Markdown](https://www.alwaysdata.com/apps/pastemarkdown/) - Convertir imágenes a Markdown

---

## 📸 Vista previa antes de pushear

Antes de hacer commit, verifica que GitHub renderiza bien:

1. Sube a una rama de testing
2. Abre en GitHub (github.com/tu-repo)
3. Verifica que imágenes se cargan
4. Verifica "layout" en mobile (?)
5. Si todo OK: haz merge a main

---

¡Listo! Con estos screenshots tu README será completamente visual y profesional para portfolio. 🚀
