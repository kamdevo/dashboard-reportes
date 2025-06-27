<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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

// Función para formatear tamaño de archivo
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

// Función para obtener archivos
function getArchivos($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.nombre,
                a.descripcion,
                a.archivo_path,
                a.thumbnail_path,
                a.tipo,
                a.tamano,
                a.categoria,
                a.descargas,
                a.fecha_creacion,
                a.fecha_actualizacion,
                u.nombre as autor,
                DATE_FORMAT(a.fecha_creacion, '%Y-%m-%d') as fecha
            FROM archivos a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            ORDER BY a.fecha_creacion DESC
        ");
        
        $stmt->execute();
        $archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear los datos para el frontend
        foreach ($archivos as &$archivo) {
            $archivo['tamaño'] = formatBytes($archivo['tamano']);
            $archivo['archivo_url'] = 'http://localhost:8000' . $archivo['archivo_path'];
            
            if ($archivo['thumbnail_path']) {
                $archivo['thumbnail_url'] = 'http://localhost:8000' . $archivo['thumbnail_path'];
            }
        }
        
        return $archivos;
    } catch (Exception $e) {
        throw new Exception('Error al obtener archivos: ' . $e->getMessage());
    }
}

// Función para crear archivo
function createArchivo($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO archivos (nombre, descripcion, archivo_path, tipo, tamano, categoria, usuario_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['archivo_path'],
            $data['tipo'],
            $data['tamano'],
            $data['categoria'] ?? 'general',
            $data['usuario_id'] ?? 1
        ]);
        
        if ($success) {
            return ['status' => 'success', 'message' => 'Archivo creado correctamente', 'id' => $pdo->lastInsertId()];
        } else {
            throw new Exception('Error al crear el archivo');
        }
    } catch (Exception $e) {
        throw new Exception('Error al crear archivo: ' . $e->getMessage());
    }
}

// Función para actualizar archivo
function updateArchivo($pdo, $id, $data) {
    try {
        // Verificar que el ID es válido
        if (!$id || !is_numeric($id)) {
            throw new Exception('ID de archivo inválido');
        }
        
        // Verificar que el archivo existe
        $stmt = $pdo->prepare("SELECT * FROM archivos WHERE id = ?");
        $stmt->execute([$id]);
        $archivoExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$archivoExistente) {
            throw new Exception('Archivo no encontrado');
        }
        
        // Validar campos requeridos
        if (empty($data['nombre'])) {
            throw new Exception('El nombre es requerido');
        }
        
        // Preparar campos para actualizar
        $camposUpdate = [];
        $valores = [];
        
        if (isset($data['nombre']) && !empty($data['nombre'])) {
            $camposUpdate[] = "nombre = ?";
            $valores[] = $data['nombre'];
        }
        
        if (isset($data['descripcion'])) {
            $camposUpdate[] = "descripcion = ?";
            $valores[] = $data['descripcion'];
        }
        
        if (isset($data['categoria'])) {
            $camposUpdate[] = "categoria = ?";
            $valores[] = $data['categoria'];
        }
        
        // Agregar fecha de actualización
        $camposUpdate[] = "fecha_actualizacion = NOW()";
        
        // Verificar que hay campos para actualizar
        if (empty($camposUpdate)) {
            throw new Exception('No hay campos para actualizar');
        }
        
        // Agregar el ID al final de los valores
        $valores[] = $id;
        
        // Construir y ejecutar la consulta
        $sql = "UPDATE archivos SET " . implode(", ", $camposUpdate) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($valores);
        
        if ($result && $stmt->rowCount() > 0) {
            // Obtener el archivo actualizado
            $stmt = $pdo->prepare("
                SELECT 
                    a.id,
                    a.nombre,
                    a.descripcion,
                    a.archivo_path,
                    a.thumbnail_path,
                    a.tipo,
                    a.tamano,
                    a.categoria,
                    a.descargas,
                    a.fecha_creacion,
                    a.fecha_actualizacion,
                    u.nombre as autor
                FROM archivos a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $archivoActualizado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'status' => 'success',
                'message' => 'Archivo "' . $data['nombre'] . '" actualizado correctamente',
                'archivo' => [
                    'id' => (int)$archivoActualizado['id'],
                    'nombre' => $archivoActualizado['nombre'],
                    'descripcion' => $archivoActualizado['descripcion'],
                    'archivo_path' => $archivoActualizado['archivo_path'],
                    'thumbnail_path' => $archivoActualizado['thumbnail_path'],
                    'tipo' => $archivoActualizado['tipo'],
                    'tamano' => (int)$archivoActualizado['tamano'],
                    'categoria' => $archivoActualizado['categoria'],
                    'descargas' => (int)$archivoActualizado['descargas'],
                    'fecha_creacion' => $archivoActualizado['fecha_creacion'],
                    'fecha_actualizacion' => $archivoActualizado['fecha_actualizacion'],
                    'autor' => $archivoActualizado['autor'],
                    'fecha' => date('Y-m-d', strtotime($archivoActualizado['fecha_creacion'])),
                    'tamaño' => formatBytes($archivoActualizado['tamano']),
                    'archivo_url' => 'http://localhost:8000' . $archivoActualizado['archivo_path']
                ]
            ];
        } else {
            throw new Exception('No se realizaron cambios en el archivo');
        }
        
    } catch (Exception $e) {
        throw new Exception('Error al actualizar archivo: ' . $e->getMessage());
    }
}

// Función para eliminar archivo
function deleteArchivo($pdo, $id) {
    try {
        // Verificar que el ID es válido
        if (!$id || !is_numeric($id)) {
            throw new Exception('ID de archivo inválido');
        }
        
        // Obtener información del archivo antes de eliminarlo
        $stmt = $pdo->prepare("SELECT archivo_path, nombre FROM archivos WHERE id = ?");
        $stmt->execute([$id]);
        $archivo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$archivo) {
            throw new Exception('Archivo no encontrado');
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Eliminar registro de la base de datos
            $stmt = $pdo->prepare("DELETE FROM archivos WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if (!$result || $stmt->rowCount() === 0) {
                throw new Exception('No se pudo eliminar el archivo de la base de datos');
            }
            
            // Confirmar la transacción
            $pdo->commit();
            
            // Eliminar archivo físico después de confirmar la eliminación de la BD
            if ($archivo['archivo_path']) {
                $filePath = '..' . $archivo['archivo_path'];
                if (file_exists($filePath)) {
                    if (!unlink($filePath)) {
                        error_log("Warning: No se pudo eliminar el archivo físico: " . $filePath);
                    }
                }
            }
            
            return [
                'status' => 'success', 
                'message' => 'Archivo "' . $archivo['nombre'] . '" eliminado correctamente',
                'id' => $id
            ];
            
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $pdo->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        throw new Exception('Error al eliminar archivo: ' . $e->getMessage());
    }
}

// Función para incrementar descargas
function incrementDownload($pdo, $id) {
    try {
        $stmt = $pdo->prepare("UPDATE archivos SET descargas = descargas + 1 WHERE id = ?");
        $success = $stmt->execute([$id]);
        
        if ($success) {
            return ['status' => 'success', 'message' => 'Descarga registrada'];
        } else {
            throw new Exception('Error al registrar descarga');
        }
    } catch (Exception $e) {
        throw new Exception('Error al registrar descarga: ' . $e->getMessage());
    }
}

// Manejar las peticiones
try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $archivos = getArchivos($pdo);
            echo json_encode($archivos);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['action']) && $input['action'] === 'download') {
                $result = incrementDownload($pdo, $input['id']);
                echo json_encode($result);
            } else {
                $result = createArchivo($pdo, $input);
                echo json_encode($result);
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['error' => 'ID requerido']);
                break;
            }
            $result = updateArchivo($pdo, $id, $input);
            echo json_encode($result);
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['error' => 'ID requerido']);
                break;
            }
            $result = deleteArchivo($pdo, $id);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
