<?php
session_start();

// Verificar sesión de administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    header('Location: ../login_admin.php?error=unauthorized');
    exit();
}

require_once '../../database_connection.php';

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../configuracion_pagos.php?error=invalid_request');
    exit();
}

// Obtener datos del formulario
$banco = trim($_POST['banco'] ?? '');
$titular = trim($_POST['titular'] ?? '');
$clabe = trim($_POST['clabe'] ?? '');
$numero_cuenta = trim($_POST['numero_cuenta'] ?? '');
$referencia_info = trim($_POST['referencia_info'] ?? '');

// Validar campos obligatorios
if (empty($banco) || empty($titular) || empty($clabe) || empty($numero_cuenta)) {
    header('Location: ../configuracion_pagos.php?error=missing_fields');
    exit();
}

// Validar CLABE (debe tener exactamente 18 dígitos)
if (!preg_match('/^[0-9]{18}$/', $clabe)) {
    header('Location: ../configuracion_pagos.php?error=clabe_invalid');
    exit();
}

try {
    // Verificar si ya existe una configuración activa
    $stmt_check = $conex->query("SELECT id FROM configuracion_pagos WHERE activo = 1 LIMIT 1");
    
    if ($stmt_check->num_rows > 0) {
        // Actualizar configuración existente
        $config = $stmt_check->fetch_assoc();
        $stmt_update = $conex->prepare("
            UPDATE configuracion_pagos 
            SET banco = ?, 
                titular = ?, 
                clabe = ?, 
                numero_cuenta = ?, 
                referencia_info = ?,
                fecha_actualizacion = NOW()
            WHERE id = ?
        ");
        
        $stmt_update->bind_param(
            "sssssi",
            $banco,
            $titular,
            $clabe,
            $numero_cuenta,
            $referencia_info,
            $config['id']
        );
        
        if (!$stmt_update->execute()) {
            throw new Exception("Error al actualizar configuración");
        }
        
    } else {
        // Crear nueva configuración
        $stmt_insert = $conex->prepare("
            INSERT INTO configuracion_pagos (banco, titular, clabe, numero_cuenta, referencia_info, activo, fecha_creacion, fecha_actualizacion)
            VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");
        
        $stmt_insert->bind_param(
            "sssss",
            $banco,
            $titular,
            $clabe,
            $numero_cuenta,
            $referencia_info
        );
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Error al crear configuración");
        }
    }
    
    // Registrar en log (opcional)
    error_log("Admin " . $_SESSION['admin_id'] . " actualizó configuración de pagos");
    
    // Redirigir con éxito
    header('Location: ../configuracion_pagos.php?success=1');
    exit();
    
} catch (Exception $e) {
    error_log("Error al actualizar datos bancarios: " . $e->getMessage());
    header('Location: ../configuracion_pagos.php?error=update_failed');
    exit();
}
?>
