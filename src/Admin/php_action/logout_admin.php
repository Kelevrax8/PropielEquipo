<?php
session_start();

// Verificar sesión de administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    header('Location: ../login_admin.php?error=unauthorized');
    exit();
}

// Destruir sesión
session_destroy();

// Limpiar cookies de sesión si existen
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirigir al login
header('Location: ../login_admin.php');
exit();
?>
