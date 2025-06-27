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

// Función para obtener perfil de usuario
function getUserProfile($pdo, $userId = 1) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            return ['error' => 'Usuario no encontrado'];
        }
        
        // Obtener estadísticas del usuario
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reportes WHERE usuario_id = ?");
        $stmt->execute([$userId]);
        $totalReportes = $stmt->fetch()['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM evidencias WHERE usuario_id = ?");
        $stmt->execute([$userId]);
        $totalEvidencias = $stmt->fetch()['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM archivos WHERE usuario_id = ?");
        $stmt->execute([$userId]);
        $totalArchivos = $stmt->fetch()['total'];
        
        // Calcular almacenamiento usado
        $stmt = $pdo->prepare("SELECT SUM(tamaño) as total FROM archivos WHERE usuario_id = ?");
        $stmt->execute([$userId]);
        $almacenamientoBytes = $stmt->fetch()['total'] ?? 0;
        $almacenamientoGB = round($almacenamientoBytes / (1024 * 1024 * 1024), 2);
        
        return [
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'biografia' => $usuario['biografia'],
            'avatar' => $usuario['avatar'],
            'fechaRegistro' => date('d M Y', strtotime($usuario['fecha_registro'])),
            'totalReportes' => (int)$totalReportes,
            'totalEvidencias' => (int)$totalEvidencias,
            'totalArchivos' => (int)$totalArchivos,
            'almacenamientoUsado' => $almacenamientoGB . ' GB'
        ];
    } catch(PDOException $e) {
        return ['error' => 'Error obteniendo perfil: ' . $e->getMessage()];
    }
}

// Función para actualizar perfil
function updateUserProfile($pdo, $userId, $data) {
    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ?, biografia = ? WHERE id = ?");
        $stmt->execute([$data['nombre'], $data['email'], $data['biografia'], $userId]);
        
        return ['success' => true, 'message' => 'Perfil actualizado correctamente'];
    } catch(PDOException $e) {
        return ['error' => 'Error actualizando perfil: ' . $e->getMessage()];
    }
}

// Manejar la solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $profile = getUserProfile($pdo);
        echo json_encode($profile);
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $result = updateUserProfile($pdo, 1, $input);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
