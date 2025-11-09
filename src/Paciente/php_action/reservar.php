<?php
// Incluir archivos de configuración
require_once "../../database_connection.php";
require_once "../../database_queries.php";
session_start();

/**
 * Función para guardar el PDF de consentimiento en el servidor
 */
function savePDFConsent($pdfBase64, $filename, $cita_id, $user_id) {
    // Obtener información del paciente para el nombre del archivo
    $db_queries = new PropielEquipoQueries();
    $usuario = $db_queries->getUserById($user_id);
    
    // Crear nombre legible del paciente
    $nombrePaciente = 'usuario_' . $user_id;
    if ($usuario && isset($usuario['nombre']) && isset($usuario['apellido'])) {
        $nombreCompleto = trim($usuario['nombre'] . '_' . $usuario['apellido']);
        // Limpiar caracteres especiales para el nombre del archivo
        $nombrePaciente = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $nombreCompleto));
        // Asegurar que no esté vacío después de la limpieza
        if (empty($nombrePaciente)) {
            $nombrePaciente = 'usuario_' . $user_id;
        }
    }
    
    // Ruta de la carpeta consentimientos
    $consentDir = '../../consentimientos/';
    
    // Crear el directorio si no existe
    if (!file_exists($consentDir)) {
        if (!mkdir($consentDir, 0755, true)) {
            throw new Exception("No se pudo crear el directorio de consentimientos");
        }
    }
    
    // Verificar que el directorio sea escribible
    if (!is_writable($consentDir)) {
        throw new Exception("El directorio de consentimientos no tiene permisos de escritura");
    }
    
    // Decodificar el PDF de base64
    $pdfData = base64_decode($pdfBase64);
    if ($pdfData === false) {
        throw new Exception("Error al decodificar el PDF");
    }
    
    // Generar nombre único del archivo con nombre del paciente
    $timestamp = date('Y-m-d_H-i-s');
    $safeFilename = "consentimiento_{$nombrePaciente}_cita_{$cita_id}_{$timestamp}.pdf";
    $fullPath = $consentDir . $safeFilename;
    
    // Guardar el archivo
    $result = file_put_contents($fullPath, $pdfData);
    if ($result === false) {
        throw new Exception("Error al escribir el archivo PDF");
    }
    
    // Log de éxito
    error_log("PDF de consentimiento guardado: " . $fullPath . " para paciente: " . ($usuario['nombre'] ?? '') . " " . ($usuario['apellido'] ?? ''));
    
    return $fullPath;
}

// Verificar si se enviaron los datos desde el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Instanciar la clase de consultas
        $db_queries = new PropielEquipoQueries();
        
        // Obtener y validar los datos desde el formulario
        $id = intval($_POST['user_id'] ?? 0);
        $fecha = trim($_POST['day'] ?? '');
        $hora = trim($_POST['time'] ?? '');
        $servicio = trim($_POST['service'] ?? '');
        $id_doctor = $_POST['doctor'] ?? 'cualquiera'; // Puede ser un ID numérico o 'cualquiera'
        
        // Convertir 'cualquiera' o valores vacíos a null para asignación automática
        if ($id_doctor === 'cualquiera' || $id_doctor === '' || $id_doctor === '0') {
            $id_doctor = null;
        } else {
            $id_doctor = intval($id_doctor);
        }
        
        // Validación básica
        if (empty($id) || empty($fecha) || empty($hora) || empty($servicio)) {
            throw new Exception("Todos los campos son obligatorios");
        }
        
        // Validar que el usuario ID corresponde al usuario de la sesión (seguridad)
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $id) {
            throw new Exception("No tiene permisos para realizar esta acción");
        }
        
        // Verificar que el usuario haya firmado el consentimiento antes de procesar la reserva
        if (!$db_queries->hasUserSignedConsent($id)) {
            echo "<script>
                alert('Debes firmar el consentimiento informado antes de poder reservar citas médicas. Te redirigiremos para que puedas firmarlo.');
                window.location.replace('../../consentimientos/');
            </script>";
            exit();
        }
        
        // Validar formato de fecha (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new Exception("Formato de fecha inválido");
        }
        
        // Validar que la fecha no sea en el pasado
        if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
            throw new Exception("No se pueden crear citas en fechas pasadas");
        }
        
        // Verificar si el usuario ya tiene una cita en esta fecha (límite: 1 cita por día)
        if ($db_queries->hasUserAppointmentOnDate($id, $fecha)) {
            echo "<script>
                alert('⚠️ Ya tienes una cita reservada para el $fecha.\\n\\nPor política médica, solo se permite una cita por día por paciente.\\n\\nSi necesitas cambiar tu cita existente, puedes cancelarla desde \"Mis Citas\" y luego reagendar.');
                window.location.replace('../reservas.php');
            </script>";
            exit();
        }
        
        // Verificar disponibilidad del horario una vez más (doble verificación)
        if (!$db_queries->isTimeSlotAvailable($fecha, $hora, $servicio)) {
            // Horario no disponible - mostrar mensaje específico
            echo "<script>
                alert('⚠️ El horario seleccionado ya ha sido reservado por otro paciente para la especialidad de $servicio.\\n\\nPor favor regresa y selecciona otro horario disponible.');
                window.location.replace('../reservar.php');
            </script>";
            exit();
        }
        
        // Crear la cita con verificación integrada (incluyendo el doctor asignado)
        try {
            $result = $db_queries->createAppointment($id, $fecha, $hora, $servicio, $id_doctor);
            
            if (!$result) {
                throw new Exception("Error al guardar la cita en la base de datos");
            }
        } catch (Exception $create_error) {
            if (strpos($create_error->getMessage(), 'no está disponible') !== false) {
                // Error de disponibilidad
                echo "<script>
                    alert('⚠️ El horario seleccionado fue reservado por otro paciente mientras completabas tu reserva.\\n\\nPor favor regresa y selecciona otro horario disponible.');
                    window.location.replace('../reservar.php');
                </script>";
                exit();
            } else {
                // Otros errores
                throw $create_error;
            }
        }
        
        if ($result) {
            // Obtener el ID de la cita creada
            $database = new Database();
            $db = $database->getConnection();
            $cita_id = $db->lastInsertId();
            
            // Procesar el PDF del consentimiento si se envió
            if (isset($_POST['consent_pdf']) && isset($_POST['consent_filename'])) {
                try {
                    savePDFConsent($_POST['consent_pdf'], $_POST['consent_filename'], $cita_id, $id);
                } catch (Exception $pdf_error) {
                    error_log("Error al guardar PDF de consentimiento: " . $pdf_error->getMessage());
                    // No interrumpir el proceso si falla el PDF, la cita ya se creó
                }
            }
            
            $mensaje = "✅ ¡Cita reservada exitosamente!\\n\\nFecha: $fecha\\nHora: $hora\\nServicio: $servicio";
            echo "<script>
                alert('$mensaje');
                window.location.replace('../reservas.php');
            </script>";
            exit();
        } else {
            throw new Exception("Error al guardar la cita");
        }
        
    } catch (Exception $e) {
        // Manejo de errores
        error_log("Error en reservar.php: " . $e->getMessage());
        $mensaje = "❌ " . addslashes($e->getMessage());
        echo "<script>
            alert('$mensaje');
            window.location.replace('../reservar.php');
        </script>";
    }
}
?>