<?php
// Save schedule block
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../../database_connection.php';

try {
    if (!isset($_POST['doctor_id']) || !isset($_POST['fecha_inicio']) || !isset($_POST['fecha_fin']) || !isset($_POST['tipo_bloqueo'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
        exit();
    }

    $doctor_id = intval($_POST['doctor_id']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $tipo_bloqueo = $_POST['tipo_bloqueo'];
    $hora_inicio = !empty($_POST['hora_inicio']) ? $_POST['hora_inicio'] : null;
    $hora_fin = !empty($_POST['hora_fin']) ? $_POST['hora_fin'] : null;
    $motivo = !empty($_POST['motivo']) ? $_POST['motivo'] : null;

    // Validar tipo de bloqueo
    $tipos_validos = ['vacaciones', 'conferencia', 'urgencia', 'personal', 'otro'];
    if (!in_array($tipo_bloqueo, $tipos_validos)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de bloqueo inválido']);
        exit();
    }

    // Validar que fecha_fin >= fecha_inicio
    if ($fecha_fin < $fecha_inicio) {
        echo json_encode(['success' => false, 'message' => 'La fecha de fin debe ser mayor o igual a la fecha de inicio']);
        exit();
    }

    $stmt = $conex->prepare("
        INSERT INTO bloqueos_horarios (id_doctor, fecha_inicio, fecha_fin, hora_inicio, hora_fin, motivo, tipo_bloqueo, activo)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->bind_param("issssss", $doctor_id, $fecha_inicio, $fecha_fin, $hora_inicio, $hora_fin, $motivo, $tipo_bloqueo);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Bloqueo creado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear bloqueo']);
    }

} catch (Exception $e) {
    error_log("Error saving block: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>