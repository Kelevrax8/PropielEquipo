<?php
/**
 * API para obtener horarios disponibles - VERSIÓN SIMPLIFICADA
 * Todos los doctores comparten las mismas horas de la clínica
 * Solo considera: horarios globales + bloqueos individuales + citas existentes
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

require_once '../../database_connection.php';

try {
    // Validar parámetros
    if (!isset($_GET['fecha']) || !isset($_GET['id_doctor'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos (fecha, id_doctor)']);
        exit();
    }
    
    $fecha = $_GET['fecha'];
    $id_doctor = intval($_GET['id_doctor']);
    
    // Validar formato de fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
        exit();
    }
    
    // Obtener día de la semana
    $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    $dia_semana = $dias[date('w', strtotime($fecha))];
    
    // Verificar fin de semana
    if ($dia_semana === 'sabado' || $dia_semana === 'domingo') {
        echo json_encode([
            'success' => true,
            'horarios' => [],
            'message' => 'No se atiende los fines de semana'
        ]);
        exit();
    }
    
    // Verificar fecha pasada
    if ($fecha < date('Y-m-d')) {
        echo json_encode([
            'success' => true,
            'horarios' => [],
            'message' => 'No se pueden reservar citas en fechas pasadas'
        ]);
        exit();
    }
    
    // 1. Obtener horarios globales de la clínica
    $stmt = $conex->prepare("
        SELECT hora_inicio, hora_fin, intervalo_minutos
        FROM horarios_clinica
        WHERE dia_semana = ? AND activo = 1
    ");
    $stmt->bind_param("s", $dia_semana);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'horarios' => [],
            'message' => 'La clínica no tiene horarios configurados para este día'
        ]);
        exit();
    }
    
    $horario_config = $result->fetch_assoc();
    $hora_inicio = $horario_config['hora_inicio'];
    $hora_fin = $horario_config['hora_fin'];
    $intervalo = $horario_config['intervalo_minutos'];
    
    // 2. Verificar bloqueos del doctor específico
    $stmt_bloqueos = $conex->prepare("
        SELECT hora_inicio, hora_fin
        FROM bloqueos_horarios
        WHERE id_doctor = ?
        AND fecha_inicio <= ?
        AND fecha_fin >= ?
        AND activo = 1
    ");
    $stmt_bloqueos->bind_param("iss", $id_doctor, $fecha, $fecha);
    $stmt_bloqueos->execute();
    $result_bloqueos = $stmt_bloqueos->get_result();
    
    $bloqueos = [];
    while ($bloqueo = $result_bloqueos->fetch_assoc()) {
        $bloqueos[] = $bloqueo;
    }
    
    // 3. Obtener citas del doctor
    $stmt_citas = $conex->prepare("
        SELECT horario
        FROM citas
        WHERE id_doctor = ?
        AND fecha = ?
        AND estado IN ('pendiente_pago', 'pendiente', 'confirmada')
    ");
    $stmt_citas->bind_param("is", $id_doctor, $fecha);
    $stmt_citas->execute();
    $result_citas = $stmt_citas->get_result();
    
    $horarios_ocupados = [];
    while ($cita = $result_citas->fetch_assoc()) {
        $horarios_ocupados[] = $cita['horario'];
    }
    
    // 4. Generar horarios disponibles
    $horarios_disponibles = [];
    $hora_actual = strtotime($hora_inicio);
    $hora_limite = strtotime($hora_fin);
    
    while ($hora_actual < $hora_limite) {
        $horario_str = date('H:i', $hora_actual);
        $hora_formato_simple = date('H:00', $hora_actual);
        
        // Verificar bloqueos
        $bloqueado = false;
        foreach ($bloqueos as $bloqueo) {
            if ($bloqueo['hora_inicio'] === null) {
                $bloqueado = true;
                break;
            }
            if ($horario_str >= $bloqueo['hora_inicio'] && $horario_str < $bloqueo['hora_fin']) {
                $bloqueado = true;
                break;
            }
        }
        
        // Verificar si está ocupado
        $ocupado = in_array($hora_formato_simple, $horarios_ocupados);
        
        // Agregar si está disponible
        if (!$bloqueado && !$ocupado) {
            $horarios_disponibles[] = [
                'horario' => $hora_formato_simple,
                'horario_display' => $horario_str,
                'disponible' => true
            ];
        }
        
        $hora_actual = strtotime("+{$intervalo} minutes", $hora_actual);
    }
    
    echo json_encode([
        'success' => true,
        'horarios' => $horarios_disponibles,
        'config' => [
            'dia_semana' => $dia_semana,
            'hora_inicio' => $hora_inicio,
            'hora_fin' => $hora_fin,
            'intervalo_minutos' => $intervalo,
            'tipo' => 'horario_clinica_global'
        ],
        'stats' => [
            'total_disponibles' => count($horarios_disponibles),
            'total_ocupados' => count($horarios_ocupados),
            'total_bloqueos' => count($bloqueos)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error al obtener horarios disponibles: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ]);
}
?>
