-- Base de datos para Reportes Inovación
CREATE DATABASE IF NOT EXISTS reportes_innovacion;
USE reportes_innovacion;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    biografia TEXT,
    avatar VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de reportes
CREATE TABLE reportes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    archivo_path VARCHAR(500),
    tipo VARCHAR(50) NOT NULL,
    tamano BIGINT DEFAULT 0,
    estado ENUM('pendiente', 'revision', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    usuario_id INT NOT NULL,
    vistas INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de evidencias
CREATE TABLE evidencias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    ubicacion VARCHAR(255),
    asistentes INT DEFAULT 0,
    imagen_path VARCHAR(500),
    usuario_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de archivos generales
CREATE TABLE archivos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    archivo_path VARCHAR(500) NOT NULL,
    thumbnail_path VARCHAR(500),
    tipo VARCHAR(50) NOT NULL,
    tamano BIGINT NOT NULL,
    categoria VARCHAR(100) DEFAULT 'general',
    descargas INT DEFAULT 0,
    usuario_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de comentarios para reportes
CREATE TABLE comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporte_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de comentarios para evidencias
CREATE TABLE comentarios_evidencias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evidencia_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evidencia_id) REFERENCES evidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de calificaciones
CREATE TABLE calificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporte_id INT NOT NULL,
    usuario_id INT NOT NULL,
    calificacion INT NOT NULL CHECK (calificacion >= 1 AND calificacion <= 5),
    comentario TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_rating (reporte_id, usuario_id),
    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de actividad de usuarios
CREATE TABLE actividad_usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Insertar usuario de ejemplo
INSERT INTO usuarios (nombre, email, password, biografia, avatar) VALUES 
('John Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Desarrollador Full Stack especializado en React y PHP', '/placeholder.svg?height=120&width=120');

-- Insertar datos de ejemplo para reportes
INSERT INTO reportes (titulo, descripcion, archivo_path, tipo, tamaño, estado, usuario_id, vistas) VALUES 
('Análisis Q4 2024.xlsx', 'Reporte completo del rendimiento del sistema', '/uploads/reportes/analisis_q4.xlsx', 'excel', 2457600, 'aprobado', 1, 245),
('Funcionalidades.docx', 'Documentación técnica de nuevas características', '/uploads/reportes/funcionalidades.docx', 'word', 1887436, 'pendiente', 1, 156),
('Seguridad-Sistema.pdf', 'Evaluación completa de vulnerabilidades', '/uploads/reportes/seguridad.pdf', 'pdf', 3355443, 'revision', 1, 389);

-- Insertar datos de ejemplo para evidencias
INSERT INTO evidencias (titulo, descripcion, ubicacion, asistentes, imagen_path, usuario_id) VALUES 
('Reunión de Planificación Estratégica Q1 2024', 'Sesión de planificación para el primer trimestre con todos los departamentos.', 'Sala de Juntas Principal', 12, '/uploads/evidencias/reunion_planificacion.jpg', 1),
('Workshop de Innovación Tecnológica', 'Taller sobre nuevas tecnologías y su implementación en nuestros procesos.', 'Auditorio Central', 25, '/uploads/evidencias/workshop_innovacion.jpg', 1);

-- Insertar datos de ejemplo para archivos
INSERT INTO archivos (nombre, descripcion, archivo_path, tipo, tamaño, categoria, descargas, usuario_id) VALUES 
('Manual_Usuario.pdf', 'Manual completo del usuario', '/uploads/archivos/manual_usuario.pdf', 'pdf', 2048576, 'documentos', 45, 1),
('Presentacion_Proyecto.pptx', 'Presentación del nuevo proyecto', '/uploads/archivos/presentacion.pptx', 'powerpoint', 5242880, 'presentaciones', 23, 1),
('Logo_Empresa.png', 'Logo oficial de la empresa', '/uploads/archivos/logo.png', 'image', 512000, 'imagenes', 67, 1);

-- Insertar comentarios de ejemplo
INSERT INTO comentarios (reporte_id, usuario_id, comentario) VALUES 
(1, 1, 'Excelente análisis, muy detallado y útil para la toma de decisiones.'),
(1, 1, 'Los gráficos están muy claros y fáciles de entender.'),
(2, 1, 'Falta más detalle en la sección de implementación.');

-- Insertar calificaciones de ejemplo
INSERT INTO calificaciones (reporte_id, usuario_id, calificacion, comentario) VALUES 
(1, 1, 5, 'Reporte excepcional, muy completo'),
(2, 1, 4, 'Buen contenido, pero podría mejorar'),
(3, 1, 5, 'Análisis de seguridad muy profesional');

-- Insertar actividad de ejemplo
INSERT INTO actividad_usuarios (usuario_id, accion, descripcion) VALUES 
(1, 'reporte_creado', 'Creó el reporte: Análisis Q4 2024.xlsx'),
(1, 'evidencia_creada', 'Agregó evidencia: Reunión de Planificación'),
(1, 'archivo_subido', 'Subió archivo: Manual_Usuario.pdf');

-- Crear índices para mejorar rendimiento
CREATE INDEX idx_reportes_usuario ON reportes(usuario_id);
CREATE INDEX idx_reportes_estado ON reportes(estado);
CREATE INDEX idx_reportes_fecha ON reportes(fecha_creacion);
CREATE INDEX idx_evidencias_usuario ON evidencias(usuario_id);
CREATE INDEX idx_evidencias_fecha ON evidencias(fecha_creacion);
CREATE INDEX idx_archivos_usuario ON archivos(usuario_id);
CREATE INDEX idx_archivos_categoria ON archivos(categoria);
CREATE INDEX idx_comentarios_reporte ON comentarios(reporte_id);
CREATE INDEX idx_calificaciones_reporte ON calificaciones(reporte_id);
CREATE INDEX idx_actividad_usuario ON actividad_usuarios(usuario_id);
CREATE INDEX idx_actividad_fecha ON actividad_usuarios(fecha_actividad);
