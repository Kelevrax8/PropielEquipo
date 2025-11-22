<?php
session_start();

// Incluir conexión a base de datos
require_once '../../database_connection.php';

// Debug mode - remove in production
$debug_mode = true;
if ($debug_mode) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login_admin.php?error=invalid_request');
    exit();
}

// Obtener credenciales del formulario
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Debug: Log the received credentials
if ($debug_mode) {
    error_log("Login attempt - Username: $username, Password length: " . strlen($password));
}

// Validar campos vacíos
if (empty($username) || empty($password)) {
    if ($debug_mode) error_log("Login failed - Empty fields");
    header('Location: ../login_admin.php?error=empty_fields');
    exit();
}

try {
    // Buscar usuario administrador
    $stmt = $conex->prepare("
        SELECT user_id, nombre, apellido, telefono, password, rol
        FROM usuarios
        WHERE telefono = ? AND rol = 4
    ");
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($debug_mode) {
        error_log("Query executed - Rows found: " . $result->num_rows);
    }
    
    // Verificar si existe el usuario
    if ($result->num_rows === 0) {
        if ($debug_mode) error_log("Login failed - User not found");
        header('Location: ../login_admin.php?error=invalid_credentials&debug=user_not_found');
        exit();
    }
    
    $admin = $result->fetch_assoc();
    
    if ($debug_mode) {
        error_log("User found - ID: " . $admin['user_id'] . ", Telefono: " . $admin['telefono']);
    }
    
    // Verificar contraseña
    if (!password_verify($password, $admin['password'])) {
        if ($debug_mode) {
            error_log("Login failed - Invalid password");
            error_log("Password hash in DB: " . $admin['password']);
        }
        header('Location: ../login_admin.php?error=invalid_credentials&debug=invalid_password');
        exit();
    }
    
    if ($debug_mode) {
        error_log("Login successful - User ID: " . $admin['user_id']);
    }
    
    // Credenciales correctas - Crear sesión de administrador
    $_SESSION['admin_id'] = $admin['user_id'];
    $_SESSION['admin_nombre'] = trim($admin['nombre'] . ' ' . $admin['apellido']);
    $_SESSION['admin_telefono'] = $admin['telefono'];
    $_SESSION['admin_rol'] = $admin['rol'];
    $_SESSION['admin_login_time'] = time();
    
    // Registrar último acceso
    $update_stmt = $conex->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE user_id = ?");
    $update_stmt->bind_param("i", $admin['user_id']);
    $update_stmt->execute();
    
    // Redirigir al dashboard
    header('Location: ../dashboard_admin.php');
    exit();
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en login admin: " . $e->getMessage());
    if ($debug_mode) {
        error_log("Stack trace: " . $e->getTraceAsString());
    }
    
    header('Location: ../login_admin.php?error=system_error&debug=' . urlencode($e->getMessage()));
    exit();
}
?>
