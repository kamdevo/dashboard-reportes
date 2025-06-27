# Sistema de Reportes con Subida de Archivos PDF

## 🚀 Funcionalidades Implementadas

### ✅ Subida de Archivos PDF

- Drag & drop de archivos PDF, Excel y Word
- Validación de tipos de archivo permitidos
- Validación de tamaño máximo (50MB)
- Vista previa del archivo seleccionado
- Interfaz intuitiva con indicadores visuales

### ✅ Almacenamiento en Base de Datos

- Los archivos se guardan en `/uploads/reportes/`
- Metadatos almacenados en la tabla `reportes`
- Sistema de versioning con nombres únicos
- Registro de actividad del usuario

### ✅ Renderizado en Tabla de Reportes

- Vista de cards con información del reporte
- Botón de descarga para cada archivo
- Íconos según el tipo de archivo
- Estado del reporte (pendiente, revisión, aprobado)
- Ratings y comentarios

### ✅ Vista Previa de PDFs

- Viewer integrado para archivos PDF
- Descarga directa desde la vista previa
- Información detallada del archivo
- Compartir y copiar enlaces

## 🛠️ Configuración del Proyecto

### Prerrequisitos

- Node.js (v18+)
- PHP (v8.0+)
- MySQL/MariaDB

### Instalación

1. **Instalar dependencias de Node.js**

```bash
cd "ruta/del/proyecto"
pnpm install
```

2. **Configurar la base de datos**

```sql
-- Ejecutar el script SQL
source database/reportes_innovacion.sql
```

3. **Ejecutar servidores**

**Terminal 1 - Frontend (Next.js):**

```bash
pnpm dev
```

**Terminal 2 - Backend (PHP):**

```bash
php -S localhost:8000
```

### URLs del Proyecto

- **Frontend:** http://localhost:3000
- **Backend:** http://localhost:8000
- **API:** http://localhost:8000/api/

## 📁 Estructura de Archivos

```
proyecto/
├── api/
│   ├── upload-reporte.php      # Endpoint para subir reportes
│   ├── conexion-reportes.php   # CRUD de reportes
│   └── ...
├── uploads/
│   └── reportes/              # Archivos subidos
├── src/
│   ├── views/
│   │   ├── Reportes.jsx       # Vista principal de reportes
│   │   └── Archivos.jsx       # Gestión de archivos
│   ├── services/
│   │   └── reportesService.js # Servicios API
│   └── styles/
│       └── App.css           # Estilos CSS
└── database/
    └── reportes_innovacion.sql # Esquema de la BD
```

## 🔧 Endpoints API

### POST /api/upload-reporte.php

Sube un nuevo reporte con archivo

**Parámetros:**

- `titulo` (string): Título del reporte
- `descripcion` (string): Descripción del reporte
- `archivo` (file): Archivo PDF/Excel/Word
- `usuario_id` (int): ID del usuario (opcional, default: 1)

**Respuesta:**

```json
{
  "success": true,
  "message": "Reporte subido exitosamente",
  "id": 123,
  "archivo_path": "/uploads/reportes/archivo.pdf"
}
```

### GET /api/conexion-reportes.php

Obtiene todos los reportes

**Respuesta:**

```json
[
  {
    "id": 1,
    "titulo": "Reporte Q4 2024",
    "descripcion": "Análisis trimestral",
    "autor": "Juan Pérez",
    "fecha": "2024-06-24",
    "estado": "aprobado",
    "rating": 4.8,
    "comentarios": 5,
    "vistas": 120,
    "tamaño": "2.4 MB",
    "tipo": "pdf",
    "archivo_url": "http://localhost:8000/uploads/reportes/archivo.pdf"
  }
]
```

## 💡 Uso de la Funcionalidad

### Subir un Nuevo Reporte

1. Ve a la sección **Reportes**
2. Haz clic en **"Nuevo Reporte"**
3. Completa el formulario:
   - **Título:** Nombre descriptivo del reporte
   - **Descripción:** Breve explicación del contenido
   - **Archivo:** Arrastra tu PDF/Excel/Word o haz clic para seleccionar
4. Haz clic en **"Crear Reporte"**

### Ver y Descargar Reportes

1. En la vista principal de reportes, cada card muestra:

   - Información del reporte (título, autor, fecha)
   - Estado (pendiente, revisión, aprobado)
   - Botón de **"Descargar"** (si hay archivo)
   - Botón de **"Ver"** para vista detallada

2. En la vista detallada:
   - Vista previa del PDF (si es PDF)
   - Información completa del archivo
   - Opciones de descarga y compartir

## 🔒 Validaciones Implementadas

### Tipos de Archivo Permitidos

- **.pdf** - Documentos PDF
- **.xlsx, .xls** - Archivos Excel
- **.docx, .doc** - Documentos Word

### Límites

- **Tamaño máximo:** 50MB por archivo
- **Título:** Obligatorio
- **Validación MIME type:** Verificación del tipo real del archivo

## 🚨 Solución de Problemas

### Error: "No se puede subir el archivo"

- Verifica que el directorio `/uploads/reportes/` tenga permisos de escritura
- Asegúrate de que el servidor PHP esté ejecutándose en puerto 8000

### Error: "CORS Policy"

- Los headers CORS están configurados en todos los endpoints PHP
- Verifica que ambos servidores estén ejecutándose

### Error: "Base de datos no conecta"

- Revisa las credenciales de MySQL en `conexion-reportes.php`
- Asegúrate de que la base de datos `reportes_innovacion` existe

### La vista previa de PDF no funciona

- Algunos navegadores bloquean iframes por seguridad
- Usa el botón "Descargar" como alternativa

## 📝 Próximas Mejoras

- [ ] Autenticación de usuarios
- [ ] Sistema de permisos por rol
- [ ] Thumbnail automático para documentos
- [ ] Búsqueda de texto en PDFs
- [ ] Versionado de archivos
- [ ] Notificaciones por email
- [ ] Integración con almacenamiento en la nube

## 🎉 ¡Listo para usar!

El sistema ya está completamente funcional. Puedes subir archivos PDF, Excel y Word que se guardarán en la base de datos y se mostrarán en la tabla de reportes con opciones de descarga y vista previa.
