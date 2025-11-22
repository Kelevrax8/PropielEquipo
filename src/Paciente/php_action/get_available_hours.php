<?php
/**
 * API para obtener horarios disponibles desde la base de datos
 * Considera:
 * - Horarios configurados por doctor
 * - Bloqueos de horarios
 * - Citas ya reservadas
 */

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

try {
    // Validar parámetros requeridos
    if (!isset($_GET['fecha']) || !isset($_GET['id_doctor'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos (fecha, id_doctor)']);
        exit();
    }
    
    $fecha = $_GET['fecha'];
    $id_doctor = intval($_GET['id_doctor']);
    $servicio = isset($_GET['servicio']) ? $_GET['servicio'] : null;
    
    // Validar formato de fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
        exit();
    }
    
    // Obtener día de la semana en español
    $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    $dia_semana = $dias[date('w', strtotime($fecha))];
    
    // Verificar que no sea fin de semana
    if ($dia_semana === 'sabado' || $dia_semana === 'domingo') {
        echo json_encode([
            'success' => true,
            'horarios' => [],
            'message' => 'No se atiende los fines de semana'
        ]);
        exit();
    }
    
    // Verificar que la fecha no sea en el pasado
    if ($fecha < date('Y-m-d')) {
        echo json_encode([
            'success' => true,
            'horarios' => [],
            'message' => 'No se pueden reservar citas en fechas pasadas'
        ]);
        exit();
    }
    
    // 1. Obtener horarios configurados para el doctor en este día
    $stmt = $conex->prepare("
        SELECT hora_inicio, hora_fin, intervalo_minutos
        FROM horarios
        WHERE id_doctor = ?
        AND dia_semana = ?
        AND activo = 1
    ");
    $stmt->bind_param("is", $id_doctor, $dia_semana);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'horarios' => [],
            'message' => 'El doctor no tiene horarios configurados para este día'
        ]);
        exit();
    }
    
    $horario_config = $result->fetch_assoc();
    $hora_inicio = $horario_config['hora_inicio'];
    $hora_fin = $horario_config['hora_fin'];
    $intervalo = $horario_config['intervalo_minutos'];
    
    // 2. Verificar bloqueos para esta fecha
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
    
    // 3. Obtener citas ya reservadas para este doctor en esta fecha
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
    
    // 4. Generar lista de horarios disponibles
    $horarios_disponibles = [];
    $hora_actual = strtotime($hora_inicio);
    $hora_limite = strtotime($hora_fin);
    
    while ($hora_actual < $hora_limite) {
        $horario_str = date('H:i', $hora_actual);
        $hora_formato_simple = date('H:00', $hora_actual); // Para compatibilidad con sistema actual
        
        // Verificar si este horario está bloqueado
        $bloqueado = false;
        foreach ($bloqueos as $bloqueo) {
            // Si el bloqueo no tiene horas específicas, bloquea todo el día
            if ($bloqueo['hora_inicio'] === null) {
                $bloqueado = true;
                break;
            }
            // Si el horario está dentro del rango del bloqueo
            if ($horario_str >= $bloqueo['hora_inicio'] && $horario_str < $bloqueo['hora_fin']) {
                $bloqueado = true;
                break;
            }
        }
        
        // Verificar si este horario ya está ocupado
        $ocupado = in_array($hora_formato_simple, $horarios_ocupados);
        
        // Solo agregar si no está bloqueado ni ocupado
        if (!$bloqueado && !$ocupado) {
            $horarios_disponibles[] = [
                'horario' => $hora_formato_simple,
                'horario_display' => $horario_str,
                'disponible' => true
            ];
        }
        
        // Avanzar al siguiente intervalo
        $hora_actual = strtotime("+{$intervalo} minutes", $hora_actual);
    }
    
    echo json_encode([
        'success' => true,
        'horarios' => $horarios_disponibles,
        'config' => [
            'dia_semana' => $dia_semana,
            'hora_inicio' => $hora_inicio,
            'hora_fin' => $hora_fin,
            'intervalo_minutos' => $intervalo
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
