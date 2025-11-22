<?php
// Delete doctor schedule
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../../database_connection.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID requerido']);
        exit();
    }

    $id = intval($input['id']);

    $stmt = $conex->prepare("DELETE FROM horarios WHERE id_horario = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
    }

} catch (Exception $e) {
    error_log("Error deleting schedule: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>