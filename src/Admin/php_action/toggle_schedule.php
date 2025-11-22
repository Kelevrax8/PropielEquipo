<?php
// Toggle schedule active status
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../../database_connection.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || !isset($input['activo'])) {
        echo json_encode(['success' => false, 'message' => 'Parámetros faltantes']);
        exit();
    }

    $id = intval($input['id']);
    $activo = $input['activo'] ? 1 : 0;

    $stmt = $conex->prepare("UPDATE horarios SET activo = ? WHERE id_horario = ?");
    $stmt->bind_param("ii", $activo, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }

} catch (Exception $e) {
    error_log("Error toggling schedule: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>