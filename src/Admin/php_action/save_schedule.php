<?php
// Save or update doctor schedule
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once '../../database_connection.php';

try {
    if (!isset($_POST['doctor_id']) || !isset($_POST['dia_semana']) || !isset($_POST['hora_inicio']) || !isset($_POST['hora_fin'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
        exit();
    }

    $doctor_id = intval($_POST['doctor_id']);
    $dia_semana = $_POST['dia_semana'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $intervalo_minutos = isset($_POST['intervalo_minutos']) ? intval($_POST['intervalo_minutos']) : 60;

    // Validar día de la semana
    $dias_validos = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    if (!in_array($dia_semana, $dias_validos)) {
        echo json_encode(['success' => false, 'message' => 'Día de semana inválido']);
        exit();
    }

    // Verificar si ya existe un horario para este doctor en este día
    $stmt_check = $conex->prepare("SELECT id_horario FROM horarios WHERE id_doctor = ? AND dia_semana = ?");
    $stmt_check->bind_param("is", $doctor_id, $dia_semana);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Actualizar horario existente
        $horario = $result_check->fetch_assoc();
        $stmt = $conex->prepare("
            UPDATE horarios
            SET hora_inicio = ?, hora_fin = ?, intervalo_minutos = ?, activo = 1
            WHERE id_horario = ?
        ");
        $stmt->bind_param("ssii", $hora_inicio, $hora_fin, $intervalo_minutos, $horario['id_horario']);
    } else {
        // Insertar nuevo horario
        $stmt = $conex->prepare("
            INSERT INTO horarios (id_doctor, dia_semana, hora_inicio, hora_fin, intervalo_minutos, activo)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->bind_param("isssi", $doctor_id, $dia_semana, $hora_inicio, $hora_fin, $intervalo_minutos);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Horario guardado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar horario']);
    }

} catch (Exception $e) {
    error_log("Error saving schedule: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>