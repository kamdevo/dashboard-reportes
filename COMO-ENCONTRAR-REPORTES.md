# 🎯 Cómo Encontrar la Sección de Reportes

## 📍 Ubicación de la Sección

La sección de reportes ahora está integrada en la aplicación principal. Aquí te explico cómo encontrarla:

### 1. **Abrir la Aplicación**

- Ve a: **http://localhost:3000**
- Asegúrate de que el servidor Next.js esté ejecutándose (`pnpm dev`)

### 2. **Navegar a la Sección de Reportes**

1. En la página principal verás un header con "Reportes Inovación"
2. En la navegación lateral hay **2 secciones principales**:
   - **Reportes** ← **¡ESTA ES LA SECCIÓN QUE BUSCAS!**
   - **Archivos** (gestión de archivos)

### 3. **Usar la Funcionalidad de Reportes**

Al hacer clic en la sección **"Reportes"**, verás:

#### ✅ **Vista Principal de Reportes:**

- Cards con todos los reportes existentes
- Información de cada reporte (título, autor, fecha, estado)
- Botones de acción (Ver, Descargar, Editar, etc.)

#### ✅ **Botón "Nuevo Reporte":**

- Ubicado en la esquina superior derecha
- Al hacer clic se abre el modal de subida
- Permite arrastrar archivos PDF, Excel, Word
- Formulario con título y descripción

#### ✅ **Funcionalidades Disponibles:**

- 📤 **Subir archivos PDF/Excel/Word**
- 📥 **Descargar archivos**
- 👁️ **Ver detalles del reporte**
- ✏️ **Editar información**
- 💬 **Agregar comentarios**
- ⭐ **Calificar reportes**
- 🗑️ **Eliminar reportes**

### 4. **Sección de Archivos Adicional**

En la pestaña **"Files"** también puedes:

- Ver todos los archivos subidos
- Gestión avanzada de archivos
- Vista en grid o lista
- Filtros por tipo de archivo

## 🚀 **Pasos para Probar la Subida de PDF:**

1. **Ve a http://localhost:3000**
2. **Haz clic en la pestaña "Reports"**
3. **Haz clic en "Nuevo Reporte"** (botón azul en la esquina)
4. **Completa el formulario:**
   - Título: "Mi Primer Reporte PDF"
   - Descripción: "Prueba de subida de archivo"
5. **Arrastra tu archivo PDF** al área designada o haz clic para seleccionar
6. **Haz clic en "Crear Reporte"**
7. **¡Tu archivo aparecerá en la lista con botón de descarga!**

## 🔧 **Si No Ves las Pestañas:**

1. Verifica que el servidor esté ejecutándose:

   ```bash
   pnpm dev
   ```

2. Verifica que el servidor PHP esté activo:

   ```bash
   php -S localhost:8000
   ```

3. Recarga la página (Ctrl + F5)

## 📱 **Vista de la Interfaz:**

```
┌─────────────────────────────────────────────────────────┐
│ 🌿 Reportes Inovación                    🔔 ➕ 👤        │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ ◄► [Reportes] [Archivos] ← NAVEGACIÓN LATERAL           │
│                                                         │
│ Gestión de Reportes                    [Nuevo Reporte] │
│ Administra y evalúa todos los reportes del sistema     │
│                                                         │
│ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐        │
│ │📄 Reporte 1 │ │📊 Reporte 2 │ │📝 Reporte 3 │        │
│ │  [Descargar]│ │  [Descargar]│ │  [Descargar]│        │
│ └─────────────┘ └─────────────┘ └─────────────┘        │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

## ✅ **¡Listo!**

Ahora ya sabes dónde encontrar la sección de reportes. La funcionalidad completa de subida de archivos PDF está disponible en la pestaña **"Reports"** de la aplicación principal.

¡Prueba subir tu primer archivo PDF!
