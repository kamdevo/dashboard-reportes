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

// Función para obtener estadísticas del dashboard
function getDashboardStats($pdo) {
    try {
        // Contar total de reportes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM reportes");
        $totalReportes = $stmt->fetch()['total'];
        
        // Contar reportes aprobados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM reportes WHERE estado = 'aprobado'");
        $reportesAprobados = $stmt->fetch()['total'];
        
        // Contar evidencias
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM evidencias");
        $evidencias = $stmt->fetch()['total'];
        
        // Contar archivos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM archivos");
        $archivos = $stmt->fetch()['total'];
        
        // Contar usuarios activos (últimos 30 días)
        $stmt = $pdo->query("SELECT COUNT(DISTINCT usuario_id) as total FROM actividad_usuarios WHERE fecha_actividad >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $usuariosActivos = $stmt->fetch()['total'];
        
        return [
            'totalReportes' => (int)$totalReportes,
            'reportesAprobados' => (int)$reportesAprobados,
            'evidencias' => (int)$evidencias,
            'archivos' => (int)$archivos,
            'usuariosActivos' => (int)$usuariosActivos
        ];
    } catch(PDOException $e) {
        return ['error' => 'Error obteniendo estadísticas: ' . $e->getMessage()];
    }
}

// Manejar la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stats = getDashboardStats($pdo);
    echo json_encode($stats);
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
?>
