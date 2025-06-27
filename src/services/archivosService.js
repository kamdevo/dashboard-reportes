const API_BASE = "http://localhost:8000/api";

export const fetchArchivos = async () => {
  try {
    console.log("🌐 Haciendo petición a:", `${API_BASE}/archivos.php`);
    const response = await fetch(`${API_BASE}/archivos.php`);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    console.log("✅ Archivos cargados:", data.length, "archivos");
    return data;
  } catch (error) {
    console.error("❌ Error fetching archivos:", error);
    // Datos de ejemplo para desarrollo
    return [
      {
        id: 1,
        nombre: "Manual_Usuario.pdf",
        descripcion: "Manual completo del usuario",
        autor: "Ana Martínez",
        fecha: "2024-01-15",
        tipo: "pdf",
        tamaño: "2.0 MB",
        categoria: "documentos",
        archivo_url:
          "http://localhost:8000/uploads/archivos/manual_usuario.pdf",
        thumbnail: null,
        descargas: 45,
      },
      {
        id: 2,
        nombre: "Presentacion_Proyecto.pptx",
        descripcion: "Presentación del nuevo proyecto",
        autor: "Luis Pérez",
        fecha: "2024-01-14",
        tipo: "powerpoint",
        tamaño: "5.0 MB",
        categoria: "presentaciones",
        archivo_url: "http://localhost:8000/uploads/archivos/presentacion.pptx",
        thumbnail: null,
        descargas: 23,
      },
    ];
  }
};

export const uploadArchivo = async (formData) => {
  try {
    console.log("📤 Enviando archivo al servidor...");

    // Si recibimos un objeto en lugar de FormData, convertirlo
    if (!(formData instanceof FormData)) {
      const newFormData = new FormData();
      Object.keys(formData).forEach((key) => {
        newFormData.append(key, formData[key]);
      });
      formData = newFormData;
    }

    const response = await fetch(`${API_BASE}/upload-archivo.php`, {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error("Error uploading archivo:", error);
    throw error;
  }
};

export const updateArchivo = async (id, archivoData) => {
  try {
    const response = await fetch(`${API_BASE}/archivos.php`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id, ...archivoData }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error("Error updating archivo:", error);
    throw error;
  }
};

export const deleteArchivo = async (id) => {
  try {
    console.log("🗑️ Eliminando archivo con ID:", id);
    const response = await fetch(`${API_BASE}/archivos.php?id=${id}`, {
      method: "DELETE",
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    console.log("✅ Archivo eliminado:", data);
    return data;
  } catch (error) {
    console.error("Error deleting archivo:", error);
    throw error;
  }
};

export const downloadArchivo = async (id) => {
  try {
    const response = await fetch(`${API_BASE}/archivos.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ action: "download", id }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error("Error registering download:", error);
    throw error;
  }
};
