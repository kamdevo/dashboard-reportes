"use client";

import { useState, useEffect } from "react";
import Card from "../components/Card";
import Modal from "../components/Modal";
import FilterBar from "../components/FilterBar";
import {
  fetchReportes,
  createReporte,
  updateReporte,
  deleteReporte,
  addComment,
  addRating,
} from "../services/reportesService";

// Función helper para clases de estado
const getStatusClass = (estado) => {
  switch (estado) {
    case "aprobado":
      return "status-approved";
    case "pendiente":
      return "status-pending";
    case "revision":
      return "status-review";
    default:
      return "status-default";
  }
};

// Función helper para iconos de estado
const getStatusIcon = (estado) => {
  switch (estado) {
    case "aprobado":
      return "✅";
    case "pendiente":
      return "⏳";
    case "revision":
      return "👁️";
    default:
      return "📄";
  }
};

const Reportes = () => {
  const [reportes, setReportes] = useState([]);
  const [filteredReportes, setFilteredReportes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedReporte, setSelectedReporte] = useState(null);
  const [modals, setModals] = useState({
    add: false,
    edit: false,
    view: false,
    comment: false,
    rating: false,
    delete: false,
  });

  useEffect(() => {
    loadReportes();
  }, []);

  const loadReportes = async () => {
    try {
      console.log("🔄 Cargando reportes...");
      const data = await fetchReportes();
      console.log("✅ Reportes cargados:", data.length, "reportes");
      console.log("📊 Datos:", data);
      setReportes(data);
      setFilteredReportes(data);
    } catch (error) {
      console.error("❌ Error cargando reportes:", error);
    } finally {
      setLoading(false);
    }
  };

  const openModal = (modalName, reporte = null) => {
    setSelectedReporte(reporte);
    setModals({ ...modals, [modalName]: true });
  };

  const closeModal = (modalName) => {
    setModals({ ...modals, [modalName]: false });
    setSelectedReporte(null);
  };

  const handleCreateReporte = async (formData) => {
    try {
      setLoading(true);
      console.log("📤 Enviando datos al backend...");
      await createReporte(formData);
      console.log("✅ Reporte creado exitosamente");
      closeModal("add");
      await loadReportes();
    } catch (error) {
      console.error("❌ Error al crear reporte:", error);
      alert("Error al crear el reporte. Por favor intenta de nuevo.");
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateSubmit = async (reporteData) => {
    try {
      setLoading(true);
      console.log("🔄 Actualizando reporte:", selectedReporte.id, reporteData);

      const response = await updateReporte(selectedReporte.id, reporteData);
      console.log("✅ Reporte actualizado:", response);

      // Mostrar mensaje de éxito
      if (response.success) {
        alert(`✅ ${response.message}`);
      }

      closeModal("edit");
      await loadReportes();
    } catch (error) {
      console.error("❌ Error actualizando reporte:", error);
      alert(
        `❌ Error al actualizar el reporte: ${
          error.message || "Por favor intenta de nuevo."
        }`
      );
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteConfirm = async () => {
    try {
      setLoading(true);
      await deleteReporte(selectedReporte.id);
      closeModal("delete");
      await loadReportes();
    } catch (error) {
      console.error("Failed to delete report", error);
      alert("Error al eliminar el reporte. Por favor intenta de nuevo.");
    } finally {
      setLoading(false);
    }
  };

  const handleFilter = (filters) => {
    let filtered = reportes;

    if (filters.search) {
      filtered = filtered.filter(
        (reporte) =>
          reporte.titulo.toLowerCase().includes(filters.search.toLowerCase()) ||
          reporte.autor.toLowerCase().includes(filters.search.toLowerCase())
      );
    }

    if (filters.status && filters.status !== "todos") {
      filtered = filtered.filter(
        (reporte) => reporte.estado === filters.status
      );
    }

    if (filters.dateFrom) {
      filtered = filtered.filter(
        (reporte) => new Date(reporte.fecha) >= new Date(filters.dateFrom)
      );
    }

    setFilteredReportes(filtered);
  };

  if (loading) {
    return <div className="loading">Cargando reportes...</div>;
  }

  return (
    <div className="reportes">
      <div className="reportes-header">
        <div className="header-content">
          <h1>Gestión de Reportes</h1>
          <p>Administra y evalúa todos los reportes del sistema</p>
        </div>
        <button className="btn btn-primary" onClick={() => openModal("add")}>
          <span className="btn-icon">➕</span>
          Nuevo Reporte
        </button>
      </div>

      <FilterBar onFilter={handleFilter} />

      <div className="reportes-grid">
        {filteredReportes.map((reporte) => (
          <Card key={reporte.id} className="reporte-card">
            <div className="reporte-header">
              <div className="file-icon">
                {reporte.tipo === "pdf"
                  ? "📄"
                  : reporte.tipo === "excel"
                  ? "📊"
                  : reporte.tipo === "word"
                  ? "📝"
                  : "📁"}
              </div>
              <div className="reporte-info">
                <h3>{reporte.titulo}</h3>
                <p className="reporte-description">{reporte.descripcion}</p>
              </div>
            </div>

            <div className="reporte-meta">
              <div className="meta-row">
                <span className="meta-label">Autor:</span>
                <span className="meta-value">{reporte.autor}</span>
              </div>
              <div className="meta-row">
                <span className="meta-label">Fecha:</span>
                <span className="meta-value">{reporte.fecha}</span>
              </div>
              <div className="meta-row">
                <span className="meta-label">Estado:</span>
                <span
                  className={`status-badge ${getStatusClass(reporte.estado)}`}
                >
                  {getStatusIcon(reporte.estado)} {reporte.estado}
                </span>
              </div>
            </div>

            <div className="reporte-stats">
              <div className="stat">
                <span className="stat-icon">⭐</span>
                <span>{reporte.rating}</span>
              </div>
              <div className="stat">
                <span className="stat-icon">👁️</span>
                <span>{reporte.vistas}</span>
              </div>
              <div className="stat">
                <span className="stat-icon">💬</span>
                <span>{reporte.comentarios}</span>
              </div>
              <div className="stat">
                <span className="stat-icon">💾</span>
                <span>{reporte.tamaño}</span>
              </div>
            </div>

            <div className="reporte-actions">
              <button
                className="btn btn-sm btn-outline"
                onClick={() => openModal("view", reporte)}
              >
                👁️ Ver
              </button>
              <button
                className="btn btn-sm btn-outline"
                onClick={() => openModal("edit", reporte)}
              >
                ✏️ Editar
              </button>
              <button
                className="btn btn-sm btn-danger"
                onClick={() => openModal("delete", reporte)}
              >
                🗑️ Eliminar
              </button>
              {reporte.archivo_url && (
                <button
                  className="btn btn-sm btn-primary"
                  onClick={() => window.open(reporte.archivo_url, "_blank")}
                  title="Descargar archivo"
                >
                  📥 Descargar
                </button>
              )}
            </div>
          </Card>
        ))}
      </div>

      {modals.add && (
        <Modal onClose={() => closeModal("add")} title="Nuevo Reporte">
          <form
            onSubmit={(e) => {
              e.preventDefault();
              console.log("🚀 Submit evento disparado");

              const titulo = e.target.titulo.value;
              const descripcion = e.target.descripcion.value;
              const archivo = e.target.archivo.files[0];

              console.log("📝 Datos del formulario:", {
                titulo,
                descripcion,
                archivo,
              });

              if (!archivo) {
                alert("Por favor selecciona un archivo");
                return;
              }

              const formData = new FormData();
              formData.append("titulo", titulo);
              formData.append("descripcion", descripcion);
              formData.append("archivo", archivo);
              formData.append("usuario_id", "1");

              console.log("� FormData creado:", Array.from(formData.entries()));

              handleCreateReporte(formData);
            }}
          >
            <div className="form-group">
              <label htmlFor="titulo">Título</label>
              <input type="text" id="titulo" name="titulo" required />
            </div>
            <div className="form-group">
              <label htmlFor="descripcion">Descripción</label>
              <textarea id="descripcion" name="descripcion" required></textarea>
            </div>
            <div className="form-group">
              <label htmlFor="archivo">Archivo</label>
              <input
                type="file"
                id="archivo"
                name="archivo"
                required
                accept=".pdf,.xlsx,.xls,.docx,.doc"
                onChange={(e) =>
                  console.log("📁 Archivo seleccionado:", e.target.files[0])
                }
              />
            </div>
            <div className="form-actions">
              <button
                type="button"
                className="btn btn-secondary"
                onClick={() => closeModal("add")}
              >
                Cancelar
              </button>
              <button
                type="submit"
                className="btn btn-primary"
                disabled={loading}
              >
                {loading ? "Subiendo..." : "Crear Reporte"}
              </button>
            </div>
          </form>
        </Modal>
      )}

      {selectedReporte && modals.view && (
        <ViewReporteModal
          reporte={selectedReporte}
          onClose={() => closeModal("view")}
        />
      )}

      {selectedReporte && modals.edit && (
        <EditReporteModal
          reporte={selectedReporte}
          onSave={handleUpdateSubmit}
          onClose={() => closeModal("edit")}
        />
      )}

      {selectedReporte && modals.delete && (
        <DeleteConfirmModal
          reporte={selectedReporte}
          onConfirm={handleDeleteConfirm}
          onClose={() => closeModal("delete")}
        />
      )}
    </div>
  );
};

// Componentes de Modales
const AddReporteModal = ({ onSave, onClose }) => {
  const [formData, setFormData] = useState({
    titulo: "",
    descripcion: "",
    archivo: null,
  });
  const [dragActive, setDragActive] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!formData.archivo) {
      alert("Por favor selecciona un archivo");
      return;
    }

    setLoading(true);
    try {
      await onSave(formData);
    } catch (error) {
      console.error("Error al subir reporte:", error);
      alert("Error al subir el reporte. Por favor intenta de nuevo.");
    } finally {
      setLoading(false);
    }
  };

  const handleDrag = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === "dragenter" || e.type === "dragover") {
      setDragActive(true);
    } else if (e.type === "dragleave") {
      setDragActive(false);
    }
  };

  const handleDrop = (e) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);

    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      const file = e.dataTransfer.files[0];

      // Validar tipo de archivo
      const allowedTypes = [
        "application/pdf",
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
      ];
      if (!allowedTypes.includes(file.type)) {
        alert("Solo se permiten archivos PDF, Excel y Word");
        return;
      }

      setFormData({
        ...formData,
        archivo: file,
        titulo: formData.titulo || file.name.replace(/\.[^/.]+$/, ""), // Quitar extensión
      });
    }
  };

  const handleFileSelect = (e) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];

      // Validar tipo de archivo
      const allowedTypes = [
        "application/pdf",
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
      ];
      if (!allowedTypes.includes(file.type)) {
        alert("Solo se permiten archivos PDF, Excel y Word");
        e.target.value = ""; // Limpiar input
        return;
      }

      setFormData({
        ...formData,
        archivo: file,
        titulo: formData.titulo || file.name.replace(/\.[^/.]+$/, ""), // Quitar extensión
      });
    }
  };

  const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return (
      Number.parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
    );
  };

  return (
    <Modal onClose={onClose} title="Nuevo Reporte">
      <form onSubmit={handleSubmit} className="reporte-form">
        <div className="form-group">
          <label>Título del Reporte</label>
          <input
            type="text"
            value={formData.titulo}
            onChange={(e) =>
              setFormData({ ...formData, titulo: e.target.value })
            }
            placeholder="Ingresa un título descriptivo"
            required
          />
        </div>

        <div className="form-group">
          <label>Descripción</label>
          <textarea
            value={formData.descripcion}
            onChange={(e) =>
              setFormData({ ...formData, descripcion: e.target.value })
            }
            rows="3"
            placeholder="Describe brevemente el contenido del reporte"
          />
        </div>

        <div className="form-group">
          <label>Archivo del Reporte</label>
          <div
            className={`file-upload-area ${dragActive ? "drag-active" : ""}`}
            onDragEnter={handleDrag}
            onDragLeave={handleDrag}
            onDragOver={handleDrag}
            onDrop={handleDrop}
          >
            <input
              type="file"
              onChange={handleFileSelect}
              className="file-input"
              id="reporte-upload"
              accept=".pdf,.xlsx,.xls,.docx,.doc"
            />
            <label htmlFor="reporte-upload" className="file-upload-label">
              <div className="upload-icon">📤</div>
              <div className="upload-text">
                {formData.archivo ? (
                  <div className="file-selected">
                    <p>
                      <strong>{formData.archivo.name}</strong>
                    </p>
                    <p className="file-size">
                      {formatFileSize(formData.archivo.size)}
                    </p>
                    <p className="file-type">
                      {formData.archivo.type.includes("pdf")
                        ? "📄 PDF"
                        : formData.archivo.type.includes("excel") ||
                          formData.archivo.type.includes("spreadsheet")
                        ? "📊 Excel"
                        : formData.archivo.type.includes("word") ||
                          formData.archivo.type.includes("document")
                        ? "📝 Word"
                        : "📁 Documento"}
                    </p>
                  </div>
                ) : (
                  <>
                    <p>Arrastra tu archivo aquí o haz clic para seleccionar</p>
                    <p className="upload-hint">
                      Se aceptan archivos PDF, Excel y Word (máximo 50MB)
                    </p>
                  </>
                )}
              </div>
            </label>
          </div>
        </div>

        <div className="form-actions">
          <button
            type="button"
            className="btn btn-secondary"
            onClick={onClose}
            disabled={loading}
          >
            Cancelar
          </button>
          <button type="submit" className="btn btn-primary" disabled={loading}>
            {loading ? "Subiendo..." : "Crear Reporte"}
          </button>
        </div>
      </form>
    </Modal>
  );
};

const EditReporteModal = ({ reporte, onSave, onClose }) => {
  const [formData, setFormData] = useState({
    titulo: reporte.titulo || "",
    descripcion: reporte.descripcion || "",
    estado: reporte.estado || "pendiente",
  });
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const validateForm = () => {
    const newErrors = {};

    if (!formData.titulo.trim()) {
      newErrors.titulo = "El título es requerido";
    } else if (formData.titulo.trim().length < 3) {
      newErrors.titulo = "El título debe tener al menos 3 caracteres";
    }

    if (formData.descripcion && formData.descripcion.length > 500) {
      newErrors.descripcion = "La descripción no puede exceder 500 caracteres";
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    setLoading(true);
    try {
      await onSave(formData);
    } catch (error) {
      console.error("Error al actualizar reporte:", error);
      setErrors({
        general: "Error al actualizar el reporte. Por favor intenta de nuevo.",
      });
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (field, value) => {
    setFormData({ ...formData, [field]: value });
    // Limpiar error del campo cuando el usuario empieza a escribir
    if (errors[field]) {
      setErrors({ ...errors, [field]: "" });
    }
  };

  return (
    <Modal onClose={onClose} title={`Editar Reporte: ${reporte.titulo}`}>
      <form onSubmit={handleSubmit} className="reporte-form">
        {errors.general && (
          <div className="error-message">⚠️ {errors.general}</div>
        )}

        <div className="form-group">
          <label>Título *</label>
          <input
            type="text"
            value={formData.titulo}
            onChange={(e) => handleInputChange("titulo", e.target.value)}
            className={errors.titulo ? "error" : ""}
            placeholder="Ingresa el título del reporte"
            disabled={loading}
            required
          />
          {errors.titulo && (
            <span className="error-text">⚠️ {errors.titulo}</span>
          )}
        </div>

        <div className="form-group">
          <label>Descripción</label>
          <textarea
            value={formData.descripcion}
            onChange={(e) => handleInputChange("descripcion", e.target.value)}
            className={errors.descripcion ? "error" : ""}
            placeholder="Describe el contenido del reporte (opcional)"
            rows="4"
            maxLength="500"
            disabled={loading}
          />
          <small className="char-count">
            {formData.descripcion.length}/500 caracteres
          </small>
          {errors.descripcion && (
            <span className="error-text">⚠️ {errors.descripcion}</span>
          )}
        </div>

        <div className="form-group">
          <label>Estado del Reporte</label>
          <select
            value={formData.estado}
            onChange={(e) => handleInputChange("estado", e.target.value)}
            disabled={loading}
          >
            <option value="pendiente">📋 Pendiente</option>
            <option value="revision">👁️ En Revisión</option>
            <option value="aprobado">✅ Aprobado</option>
          </select>
        </div>

        <div className="reporte-info-preview">
          <h4>Vista Previa:</h4>
          <div className="preview-card">
            <strong>{formData.titulo || "Sin título"}</strong>
            <p>{formData.descripcion || "Sin descripción"}</p>
            <span className={`status-badge ${getStatusClass(formData.estado)}`}>
              {getStatusIcon(formData.estado)} {formData.estado}
            </span>
          </div>
        </div>

        <div className="form-actions">
          <button
            type="button"
            className="btn btn-secondary"
            onClick={onClose}
            disabled={loading}
          >
            Cancelar
          </button>
          <button
            type="submit"
            className="btn btn-primary"
            disabled={loading || !formData.titulo.trim()}
          >
            {loading ? (
              <>
                <span className="spinner">⟳</span>
                Actualizando...
              </>
            ) : (
              <>💾 Guardar Cambios</>
            )}
          </button>
        </div>
      </form>
    </Modal>
  );
};

const ViewReporteModal = ({ reporte, onClose }) => (
  <Modal onClose={onClose} title="Ver Reporte" size="large">
    <div className="reporte-view">
      <div className="reporte-details">
        <h2>{reporte.titulo}</h2>
        <p className="description">{reporte.descripcion}</p>

        <div className="details-grid">
          <div className="detail-item">
            <strong>Autor:</strong> {reporte.autor}
          </div>
          <div className="detail-item">
            <strong>Fecha:</strong> {reporte.fecha}
          </div>
          <div className="detail-item">
            <strong>Estado:</strong>
            <span className={`status-badge ${getStatusClass(reporte.estado)}`}>
              {getStatusIcon(reporte.estado)} {reporte.estado}
            </span>
          </div>
          <div className="detail-item">
            <strong>Tipo:</strong> {reporte.tipo?.toUpperCase()}
          </div>
          <div className="detail-item">
            <strong>Tamaño:</strong> {reporte.tamaño}
          </div>
          <div className="detail-item">
            <strong>Rating:</strong> {reporte.rating} ⭐
          </div>
          <div className="detail-item">
            <strong>Vistas:</strong> {reporte.vistas}
          </div>
        </div>

        {/* Vista previa del archivo */}
        {reporte.archivo_url && (
          <div className="file-preview-section">
            <h3>Vista Previa</h3>
            {reporte.tipo === "pdf" ? (
              <div className="pdf-viewer">
                <iframe
                  src={`${reporte.archivo_url}#toolbar=1&navpanes=1&scrollbar=1`}
                  title="PDF Viewer"
                  width="100%"
                  height="600px"
                />
              </div>
            ) : (
              <div className="file-preview">
                <div className="file-preview-icon">
                  {reporte.tipo === "excel"
                    ? "📊"
                    : reporte.tipo === "word"
                    ? "📝"
                    : reporte.tipo === "powerpoint"
                    ? "📋"
                    : "📁"}
                </div>
                <p>Vista previa no disponible para este tipo de archivo</p>
                <p>Haz clic en "Descargar" para abrir el archivo</p>
              </div>
            )}
          </div>
        )}

        {/* Acciones */}
        <div className="reporte-actions-detailed">
          {reporte.archivo_url && (
            <button
              className="btn btn-primary"
              onClick={() => window.open(reporte.archivo_url, "_blank")}
            >
              📥 Descargar Archivo
            </button>
          )}
          <button className="btn btn-outline">📤 Compartir</button>
          <button
            className="btn btn-outline"
            onClick={() => {
              if (reporte.archivo_url) {
                navigator.clipboard.writeText(reporte.archivo_url);
                alert("Enlace copiado al portapapeles");
              }
            }}
          >
            📋 Copiar Enlace
          </button>
        </div>
      </div>
    </div>
  </Modal>
);

const CommentModal = ({ reporte, onSave, onClose }) => {
  const [comment, setComment] = useState("");

  const handleSubmit = (e) => {
    e.preventDefault();
    onSave(comment);
  };

  return (
    <Modal onClose={onClose} title="Agregar Comentario">
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label>Comentario sobre: {reporte.titulo}</label>
          <textarea
            value={comment}
            onChange={(e) => setComment(e.target.value)}
            rows="6"
            placeholder="Escribe tu comentario aquí..."
            required
          />
        </div>

        <div className="form-actions">
          <button type="button" className="btn btn-secondary" onClick={onClose}>
            Cancelar
          </button>
          <button type="submit" className="btn btn-primary">
            Enviar Comentario
          </button>
        </div>
      </form>
    </Modal>
  );
};

const RatingModal = ({ reporte, onSave, onClose }) => {
  const [rating, setRating] = useState(5);
  const [comment, setComment] = useState("");

  const handleSubmit = (e) => {
    e.preventDefault();
    onSave({ rating, comment });
  };

  return (
    <Modal onClose={onClose} title="Calificar Reporte">
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label>Calificación para: {reporte.titulo}</label>
          <div className="rating-input">
            {[1, 2, 3, 4, 5].map((star) => (
              <button
                key={star}
                type="button"
                className={`star ${star <= rating ? "active" : ""}`}
                onClick={() => setRating(star)}
              >
                ⭐
              </button>
            ))}
          </div>
          <p>Calificación: {rating} de 5 estrellas</p>
        </div>

        <div className="form-group">
          <label>Comentario (opcional)</label>
          <textarea
            value={comment}
            onChange={(e) => setComment(e.target.value)}
            rows="4"
            placeholder="Comparte tu opinión sobre este reporte..."
          />
        </div>

        <div className="form-actions">
          <button type="button" className="btn btn-secondary" onClick={onClose}>
            Cancelar
          </button>
          <button type="submit" className="btn btn-primary">
            Enviar Calificación
          </button>
        </div>
      </form>
    </Modal>
  );
};

const DeleteConfirmModal = ({ reporte, onConfirm, onClose }) => (
  <Modal onClose={onClose} title="Confirmar Eliminación" size="small">
    <div className="delete-confirm">
      <div className="warning-icon">⚠️</div>
      <h3>¿Estás seguro?</h3>
      <p>Esta acción eliminará permanentemente el reporte:</p>
      <div className="reporte-info">
        <strong>{reporte.titulo}</strong>
        <br />
        <small>
          Creado por {reporte.autor} el {reporte.fecha}
        </small>
      </div>

      <div className="form-actions">
        <button className="btn btn-secondary" onClick={onClose}>
          Cancelar
        </button>
        <button className="btn btn-danger" onClick={onConfirm}>
          Eliminar Definitivamente
        </button>
      </div>
    </div>
  </Modal>
);

export default Reportes;
