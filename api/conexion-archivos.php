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
    die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
}

// Función para obtener todos los archivos
function getArchivos($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT a.*, u.nombre as autor
            FROM archivos a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            ORDER BY a.fecha_creacion DESC
        ");
        
        $archivos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $archivos[] = [
                'id' => (int)$row['id'],
                'nombre' => $row['nombre'],
                'descripcion' => $row['descripcion'],
                'autor' => $row['autor'],
                'fecha' => date('Y-m-d', strtotime($row['fecha_creacion'])),
                'tipo' => $row['tipo'],
                'tamaño' => (int)$row['tamaño'],
                'categoria' => $row['categoria'],
                'url' => $row['archivo_path'],
                'thumbnail' => $row['thumbnail_path'],
                'descargas' => (int)$row['descargas']
            ];
        }
        
        return $archivos;
    } catch(PDOException $e) {
        return ['error' => 'Error obteniendo archivos: ' . $e->getMessage()];
    }
}

// Función para subir un nuevo archivo
function uploadArchivo($pdo, $data) {
    try {
        // Manejar subida de archivo
        $archivoPath = null;
        $thumbnailPath = null;
        
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/archivos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . $_FILES['archivo']['name'];
            $archivoPath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $archivoPath)) {
                return ['error' => 'Error subiendo archivo'];
            }
            
            // Generar thumbnail para imágenes
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $thumbnailPath = generateThumbnail($archivoPath, $uploadDir);
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO archivos (nombre, descripcion, archivo_path, thumbnail_path, tipo, tamaño, categoria, usuario_id, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $fileSize = isset($_FILES['archivo']) ? $_FILES['archivo']['size'] : 0;
        $fileType = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $archivoPath,
            $thumbnailPath,
            $fileType,
            $fileSize,
            $data['categoria'],
            1 // ID del usuario actual
        ]);
        
        return ['success' => true, 'message' => 'Archivo subido correctamente'];
    } catch(PDOException $e) {
        return ['error' => 'Error subiendo archivo: ' . $e->getMessage()];
    }
}

// Función para generar thumbnail
function generateThumbnail($sourcePath, $uploadDir) {
    try {
        $thumbnailDir = $uploadDir . 'thumbnails/';
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0777, true);
        }
        
        $fileName = basename($sourcePath);
        $thumbnailPath = $thumbnailDir . 'thumb_' . $fileName;
        
        // Crear thumbnail usando GD
        $imageInfo = getimagesize($sourcePath);
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        
        $thumbWidth = 200;
        $thumbHeight = 150;
        
        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($sourcePath);
                break;
            default:
                return null;
        }
        
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);
        
        imagejpeg($thumb, $thumbnailPath, 80);
        imagedestroy($thumb);
        imagedestroy($source);
        
        return $thumbnailPath;
    } catch (Exception $e) {
        return null;
    }
}

// Función para actualizar un archivo
function updateArchivo($pdo, $id, $data) {
    try {
        $stmt = $pdo->prepare("
            UPDATE archivos 
            SET nombre = ?, descripcion = ?, categoria = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['categoria'],
            $id
        ]);
        
        return ['success' => true, 'message' => 'Archivo actualizado correctamente'];
    } catch(PDOException $e) {
        return ['error' => 'Error actualizando archivo: ' . $e->getMessage()];
    }
}

// Función para eliminar un archivo
function deleteArchivo($pdo, $id) {
    try {
        // Obtener información del archivo para eliminarlo
        $stmt = $pdo->prepare("SELECT archivo_path, thumbnail_path FROM archivos WHERE id = ?");
        $stmt->execute([$id]);
        $archivo = $stmt->fetch();
        
        if ($archivo) {
            if ($archivo['archivo_path'] && file_exists($archivo['archivo_path'])) {
                unlink($archivo['archivo_path']);
            }
            if ($archivo['thumbnail_path'] && file_exists($archivo['thumbnail_path'])) {
                unlink($archivo['thumbnail_path']);
            }
        }
        
        // Eliminar el registro
        $stmt = $pdo->prepare("DELETE FROM archivos WHERE id = ?");
        $stmt->execute([$id]);
        
        return ['success' => true, 'message' => 'Archivo eliminado correctamente'];
    } catch(PDOException $e) {
        return ['error' => 'Error eliminando archivo: ' . $e->getMessage()];
    }
}

// Manejar la solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $archivos = getArchivos($pdo);
        echo json_encode($archivos);
        break;
        
    case 'POST':
        $result = uploadArchivo($pdo, $_POST);
        echo json_encode($result);
        break;
        
    case 'PUT':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['error' => 'ID requerido']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $result = updateArchivo($pdo, $id, $input);
        echo json_encode($result);
        break;
        
    case 'DELETE':
        $id = $_GET['id'] ?? null;
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
?>
