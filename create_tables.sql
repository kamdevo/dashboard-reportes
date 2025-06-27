DROP TABLE IF EXISTS `actividad_usuarios`;
DROP TABLE IF EXISTS `calificaciones`;
DROP TABLE IF EXISTS `comentarios_evidencias`;
DROP TABLE IF EXISTS `comentarios`;
DROP TABLE IF EXISTS `archivos`;
DROP TABLE IF EXISTS `evidencias`;
DROP TABLE IF EXISTS `reportes`;
DROP TABLE IF EXISTS `usuarios`;

-- Crear tablas necesarias para el sistema de reportes

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar usuario de ejemplo si no existe
INSERT IGNORE INTO usuarios (id, nombre, email, password, rol) VALUES
(1, 'Usuario de Sistema', 'sistema@example.com', 'password_placeholder', 'admin');

-- Tabla de reportes
CREATE TABLE IF NOT EXISTS reportes (
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
CREATE TABLE IF NOT EXISTS evidencias (
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
CREATE TABLE IF NOT EXISTS archivos (
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
CREATE TABLE IF NOT EXISTS comentarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporte_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporte_id) REFERENCES reportes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de comentarios para evidencias
CREATE TABLE IF NOT EXISTS comentarios_evidencias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evidencia_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evidencia_id) REFERENCES evidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de calificaciones
CREATE TABLE IF NOT EXISTS calificaciones (
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
CREATE TABLE IF NOT EXISTS actividad_usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Insertar datos de ejemplo para reportes
INSERT IGNORE INTO reportes (titulo, descripcion, archivo_path, tipo, tamano, estado, usuario_id, vistas) VALUES 
('Análisis Q4 2024.xlsx', 'Reporte completo del rendimiento del sistema', '/uploads/reportes/analisis_q4.xlsx', 'excel', 2457600, 'aprobado', 1, 245),
('Funcionalidades.docx', 'Documentación técnica de nuevas características', '/uploads/reportes/funcionalidades.docx', 'word', 1887436, 'pendiente', 1, 156),
('Seguridad-Sistema.pdf', 'Evaluación completa de vulnerabilidades', '/uploads/reportes/seguridad.pdf', 'pdf', 3355443, 'revision', 1, 389);

-- Insertar datos de ejemplo para evidencias
INSERT IGNORE INTO evidencias (titulo, descripcion, ubicacion, asistentes, imagen_path, usuario_id) VALUES 
('Reunión de Planificación Estratégica Q1 2024', 'Sesión de planificación para el primer trimestre con todos los departamentos.', 'Sala de Juntas Principal', 12, '/uploads/evidencias/reunion_planificacion.jpg', 1),
('Workshop de Innovación Tecnológica', 'Taller sobre nuevas tecnologías y su implementación en nuestros procesos.', 'Auditorio Central', 25, '/uploads/evidencias/workshop_innovacion.jpg', 1);

-- Insertar datos de ejemplo para archivos
INSERT IGNORE INTO archivos (nombre, descripcion, archivo_path, tipo, tamano, categoria, descargas, usuario_id) VALUES 
('Manual_Usuario.pdf', 'Manual completo del usuario', '/uploads/archivos/manual_usuario.pdf', 'pdf', 2048576, 'documentos', 45, 1),
('Presentacion_Proyecto.pptx', 'Presentación del nuevo proyecto', '/uploads/archivos/presentacion.pptx', 'powerpoint', 5242880, 'presentaciones', 23, 1),
('Logo_Empresa.png', 'Logo oficial de la empresa', '/uploads/archivos/logo.png', 'image', 512000, 'imagenes', 67, 1);

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
