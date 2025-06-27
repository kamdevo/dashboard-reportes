const API_BASE = "http://localhost:8000/api";

export const fetchReportes = async () => {
  try {
    console.log("🌐 Haciendo petición a:", `${API_BASE}/conexion-reportes.php`);
    const response = await fetch(`${API_BASE}/conexion-reportes.php`);
    console.log("📡 Respuesta recibida:", response.status, response.statusText);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    console.log("✅ Datos JSON parseados:", data);

    if (data.error) {
      console.error("Error del servidor:", data.error);
      throw new Error(data.error);
    }

    console.log(
      "🎯 Retornando datos reales de la API:",
      data.length,
      "reportes"
    );
    return data;
  } catch (error) {
    console.error("❌ Error fetching reportes:", error);
    // Datos de ejemplo para desarrollo en caso de error
    return [
      {
        id: 1,
        titulo: "Análisis Q4 2024.xlsx",
        descripcion: "Reporte completo del rendimiento del sistema",
        autor: "María González",
        fecha: "2024-01-15",
        estado: "aprobado",
        rating: 4.8,
        comentarios: 12,
        vistas: 245,
        tamaño: "2.4 MB",
        tipo: "excel",
      },
    ];
  }
};

export const createReporte = async (formData) => {
  try {
    console.log("📤 Enviando datos al servidor...");

    // Si recibimos un objeto en lugar de FormData, convertirlo
    if (!(formData instanceof FormData)) {
      const newFormData = new FormData();
      Object.keys(formData).forEach((key) => {
        newFormData.append(key, formData[key]);
      });
      formData = newFormData;
    }

    // Log para debug
    for (let pair of formData.entries()) {
      console.log("📝", pair[0], pair[1]);
    }

    const response = await fetch(`${API_BASE}/upload-reporte.php`, {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error creating reporte:", error);
    throw error;
  }
};

export const updateReporte = async (id, reporteData) => {
  try {
    console.log("📝 Actualizando reporte ID:", id, "con datos:", reporteData);

    // Validar datos antes de enviar
    if (!id || !reporteData.titulo || reporteData.titulo.trim() === "") {
      throw new Error("ID y título son requeridos para actualizar el reporte");
    }

    const response = await fetch(`${API_BASE}/conexion-reportes.php`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id, ...reporteData }),
    });

    console.log(
      "📡 Respuesta del servidor:",
      response.status,
      response.statusText
    );

    if (!response.ok) {
      throw new Error(`Error HTTP! status: ${response.status}`);
    }

    const data = await response.json();
    console.log("✅ Datos recibidos:", data);

    // Verificar si hay error en la respuesta
    if (data.error) {
      throw new Error(data.error);
    }

    // Verificar que la actualización fue exitosa
    if (!data.success) {
      throw new Error(
        data.message || "Error desconocido al actualizar el reporte"
      );
    }

    return data;
  } catch (error) {
    console.error("❌ Error updating reporte:", error);
    throw error;
  }
};

export const deleteReporte = async (id) => {
  try {
    console.log("🗑️ Eliminando reporte con ID:", id);
    const response = await fetch(`${API_BASE}/conexion-reportes.php?id=${id}`, {
      method: "DELETE",
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    console.log("✅ Reporte eliminado:", data);
    return data;
  } catch (error) {
    console.error("Error deleting reporte:", error);
    throw error;
  }
};

export const addComment = async (reporteId, comentario) => {
  try {
    const response = await fetch(`${API_BASE}/conexion-reportes.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "add_comment",
        reporte_id: reporteId,
        comentario,
        usuario_id: 1, // TODO: obtener del contexto de usuario
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error adding comment:", error);
    throw error;
  }
};

export const addRating = async (reporteId, calificacion, comentario = "") => {
  try {
    const response = await fetch(`${API_BASE}/conexion-reportes.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "add_rating",
        reporte_id: reporteId,
        calificacion,
        comentario,
        usuario_id: 1, // TODO: obtener del contexto de usuario
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error adding rating:", error);
    throw error;
  }
};
