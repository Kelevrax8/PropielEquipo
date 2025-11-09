<?php
// API para verificar si un usuario ya tiene una cita en una fecha específica
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Verificar que sea una petición GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

// Incluir archivos necesarios
require_once '../../database_connection.php';
require_once '../../database_queries.php';

try {
    $db_queries = new PropielEquipoQueries();
    
    // Validar parámetros requeridos
    if (!isset($_GET['fecha'])) {
        echo json_encode(['success' => false, 'message' => 'Fecha requerida']);
        exit();
    }
    
    $fecha = $_GET['fecha'];
    $user_id = $_SESSION['user_id'];
    
    // Validar formato de fecha (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
        exit();
    }
    
    // Verificar si el usuario ya tiene una cita en esta fecha
    $hasAppointment = $db_queries->hasUserAppointmentOnDate($user_id, $fecha);
    
    // Obtener detalles de la cita existente si la hay
    $appointmentDetails = null;
    if ($hasAppointment) {
        $appointments = $db_queries->getAppointmentsByDate($fecha);
        foreach ($appointments as $appointment) {
            if ($appointment['id_usuario'] == $user_id) {
                $appointmentDetails = [
                    'horario' => $appointment['horario'],
                    'servicio' => $appointment['servicio'],
                    'estado' => $appointment['estado']
                ];
                break;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'hasAppointment' => $hasAppointment,
        'fecha' => $fecha,
        'appointmentDetails' => $appointmentDetails,
        'message' => $hasAppointment ? 'Usuario ya tiene cita en esta fecha' : 'Usuario puede reservar en esta fecha'
    ]);
    
} catch (Exception $e) {
    error_log("Error en verificación de cita existente: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor'
    ]);
}
?>
