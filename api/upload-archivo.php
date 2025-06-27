<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejo de preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'reportes_innovacion';
$username = 'root';
$password = 'Juanca1026';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
}

// Función para subir archivo
function uploadArchivo($pdo) {
    try {
        // Validar que se recibió un archivo
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No se recibió archivo o hubo un error en la subida');
        }

        $archivo = $_FILES['archivo'];
        $nombre = $_POST['nombre'] ?? $archivo['name'];
        $descripcion = $_POST['descripcion'] ?? '';
        $categoria = $_POST['categoria'] ?? 'general';
        $usuario_id = $_POST['usuario_id'] ?? 1;

        // Validaciones
        if (empty($nombre)) {
            throw new Exception('El nombre es requerido');
        }

        // Validar tipo de archivo (más permisivo para archivos generales)
        $tiposPermitidos = [
            'application/pdf',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed'
        ];
        
        if (!in_array($archivo['type'], $tiposPermitidos)) {
            throw new Exception('Tipo de archivo no permitido');
        }

        // Validar tamaño (máximo 100MB)
        $maxSize = 100 * 1024 * 1024; // 100MB
        if ($archivo['size'] > $maxSize) {
            throw new Exception('El archivo es demasiado grande. Máximo 100MB');
        }

        // Crear directorio de uploads si no existe
        $uploadDir = '../uploads/archivos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generar nombre único para el archivo
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
        $rutaArchivo = $uploadDir . $nombreArchivo;

        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
            throw new Exception('Error al mover el archivo');
        }

        // Determinar tipo de archivo
        $tipo = '';
        switch ($archivo['type']) {
            case 'application/pdf':
                $tipo = 'pdf';
                break;
            case 'application/vnd.ms-excel':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                $tipo = 'excel';
                break;
            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                $tipo = 'word';
                break;
            case 'application/vnd.ms-powerpoint':
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                $tipo = 'powerpoint';
                break;
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
                $tipo = 'image';
                break;
            case 'text/plain':
                $tipo = 'text';
                break;
            case 'application/zip':
            case 'application/x-rar-compressed':
                $tipo = 'archive';
                break;
            default:
                $tipo = 'other';
        }

        // Insertar en base de datos
        $stmt = $pdo->prepare("
            INSERT INTO archivos (nombre, descripcion, archivo_path, tipo, tamano, categoria, usuario_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $nombre,
            $descripcion,
            $rutaArchivo,
            $tipo,
            $archivo['size'],
            $categoria,
            $usuario_id
        ]);

        if ($success) {
            $archivo_id = $pdo->lastInsertId();
            return [
                'status' => 'success',
                'message' => 'Archivo subido y guardado correctamente.',
                'archivo_id' => $archivo_id
            ];
        } else {
            throw new Exception('Error al guardar el archivo en la base de datos.');
        }
    } catch (Exception $e) {
        http_response_code(500);
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// Manejar la petición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = uploadArchivo($pdo);
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
?>
