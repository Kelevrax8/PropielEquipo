<?php
// Procesar y guardar el consentimiento informado firmado
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Incluir archivos necesarios
require_once '../database_connection.php';
require_once '../database_queries.php';

try {
    $db_queries = new PropielEquipoQueries();
    $user_id = $_SESSION['user_id'];
    
    // Validar datos recibidos
    if (!isset($_POST['consent_pdf']) || !isset($_POST['consent_filename']) || !isset($_POST['user_id'])) {
        throw new Exception('Datos incompletos');
    }
    
    // Verificar que el user_id coincida con la sesión (seguridad)
    if ($_POST['user_id'] != $user_id) {
        throw new Exception('No autorizado');
    }
    
    $pdfBase64 = $_POST['consent_pdf'];
    $filename = $_POST['consent_filename'];
    
    // Validar que el PDF base64 sea válido
    $pdfData = base64_decode($pdfBase64);
    if ($pdfData === false) {
        throw new Exception('PDF inválido');
    }
    
    // Validar que sea realmente un PDF
    if (substr($pdfData, 0, 4) !== '%PDF') {
        throw new Exception('El archivo no es un PDF válido');
    }
    
    // Obtener información del usuario para el nombre del archivo
    $usuario = $db_queries->getUserById($user_id);
    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }
    
    // Crear nombre legible del paciente
    $nombrePaciente = 'usuario_' . $user_id;
    if (isset($usuario['nombre']) && isset($usuario['apellido'])) {
        $nombreCompleto = trim($usuario['nombre'] . '_' . $usuario['apellido']);
        // Limpiar caracteres especiales para el nombre del archivo
        $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $nombreCompleto));
        // Asegurar que no esté vacío después de la limpieza
        if (!empty($nombreLimpio)) {
            $nombrePaciente = $nombreLimpio;
        }
    }
    
    // Verificar que el usuario no haya firmado ya un consentimiento
    if ($db_queries->hasUserSignedConsent($user_id)) {
        echo json_encode(['success' => false, 'message' => 'Ya tienes un consentimiento firmado']);
        exit();
    }
    
    // Crear el directorio si no existe
    $consentDir = './';
    if (!file_exists($consentDir)) {
        if (!mkdir($consentDir, 0755, true)) {
            throw new Exception("No se pudo crear el directorio de consentimientos");
        }
    }
    
    // Verificar que el directorio sea escribible
    if (!is_writable($consentDir)) {
        throw new Exception("El directorio de consentimientos no tiene permisos de escritura");
    }
    
    // Generar nombre único del archivo con ID de usuario para garantizar unicidad
    $timestamp = date('Y-m-d_H-i-s');
    $safeFilename = "consentimiento_{$nombrePaciente}_usuario_{$user_id}_{$timestamp}.pdf";
    $fullPath = $consentDir . $safeFilename;
    
    // Guardar el archivo
    $result = file_put_contents($fullPath, $pdfData);
    if ($result === false) {
        throw new Exception("Error al escribir el archivo PDF");
    }
    
    // Log de éxito
    error_log("Consentimiento guardado exitosamente: " . $fullPath . " para usuario: " . $user_id . " (" . ($usuario['nombre'] ?? '') . " " . ($usuario['apellido'] ?? '') . ")");
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true, 
        'message' => 'Consentimiento guardado exitosamente',
        'filename' => $safeFilename,
        'path' => $fullPath
    ]);
    
} catch (Exception $e) {
    // Log del error
    error_log("Error al guardar consentimiento: " . $e->getMessage() . " - Usuario: " . ($user_id ?? 'desconocido'));
    
    // Respuesta de error
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
