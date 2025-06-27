<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'reportes_innovacion';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
}

// Función para obtener todas las evidencias
function getEvidencias($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT e.*, u.nombre as autor, u.avatar as autorAvatar,
                   COUNT(DISTINCT c.id) as comentarios
            FROM evidencias e
            LEFT JOIN usuarios u ON e.usuario_id = u.id
            LEFT JOIN comentarios_evidencias c ON e.id = c.evidencia_id
            GROUP BY e.id
            ORDER BY e.fecha_creacion DESC
        ");
        
        $evidencias = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Obtener comentarios recientes
            $stmtComentarios = $pdo->prepare("
                SELECT ce.comentario as texto, u.nombre as autor, u.avatar as autorAvatar,
                       TIMESTAMPDIFF(HOUR, ce.fecha_creacion, NOW()) as horas_transcurridas
                FROM comentarios_evidencias ce
                LEFT JOIN usuarios u ON ce.usuario_id = u.id
                WHERE ce.evidencia_id = ?
                ORDER BY ce.fecha_creacion DESC
                LIMIT 3
            ");
            $stmtComentarios->execute([$row['id']]);
            $comentariosRecientes = [];
            
            while ($comentario = $stmtComentarios->fetch(PDO::FETCH_ASSOC)) {
                $comentariosRecientes[] = [
                    'autor' => $comentario['autor'],
                    'autorAvatar' => $comentario['autorAvatar'],
                    'texto' => $comentario['texto'],
                    'tiempo' => 'hace ' . $comentario['horas_transcurridas'] . 'h'
                ];
            }
            
            $evidencias[] = [
                'id' => (int)$row['id'],
                'titulo' => $row['titulo'],
                'descripcion' => $row['descripcion'],
                'autor' => $row['autor'],
                'autorAvatar' => $row['autorAvatar'],
                'fecha' => date('Y-m-d H:i', strtotime($row['fecha_creacion'])),
                'imagen' => $row['imagen_path'],
                'ubicacion' => $row['ubicacion'],
                'asistentes' => (int)$row['asistentes'],
                'comentarios' => (int)$row['comentarios'],
                'comentariosRecientes' => $comentariosRecientes
            ];
        }
        
        return $evidencias;
    } catch(PDOException $e) {
        return ['error' => 'Error obteniendo evidencias: ' . $e->getMessage()];
    }
}

// Función para crear una nueva evidencia
function createEvidencia($pdo, $data) {
    try {
        // Manejar subida de imagen
        $imagenPath = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/evidencias/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . $_FILES['imagen']['name'];
            $imagenPath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $imagenPath)) {
                return ['error' => 'Error subiendo imagen'];
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO evidencias (titulo, descripcion, ubicacion, asistentes, imagen_path, usuario_id, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['titulo'],
            $data['descripcion'],
            $data['ubicacion'],
            $data['asistentes'],
            $imagenPath,
            1 // ID del usuario actual
        ]);
        
        return ['success' => true, 'message' => 'Evidencia creada correctamente'];
    } catch(PDOException $e) {
        return ['error' => 'Error creando evidencia: ' . $e->getMessage()];
    }
}

// Función para actualizar una evidencia
function updateEvidencia($pdo, $id, $data) {
    try {
        $stmt = $pdo->prepare("
            UPDATE evidencias 
            SET titulo = ?, descripcion = ?, ubicacion = ?, asistentes = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['titulo'],
            $data['descripcion'],
            $data['ubicacion'],
            $data['asistentes'],
            $id
        ]);
        
        return ['success' => true, 'message' => 'Evidencia actualizada correctamente'];
    } catch(PDOException $e) {
        return ['error' => 'Error actualizando evidencia: ' . $e->getMessage()];
    }
}

// Función para eliminar una evidencia
function deleteEvidencia($pdo, $id) {
    try {
        // Obtener información de la imagen para eliminarla
        $stmt = $pdo->prepare("SELECT imagen_path FROM evidencias WHERE id = ?");
        $stmt->execute([$id]);
        $evidencia = $stmt->fetch();
        
        if ($evidencia && $evidencia['imagen_path'] && file_exists($evidencia['imagen_path'])) {
            unlink($evidencia['imagen_path']);
        }
        
        // Eliminar comentarios relacionados
        $pdo->prepare("DELETE FROM comentarios_evidencias WHERE evidencia_id = ?")->execute([$id]);
        
        // Eliminar la evidencia
        $stmt = $pdo->prepare("DELETE FROM evidencias WHERE id = ?");
        $stmt->execute([$id]);
        
        return ['success' => true, 'message' => 'Evidencia eliminada correctamente'];
    } catch(PDOException $e) {
        return ['error' => 'Error eliminando evidencia: ' . $e->getMessage()];
    }
}

// Manejar la solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $evidencias = getEvidencias($pdo);
        echo json_encode($evidencias);
        break;
        
    case 'POST':
        $result = createEvidencia($pdo, $_POST);
        echo json_encode($result);
        break;
        
    case 'PUT':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['error' => 'ID requerido']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $result = updateEvidencia($pdo, $id, $input);
        echo json_encode($result);
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['error' => 'ID requerido']);
            break;
        }
        $result = deleteEvidencia($pdo, $id);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
