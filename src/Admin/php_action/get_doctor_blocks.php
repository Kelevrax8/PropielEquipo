<?php
// Get doctor blocks from database
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../../database_connection.php';

try {
    if (!isset($_GET['doctor_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de doctor requerido']);
        exit();
    }

    $doctor_id = intval($_GET['doctor_id']);

    $stmt = $conex->prepare("
        SELECT id_bloqueo, fecha_inicio, fecha_fin, hora_inicio, hora_fin, motivo, tipo_bloqueo, activo
        FROM bloqueos_horarios
        WHERE id_doctor = ? AND activo = 1
        ORDER BY fecha_inicio DESC
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $bloqueos = [];
    while ($row = $result->fetch_assoc()) {
        $row['activo'] = (bool)$row['activo'];
        $bloqueos[] = $row;
    }

    echo json_encode([
        'success' => true,
        'bloqueos' => $bloqueos
    ]);

} catch (Exception $e) {
    error_log("Error getting doctor blocks: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>