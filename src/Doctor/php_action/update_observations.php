<?php
/**
 * Update Appointment Observations
 * PropielEquipo Medical System
 */

session_start();

// Verificar autenticación y permisos de doctor
if (!isset($_SESSION['telefono']) || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Verificar que sea un doctor
require_once '../../database_queries.php';

try {
    $db_queries = new PropielEquipoQueries();
    $user = $db_queries->getUserById($_SESSION['user_id']);
    
    if (!$user || $user['rol'] != 1) { // rol 1 = Doctor
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado - Solo médicos']);
        exit();
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de servidor']);
    exit();
}

// Procesar la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id_cita = isset($input['id_cita']) ? intval($input['id_cita']) : null;
    $observaciones = isset($input['observaciones']) ? trim($input['observaciones']) : '';
    
    // Validar datos
    if (!$id_cita) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de cita requerido']);
        exit();
    }
    
    // Verificar que la cita existe
    $appointment = $db_queries->getAppointmentDetails($id_cita);
    if (!$appointment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Cita no encontrada']);
        exit();
    }
    
    // Actualizar observaciones
    try {
        $result = $db_queries->updateAppointmentObservations($id_cita, $observaciones, $_SESSION['user_id']);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Observaciones actualizadas correctamente',
                'observaciones' => $observaciones
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar observaciones']);
        }
        
    } catch (Exception $e) {
        error_log("Update Observations Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener detalles de una cita específica
    $id_cita = isset($_GET['id_cita']) ? intval($_GET['id_cita']) : null;
    
    if (!$id_cita) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de cita requerido']);
        exit();
    }
    
    try {
        $appointment = $db_queries->getAppointmentDetails($id_cita);
        
        if ($appointment) {
            echo json_encode([
                'success' => true,
                'appointment' => $appointment
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Cita no encontrada']);
        }
        
    } catch (Exception $e) {
        error_log("Get Appointment Details Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
