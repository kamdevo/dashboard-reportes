<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'reportes_innovacion';
$username = 'root';
$password = 'Juanca1026';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Si la base de datos no existe, intentar crearla
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        try {
            $pdo_create = new PDO("mysql:host=$host;charset=utf8", $username, $password);
            $pdo_create->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo_create->exec("CREATE DATABASE IF NOT EXISTS $dbname");
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e2) {
            die(json_encode(['error' => 'Error creando base de datos: ' . $e2->getMessage()]));
        }
    } else {
        die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage(), 'solution' => 'Verifica las credenciales de MySQL']));
    }
}

// Función para formatear el tamaño del archivo
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = array('Bytes', 'KB', 'MB', 'GB');
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// Función para obtener todos los reportes
function getReportes($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT r.*, u.nombre as autor,
                   COALESCE(AVG(c.calificacion), 0) as rating,
                   COUNT(DISTINCT co.id) as comentarios,
                   r.vistas
            FROM reportes r
            LEFT JOIN usuarios u ON r.usuario_id = u.id
            LEFT JOIN calificaciones c ON r.id = c.reporte_id
            LEFT JOIN comentarios co ON r.id = co.reporte_id
            GROUP BY r.id
            ORDER BY r.fecha_creacion DESC
        ");
        
        $reportes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $reportes[] = [
                'id' => (int)$row['id'],
                'titulo' => $row['titulo'],
                'descripcion' => $row['descripcion'],
                'autor' => $row['autor'],
                'fecha' => date('Y-m-d', strtotime($row['fecha_creacion'])),
                'estado' => $row['estado'],
                'rating' => round($row['rating'], 1),
                'comentarios' => (int)$row['comentarios'],
                'vistas' => (int)$row['vistas'],
                'tamaño' => formatFileSize($row['tamano']),
                'tipo' => $row['tipo'],
                'archivo_path' => $row['archivo_path'],
                'archivo_url' => 'http://localhost:8000' . str_replace('../', '/', $row['archivo_path'])
            ];
        }
        
        return $reportes;
    } catch(PDOException $e) {
        return ['error' => 'Error obteniendo reportes: ' . $e->getMessage()];
    }
}

// Función para crear un nuevo reporte
function createReporte($pdo, $data) {
    try {
        // Manejar subida de archivo
        $archivoPath = null;
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/reportes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . $_FILES['archivo']['name'];
            $archivoPath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $archivoPath)) {
                return ['error' => 'Error subiendo archivo'];
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO reportes (titulo, descripcion, archivo_path, tipo, tamaño, usuario_id, estado, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, 'pendiente', NOW())
        ");
        
        $fileSize = isset($_FILES['archivo']) ? $_FILES['archivo']['size'] : 0;
        $fileType = isset($data['tipo']) ? $data['tipo'] : 'pdf';
        
        $stmt->execute([
            $data['titulo'],
            $data['descripcion'],
            $archivoPath,
            $fileType,
            $fileSize,
            1 // ID del usuario actual
        ]);
        
        return ['success' => true, 'message' => 'Reporte creado correctamente'];
    } catch(PDOException $e) {
        return ['error' => 'Error creando reporte: ' . $e->getMessage()];
    }
}

// Función para actualizar un reporte
function updateReporte($pdo, $id, $data) {
    try {
        // Verificar que el ID es válido
        if (!$id || !is_numeric($id)) {
            return ['error' => 'ID de reporte inválido'];
        }
        
        // Verificar que el reporte existe
        $stmt = $pdo->prepare("SELECT * FROM reportes WHERE id = ?");
        $stmt->execute([$id]);
        $reporteExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reporteExistente) {
            return ['error' => 'Reporte no encontrado'];
        }
        
        // Validar campos requeridos
        if (empty($data['titulo'])) {
            return ['error' => 'El título es requerido'];
        }
        
        // Preparar campos para actualizar (solo los que se envían)
        $camposUpdate = [];
        $valores = [];
        
        if (isset($data['titulo']) && !empty($data['titulo'])) {
            $camposUpdate[] = "titulo = ?";
            $valores[] = $data['titulo'];
        }
        
        if (isset($data['descripcion'])) {
            $camposUpdate[] = "descripcion = ?";
            $valores[] = $data['descripcion'];
        }
        
        if (isset($data['estado'])) {
            $camposUpdate[] = "estado = ?";
            $valores[] = $data['estado'];
        }
        
        // Agregar fecha de actualización
        $camposUpdate[] = "fecha_actualizacion = NOW()";
        
        // Verificar que hay campos para actualizar
        if (empty($camposUpdate)) {
            return ['error' => 'No hay campos para actualizar'];
        }
        
        // Agregar el ID al final de los valores
        $valores[] = $id;
        
        // Construir y ejecutar la consulta
        $sql = "UPDATE reportes SET " . implode(", ", $camposUpdate) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($valores);
        
        if ($result && $stmt->rowCount() > 0) {
            // Obtener el reporte actualizado
            $stmt = $pdo->prepare("
                SELECT r.*, u.nombre as autor,
                       COALESCE(AVG(c.calificacion), 0) as rating,
                       COUNT(DISTINCT co.id) as comentarios
                FROM reportes r
                LEFT JOIN usuarios u ON r.usuario_id = u.id
                LEFT JOIN calificaciones c ON r.id = c.reporte_id
                LEFT JOIN comentarios co ON r.id = co.reporte_id
                WHERE r.id = ?
                GROUP BY r.id
            ");
            $stmt->execute([$id]);
            $reporteActualizado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true, 
                'message' => 'Reporte "' . $data['titulo'] . '" actualizado correctamente',
                'reporte' => [
                    'id' => (int)$reporteActualizado['id'],
                    'titulo' => $reporteActualizado['titulo'],
                    'descripcion' => $reporteActualizado['descripcion'],
                    'autor' => $reporteActualizado['autor'],
                    'fecha' => date('Y-m-d', strtotime($reporteActualizado['fecha_creacion'])),
                    'estado' => $reporteActualizado['estado'],
                    'rating' => round($reporteActualizado['rating'], 1),
                    'comentarios' => (int)$reporteActualizado['comentarios'],
                    'vistas' => (int)$reporteActualizado['vistas'],
                    'tamaño' => formatFileSize($reporteActualizado['tamano']),
                    'tipo' => $reporteActualizado['tipo'],
                    'archivo_path' => $reporteActualizado['archivo_path'],
                    'archivo_url' => 'http://localhost:8000' . str_replace('../', '/', $reporteActualizado['archivo_path'])
                ]
            ];
        } else {
            return ['error' => 'No se realizaron cambios en el reporte'];
        }
        
    } catch(PDOException $e) {
        return ['error' => 'Error actualizando reporte: ' . $e->getMessage()];
    }
}

// Función para eliminar un reporte
function deleteReporte($pdo, $id) {
    try {
        // Verificar que el ID es válido
        if (!$id || !is_numeric($id)) {
            return ['error' => 'ID de reporte inválido'];
        }
        
        // Verificar que el reporte existe
        $stmt = $pdo->prepare("SELECT archivo_path, titulo FROM reportes WHERE id = ?");
        $stmt->execute([$id]);
        $reporte = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reporte) {
            return ['error' => 'Reporte no encontrado'];
        }
        
        // Iniciar transacción para asegurar que todo se elimine correctamente
        $pdo->beginTransaction();
        
        try {
            // Eliminar registros relacionados primero (para evitar problemas de foreign key)
            $comentarios_deleted = $pdo->prepare("DELETE FROM comentarios WHERE reporte_id = ?")->execute([$id]);
            $calificaciones_deleted = $pdo->prepare("DELETE FROM calificaciones WHERE reporte_id = ?")->execute([$id]);
            
            // Eliminar el reporte de la base de datos
            $stmt = $pdo->prepare("DELETE FROM reportes WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if (!$result || $stmt->rowCount() === 0) {
                throw new Exception('No se pudo eliminar el reporte de la base de datos');
            }
            
            // Confirmar la transacción
            $pdo->commit();
            
            // Eliminar archivo físico después de confirmar la eliminación de la BD
            if ($reporte['archivo_path']) {
                $filePath = '..' . $reporte['archivo_path'];
                if (file_exists($filePath)) {
                    if (!unlink($filePath)) {
                        error_log("Warning: No se pudo eliminar el archivo físico: " . $filePath);
                    }
                }
            }
            
            return [
                'success' => true, 
                'message' => 'Reporte "' . $reporte['titulo'] . '" eliminado correctamente',
                'id' => $id
            ];
            
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $pdo->rollBack();
            throw $e;
        }
        
    } catch(PDOException $e) {
        return ['error' => 'Error eliminando reporte: ' . $e->getMessage()];
    } catch(Exception $e) {
        return ['error' => 'Error eliminando reporte: ' . $e->getMessage()];
    }
}

// Manejar la solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $reportes = getReportes($pdo);
        echo json_encode($reportes);
        break;
        
    case 'POST':
        $result = createReporte($pdo, $_POST);
        echo json_encode($result);
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['error' => 'ID requerido']);
            break;
        }
        $result = updateReporte($pdo, $id, $input);
        echo json_encode($result);
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['error' => 'ID requerido']);
            break;
        }
        $result = deleteReporte($pdo, $id);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
