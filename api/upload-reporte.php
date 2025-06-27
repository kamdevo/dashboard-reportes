<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejo de preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Log para debug
$logFile = 'upload_log.txt';
$logMessage = "[" . date('Y-m-d H:i:s') . "] " .
              "POST: " . json_encode($_POST) . " | " .
              "FILES: " . json_encode($_FILES) . "\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

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
function uploadReporte($pdo) {
    try {
        // Validar que se recibió un archivo
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No se recibió archivo o hubo un error en la subida');
        }

        $archivo = $_FILES['archivo'];
        $titulo = $_POST['titulo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $usuario_id = $_POST['usuario_id'] ?? 1; // Por defecto usuario 1

        // Validaciones
        if (empty($titulo)) {
            throw new Exception('El título es requerido');
        }

        // Validar tipo de archivo
        $tiposPermitidos = ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($archivo['type'], $tiposPermitidos)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten PDF, Excel y Word');
        }

        // Validar tamaño (máximo 50MB)
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($archivo['size'] > $maxSize) {
            throw new Exception('El archivo es demasiado grande. Máximo 50MB');
        }

        // Crear directorio de uploads si no existe
        $uploadDir = '../uploads/reportes/';
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
        }

        // Insertar en base de datos
        $stmt = $pdo->prepare("
            INSERT INTO reportes (titulo, descripcion, archivo_path, tipo, tamano, usuario_id, estado)
            VALUES (?, ?, ?, ?, ?, ?, 'pendiente')
        ");

        $success = $stmt->execute([
            $titulo,
            $descripcion,
            $rutaArchivo,
            $tipo,
            $archivo['size'],
            $usuario_id
        ]);

        if ($success) {
            $reporte_id = $pdo->lastInsertId();
            return [
                'status' => 'success',
                'message' => 'Reporte subido y guardado correctamente.',
                'reporte_id' => $reporte_id
            ];
        } else {
            throw new Exception('Error al guardar el reporte en la base de datos.');
        }
    } catch (Exception $e) {
        // Loggear el error específico de la base de datos
        $logFile = 'upload_error_log.txt';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        http_response_code(500); // Internal Server Error
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// Manejar la petición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = uploadReporte($pdo);
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
?>
