<?php
session_start();
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

// Función para login
function login($pdo, $email, $password) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            // Crear sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            
            // Actualizar último acceso
            $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
            $stmt->execute([$usuario['id']]);
            
            return [
                'success' => true,
                'message' => 'Login exitoso',
                'usuario' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email'],
                    'avatar' => $usuario['avatar']
                ]
            ];
        } else {
            return ['error' => 'Credenciales inválidas'];
        }
    } catch(PDOException $e) {
        return ['error' => 'Error en login: ' . $e->getMessage()];
    }
}

// Función para registro
function register($pdo, $data) {
    try {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return ['error' => 'El email ya está registrado'];
        }
        
        // Crear nuevo usuario
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, fecha_registro)
            VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['nombre'],
            $data['email'],
            $hashedPassword
        ]);
        
        $userId = $pdo->lastInsertId();
        
        return [
            'success' => true,
            'message' => 'Usuario registrado correctamente',
            'usuario_id' => $userId
        ];
    } catch(PDOException $e) {
        return ['error' => 'Error en registro: ' . $e->getMessage()];
    }
}

// Función para logout
function logout() {
    session_destroy();
    return ['success' => true, 'message' => 'Logout exitoso'];
}

// Función para verificar sesión
function checkSession() {
    if (isset($_SESSION['usuario_id'])) {
        return [
            'authenticated' => true,
            'usuario' => [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'email' => $_SESSION['usuario_email']
            ]
        ];
    } else {
        return ['authenticated' => false];
    }
}

// Manejar la solicitud
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        switch ($action) {
            case 'login':
                $result = login($pdo, $input['email'], $input['password']);
                echo json_encode($result);
                break;
                
            case 'register':
                $result = register($pdo, $input);
                echo json_encode($result);
                break;
                
            case 'logout':
                $result = logout();
                echo json_encode($result);
                break;
                
            default:
                echo json_encode(['error' => 'Acción no válida']);
                break;
        }
        break;
        
    case 'GET':
        if ($action === 'check') {
            $result = checkSession();
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'Acción no válida']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
