<?php
/**
 * Endpoint para obtener el historial médico de un paciente de Tamizaje
 */

// Configuración de errores y headers
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Función para enviar respuesta JSON
function sendResponse($success, $data = null, $message = '') {
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Verificar método de petición
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, null, 'Método no permitido');
    }
    
    // Verificar parámetros
    if (!isset($_GET['patient_id']) || empty($_GET['patient_id'])) {
        sendResponse(false, null, 'ID de paciente requerido');
    }
    
    $patient_id = intval($_GET['patient_id']);
    
    // Iniciar sesión para verificar autenticación
    session_start();
    
    // Verificar autenticación
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, null, 'Sesión no válida. Debe iniciar sesión como médico.');
    }
    
    // Verificar que el médico tenga especialidad en tamizaje
    $tiene_tamizaje = false;
    $especialidad_medico = 'General';
    
    if (isset($_SESSION['especialidades']) && is_array($_SESSION['especialidades'])) {
        foreach ($_SESSION['especialidades'] as $esp) {
            if (isset($esp['id_especialidad']) && $esp['id_especialidad'] == 3) { // ID 3 = Tamizaje
                $tiene_tamizaje = true;
                $especialidad_medico = 'Tamizaje';
                break;
            }
        }
    }
    
    // Si no tiene especialidad en tamizaje, no debería poder acceder
    if (!$tiene_tamizaje) {
        sendResponse(false, null, 'No tiene permisos para acceder a historiales de tamizaje');
    }
    
    // Incluir archivos de base de datos
    require_once __DIR__ . '/../database_connection.php';
    require_once __DIR__ . '/../database_queries.php';
    
    // Crear conexión usando la clase Database
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        sendResponse(false, null, 'Error de conexión a la base de datos');
    }
    
    // Obtener información del paciente con género desde la tabla genero
    $query_paciente = "SELECT u.user_id, u.nombre, u.apellido, u.telefono, u.edad, 
                              g.genero as genero_nombre
                       FROM usuarios u 
                       LEFT JOIN genero g ON u.genero = g.id_genero 
                       WHERE u.user_id = ? AND u.rol = 3";
    
    $stmt = $pdo->prepare($query_paciente);
    $stmt->execute([$patient_id]);
    $paciente = $stmt->fetch();
    
    if (!$paciente) {
        sendResponse(false, null, 'Paciente no encontrado');
    }
    
    // Obtener citas de tamizaje del paciente
    $query_citas = "SELECT c.id_cita, c.fecha, c.horario, c.servicio, c.estado, c.notas
                    FROM citas c
                    WHERE c.id_usuario = ? 
                    AND LOWER(c.servicio) LIKE '%tamiz%'
                    ORDER BY c.fecha DESC, c.horario DESC";
    
    $stmt = $pdo->prepare($query_citas);
    $stmt->execute([$patient_id]);
    $todas_citas = $stmt->fetchAll();
    
    $citas_tamizaje = [];
    
    // Procesar las citas obtenidas
    foreach ($todas_citas as $cita) {
        $citas_tamizaje[] = [
            'id_cita' => $cita['id_cita'] ?? 'N/A',
            'fecha' => $cita['fecha'] ?? date('Y-m-d'),
            'horario' => $cita['horario'] ?? '00:00',
            'servicio' => $cita['servicio'] ?? 'Tamizaje',
            'especialidad' => 'Tamizaje',
            'notas' => $cita['notas'] ?? '',
            'estado' => $cita['estado'] ?? 'completada',
            'doctor_nombre' => 'Dr. ' . ($_SESSION['nombre'] ?? 'No especificado')
        ];
    }
    
    // Preparar información del paciente
    $patient_info = [
        'nombre' => $paciente['nombre'] ?? 'Nombre',
        'apellido' => $paciente['apellido'] ?? 'Apellido',
        'telefono' => $paciente['telefono'] ?? 'No especificado',
        'genero' => $paciente['genero_nombre'] ?? 'No especificado'
    ];
    
    // Enviar respuesta exitosa
    sendResponse(true, [
        'patient' => $patient_info,
        'appointments' => $citas_tamizaje,
        'total_appointments' => count($citas_tamizaje)
    ], count($citas_tamizaje) > 0 ? 
        'Se encontraron ' . count($citas_tamizaje) . ' citas de tamizaje' : 
        'No se encontraron citas de tamizaje para este paciente');
    
} catch (Exception $e) {
    error_log("Error en get_patient_history_tamizaje.php: " . $e->getMessage());
    sendResponse(false, null, 'Error del servidor: ' . $e->getMessage());
}
?>
