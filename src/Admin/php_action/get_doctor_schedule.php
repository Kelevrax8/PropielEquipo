<?php
// Get doctor schedule from database
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
        SELECT id_horario, dia_semana, hora_inicio, hora_fin, intervalo_minutos, activo
        FROM horarios
        WHERE id_doctor = ?
        ORDER BY 
            FIELD(dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo')
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $horarios = [];
    while ($row = $result->fetch_assoc()) {
        $row['activo'] = (bool)$row['activo'];
        $horarios[] = $row;
    }

    echo json_encode([
        'success' => true,
        'horarios' => $horarios
    ]);

} catch (Exception $e) {
    error_log("Error getting doctor schedule: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>