<?php
// Endpoint para cancelar citas médicas
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    // Obtener datos JSON del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['cita_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de cita requerido']);
        exit();
    }
    
    $cita_id = intval($input['cita_id']);
    $user_id = $_SESSION['user_id'];
    
    if ($cita_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de cita inválido']);
        exit();
    }
    
    // Verificar que la cita existe y pertenece al usuario
    $cita = $db_queries->getAppointmentById($cita_id);
    
    if (!$cita) {
        echo json_encode(['success' => false, 'message' => 'Cita no encontrada']);
        exit();
    }
    
    if ($cita['id_usuario'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para cancelar esta cita']);
        exit();
    }
    
    // Verificar que la cita sea futura y cumpla con el tiempo mínimo de cancelación
    $fecha_cita = $cita['fecha'];
    $hora_cita = $cita['horario'];
    
    // Configurar zona horaria para consistencia
    date_default_timezone_set('America/Mexico_City');
    
    // Crear datetime completo de la cita
    $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $fecha_cita . ' ' . $hora_cita);
    if (!$datetime_cita) {
        $datetime_cita = DateTime::createFromFormat('Y-m-d H:i:s', $fecha_cita . ' ' . $hora_cita . ':00');
    }
    
    $datetime_actual = new DateTime();
    
    if (!$datetime_cita) {
        echo json_encode(['success' => false, 'message' => 'Error al procesar la fecha y hora de la cita']);
        exit();
    }
    
    // Verificar que la cita no haya pasado ya
    if ($datetime_cita <= $datetime_actual) {
        echo json_encode(['success' => false, 'message' => 'No se pueden cancelar citas pasadas o en curso']);
        exit();
    }
    
    // Verificar que la cancelación se haga con al menos 2 horas de anticipación
    // Calcular la diferencia en horas entre ahora y la cita
    $diferencia_segundos = $datetime_cita->getTimestamp() - $datetime_actual->getTimestamp();
    $diferencia_horas = $diferencia_segundos / 3600; // Convertir a horas
    
    if ($diferencia_horas < 2) {
        $horas_restantes = round($diferencia_horas, 1);
        echo json_encode([
            'success' => false, 
            'message' => "Las citas deben cancelarse con al menos 2 horas de anticipación. Solo faltan {$horas_restantes} horas para tu cita."
        ]);
        exit();
    }
    
    // Proceder con la cancelación (eliminar la cita)
    $resultado = $db_queries->deleteAppointment($cita_id, $user_id);
    
    if ($resultado) {
        // Log de la cancelación
        error_log("Cita cancelada - ID: $cita_id, Usuario: $user_id, Fecha: $fecha_cita, Hora: $hora_cita, Servicio: " . $cita['servicio']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cita cancelada exitosamente',
            'cita_id' => $cita_id,
            'fecha' => $fecha_cita,
            'horario' => $hora_cita,
            'servicio' => $cita['servicio']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al cancelar la cita en la base de datos']);
    }
    
} catch (Exception $e) {
    error_log("Error al cancelar cita: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor'
    ]);
}
?>
