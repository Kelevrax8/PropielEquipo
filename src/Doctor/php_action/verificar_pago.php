<?php
session_start();
require_once('../../database_connection.php');

// Check if user is logged in and is a doctor
if (!isset($_SESSION['telefono']) || !isset($_SESSION['especialidades'])) {
    $_SESSION['error'] = 'Acceso no autorizado';
    header('Location: ../../Landing/login.html');
    exit();
}

// Validate parameters
if (!isset($_GET['id_cita']) || !isset($_GET['accion'])) {
    $_SESSION['error'] = 'Parámetros inválidos';
    header('Location: ../especialidades/verificar_pagos.php');
    exit();
}

$id_cita = intval($_GET['id_cita']);
$accion = $_GET['accion'];
$id_doctor = $_SESSION['user_id'];
$especialidad = isset($_GET['especialidad']) ? $_GET['especialidad'] : null;

// Determine redirect URL based on specialty
$redirect_url = '../especialidades/verificar_pagos.php';
if ($especialidad) {
    $redirect_url = "../especialidades/{$especialidad}/verificar_pagos_{$especialidad}.php";
}

// Validate action
if (!in_array($accion, ['aprobar', 'rechazar'])) {
    $_SESSION['error'] = 'Acción no válida';
    header('Location: ' . $redirect_url);
    exit();
}

// Verify the appointment exists and has a payment proof
$query = "SELECT id_cita, comprobante_pago, verificado_por FROM citas WHERE id_cita = ?";
$stmt = $conex->prepare($query);
$stmt->bind_param("i", $id_cita);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Cita no encontrada';
    header('Location: ' . $redirect_url);
    exit();
}

$cita = $result->fetch_assoc();

// Check if payment proof exists
if (empty($cita['comprobante_pago'])) {
    $_SESSION['error'] = 'Esta cita no tiene comprobante de pago';
    header('Location: ' . $redirect_url);
    exit();
}

// Check if already verified
if (!empty($cita['verificado_por'])) {
    $_SESSION['error'] = 'Este pago ya fue verificado previamente';
    header('Location: ' . $redirect_url);
    exit();
}

// Process verification
if ($accion === 'aprobar') {
    // Approve payment - update appointment status to confirmed
    $query = "UPDATE citas 
              SET estado = 'confirmada', 
                  verificado_por = ?,
                  fecha_verificacion = NOW()
              WHERE id_cita = ?";
    
    $stmt = $conex->prepare($query);
    $stmt->bind_param("ii", $id_doctor, $id_cita);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Pago aprobado correctamente. La cita ha sido confirmada.';
    } else {
        $_SESSION['error'] = 'Error al aprobar el pago. Por favor intenta nuevamente.';
    }
    
} else if ($accion === 'rechazar') {
    // Reject payment - return to pending_payment status and clear proof
    $comprobante_path = '../../comprobantes_pago/' . $cita['comprobante_pago'];
    
    $query = "UPDATE citas 
              SET estado = 'pendiente_pago', 
                  comprobante_pago = NULL,
                  fecha_pago = NULL,
                  verificado_por = NULL,
                  fecha_verificacion = NULL,
                  notas_pago = CONCAT(COALESCE(notas_pago, ''), '\n[RECHAZADO] Comprobante rechazado por el doctor el ', NOW())
              WHERE id_cita = ?";
    
    $stmt = $conex->prepare($query);
    $stmt->bind_param("i", $id_cita);
    
    if ($stmt->execute()) {
        // Delete the rejected payment proof file
        if (file_exists($comprobante_path)) {
            unlink($comprobante_path);
        }
        $_SESSION['success'] = 'Pago rechazado. El paciente deberá subir un nuevo comprobante.';
    } else {
        $_SESSION['error'] = 'Error al rechazar el pago. Por favor intenta nuevamente.';
    }
}

$stmt->close();
$conex->close();

// Redirect back to verification page
header('Location: ' . $redirect_url);
exit();
?>
