<?php
session_start();
require_once '../../database_connection.php';
require_once '../../database_queries.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $especialidad_id = intval($_POST['especialidad_id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;
        
        if (!$user_id || !$especialidad_id) {
            throw new Exception("Datos inválidos");
        }
        
        // Verificar que el doctor tiene esa especialidad
        $db_queries = new PropielEquipoQueries();
        $doctor_especialidades = $db_queries->getDoctorSpecialties($user_id);
        
        $especialidad_valida = false;
        $especialidad_actual = null;
        
        foreach ($doctor_especialidades as $esp) {
            if ($esp['id_especialidad'] == $especialidad_id) {
                $especialidad_valida = true;
                $especialidad_actual = $esp;
                break;
            }
        }
        
        if (!$especialidad_valida) {
            throw new Exception("Especialidad no válida para este doctor");
        }
        
        // Actualizar especialidad actual en sesión
        $_SESSION['especialidad_actual'] = $especialidad_actual;
        
        echo json_encode([
            'success' => true,
            'message' => 'Especialidad cambiada exitosamente',
            'especialidad' => $especialidad_actual['nombre_especialidad']
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
}
?>
