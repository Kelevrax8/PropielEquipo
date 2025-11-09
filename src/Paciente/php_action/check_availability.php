<?php
// API para verificar disponibilidad de horarios en tiempo real
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar que sea una petición GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Incluir archivos necesarios
require_once '../../database_connection.php';
require_once '../../database_queries.php';

try {
    $db_queries = new PropielEquipoQueries();
    
    // Validar parámetros requeridos
    if (!isset($_GET['fecha']) || !isset($_GET['horario'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
        exit();
    }
    
    $fecha = $_GET['fecha'];
    $horario = $_GET['horario'];
    $servicio = isset($_GET['servicio']) ? $_GET['servicio'] : null; // Nuevo parámetro para especialidad
    
    // Validar formato de fecha (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
        exit();
    }
    
    // Validar formato de horario (HH:MM)
    if (!preg_match('/^\d{1,2}:00$/', $horario)) {
        echo json_encode(['success' => false, 'message' => 'Formato de horario inválido']);
        exit();
    }
    
    // Validar servicio si se proporciona
    if ($servicio && !in_array($servicio, ['dermatología', 'podología', 'tamiz'])) {
        echo json_encode(['success' => false, 'message' => 'Servicio no válido']);
        exit();
    }
    
    // Verificar que la fecha no sea en el pasado
    $fechaActual = date('Y-m-d');
    if ($fecha < $fechaActual) {
        echo json_encode(['success' => false, 'available' => false, 'message' => 'No se pueden reservar citas en fechas pasadas']);
        exit();
    }
    
    // Verificar que la fecha no sea fin de semana
    $diaSemana = date('w', strtotime($fecha)); // 0 = domingo, 6 = sábado
    if ($diaSemana == 0 || $diaSemana == 6) {
        echo json_encode(['success' => false, 'available' => false, 'message' => 'No se atiende los fines de semana']);
        exit();
    }
    
    // Verificar disponibilidad del horario para la especialidad específica
    $available = $db_queries->isTimeSlotAvailable($fecha, $horario, $servicio);
    
    // Obtener información adicional sobre las citas existentes en esa fecha y especialidad
    $citasDelDia = $db_queries->getAppointmentsByDateAndSpecialty($fecha, $servicio);
    $horariosOcupados = array_column($citasDelDia, 'horario');
    
    // Información adicional para debugging
    $info = [
        'success' => true,
        'available' => $available,
        'fecha' => $fecha,
        'horario' => $horario,
        'servicio' => $servicio,
        'message' => $available ? 'Horario disponible' : 'Horario ya reservado para esta especialidad',
        'horarios_ocupados_especialidad' => $horariosOcupados,
        'total_citas_especialidad' => count($citasDelDia)
    ];
    
    echo json_encode($info);
    
} catch (Exception $e) {
    error_log("Error en verificación de disponibilidad: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor'
    ]);
}
?>
