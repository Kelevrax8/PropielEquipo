<?php
/**
 * Servicio de Imágenes Médicas Seguro - PropielEquipo
 * Maneja el acceso autorizado a imágenes médicas con control de permisos
 */

require_once '../database_connection.php';
require_once '../database_queries.php';

session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado', 'message' => 'Debe iniciar sesión']);
    exit();
}

$db_queries = new PropielEquipoQueries();
$user_id = $_SESSION['user_id'];

// Obtener información del usuario para determinar el rol
$user_info = $db_queries->getUserById($user_id);
$user_role = $user_info['rol'] ?? 3; // Por defecto paciente

// También verificar si tiene especialidades (es médico)
$is_doctor = isset($_SESSION['especialidades']) && !empty($_SESSION['especialidades']);

// Obtener parámetros
$image_filename = $_GET['image'] ?? '';
$action = $_GET['action'] ?? 'view';

if (empty($image_filename)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Parámetro inválido', 'message' => 'Nombre de imagen requerido']);
    exit();
}

// Sanitizar nombre de archivo
$image_filename = basename($image_filename);
$image_path = __DIR__ . '/ImgMedicas/' . $image_filename;

// Verificar que el archivo existe
if (!file_exists($image_path)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No encontrado', 'message' => 'Imagen no encontrada']);
    exit();
}

try {
    // Obtener información de la imagen desde la base de datos
    $image_info = null;
    
    if ($user_role == 3 && !$is_doctor) { // Paciente - solo sus propias imágenes
        $patient_images = $db_queries->getPatientImages($user_id);
        foreach ($patient_images as $img) {
            if ($img['nombre_archivo'] === $image_filename) {
                $image_info = $img;
                break;
            }
        }
    } else { // Doctor/Enfermera/Admin - todas las imágenes
        $all_images = $db_queries->getAllMedicalImages();
        foreach ($all_images as $img) {
            if ($img['nombre_archivo'] === $image_filename) {
                $image_info = $img;
                break;
            }
        }
    }
    
    // Verificar permisos
    if (!$image_info) {
        // Log intento de acceso no autorizado
        $log_entry = date('Y-m-d H:i:s') . " - Unauthorized image access attempt by user {$user_id} for file {$image_filename}" . PHP_EOL;
        error_log($log_entry, 3, __DIR__ . '/../logs/security.log');
        
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Acceso denegado', 'message' => 'No tiene permisos para acceder a esta imagen']);
        exit();
    }
    
    // Log acceso autorizado
    $log_entry = date('Y-m-d H:i:s') . " - Authorized image access by user {$user_id} (role {$user_role}, is_doctor: " . ($is_doctor ? 'yes' : 'no') . ") for file {$image_filename}" . PHP_EOL;
    error_log($log_entry, 3, __DIR__ . '/../logs/medical_access.log');
    
    // Servir la imagen
    $image_size = getimagesize($image_path);
    $mime_type = $image_size['mime'] ?? 'application/octet-stream';
    
    // Cabeceras de seguridad
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . filesize($image_path));
    header('Cache-Control: private, no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    
    // Si es descarga, agregar header de disposición
    if ($action === 'download') {
        header('Content-Disposition: attachment; filename="' . $image_filename . '"');
    } else {
        header('Content-Disposition: inline; filename="' . $image_filename . '"');
    }
    
    // Leer y enviar el archivo
    readfile($image_path);
    
} catch (Exception $e) {
    error_log("Error serving medical image: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error interno', 'message' => 'Error al procesar la solicitud']);
}

exit();
?>
