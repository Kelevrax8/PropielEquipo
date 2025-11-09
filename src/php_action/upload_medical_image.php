<?php
/**
 * Medical Image Upload Handler
 * PropielEquipo Medical System
 */

require_once '../database_queries.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

$db = new PropielEquipoQueries();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['rol'] ?? 3;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Debug information
    error_log("Upload attempt - POST data: " . print_r($_POST, true));
    error_log("Upload attempt - FILES data: " . print_r($_FILES, true));
    
    // Validate file upload
    if (!isset($_FILES['imagen_medica']) || $_FILES['imagen_medica']['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'El archivo es demasiado grande (límite del servidor)',
            UPLOAD_ERR_FORM_SIZE => 'El archivo es demasiado grande (límite del formulario)',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo',
            UPLOAD_ERR_EXTENSION => 'Extensión de archivo no permitida'
        ];
        
        $error_code = $_FILES['imagen_medica']['error'] ?? UPLOAD_ERR_NO_FILE;
        $error_message = $error_messages[$error_code] ?? 'Error desconocido al subir el archivo';
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit();
    }
    
    $file = $_FILES['imagen_medica'];
    $descripcion = $_POST['descripcion'] ?? '';
    $tipo_imagen = $_POST['tipo_imagen'] ?? 'general';
    $id_paciente = $_POST['id_paciente'] ?? $user_id;
    
    // Security: Patients can only upload for themselves
    if ($user_role == 3 && $id_paciente != $user_id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para subir imágenes de otros pacientes']);
        exit();
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Get file extension
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Get MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    // Fallback to $_FILES mime type if finfo fails
    if (!$file_type) {
        $file_type = $file['type'];
    }
    
    if (!in_array($file_type, $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo se permiten imágenes (JPG, PNG, GIF, WebP)']);
        exit();
    }
    
    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande. Máximo 5MB']);
        exit();
    }
    
    // Create unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = 'paciente_' . $id_paciente . '_' . time() . '_' . uniqid() . '.' . $file_extension;
    
    // Ensure upload directory exists
    $upload_dir = '../Images/ImgMedicas/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . $unique_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Save to database
        $success = $db->uploadMedicalImage($id_paciente, $unique_filename, $descripcion, $tipo_imagen);
        
        if ($success) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Imagen subida exitosamente',
                'filename' => $unique_filename
            ]);
        } else {
            // Delete file if database save failed
            unlink($upload_path);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error al mover el archivo']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Get images based on user role
    if (isset($_GET['action']) && $_GET['action'] === 'get_images') {
        
        if ($user_role == 3) { // Patient - only their own images
            $images = $db->getPatientImages($user_id);
        } elseif ($user_role == 1 || $user_role == 2) { // Doctor/Nurse - all patient images
            $images = $db->getDoctorPatientImages($user_id);
        } else {
            $images = [];
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'images' => $images]);
        
    } elseif (isset($_GET['action']) && $_GET['action'] === 'view_image' && isset($_GET['id'])) {
        
        $image_id = $_GET['id'];
        
        // Check if user can access this image
        if ($db->canAccessImage($image_id, $user_id, $user_role)) {
            // Get image info
            $images = $db->getPatientImages($user_id);
            $image = null;
            
            foreach ($images as $img) {
                if ($img['id_imagen'] == $image_id) {
                    $image = $img;
                    break;
                }
            }
            
            if ($image) {
                $image_path = '../Images/ImgMedicas/' . $image['nombre_archivo'];
                
                if (file_exists($image_path)) {
                    $image_info = getimagesize($image_path);
                    header('Content-Type: ' . $image_info['mime']);
                    readfile($image_path);
                    exit();
                }
            }
        }
        
        // If we get here, access denied or file not found
        header('HTTP/1.0 403 Forbidden');
        echo 'Acceso denegado';
        
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    
    // Handle image deletion
    parse_str(file_get_contents("php://input"), $delete_data);
    $image_id = $delete_data['id'] ?? null;
    
    if ($image_id) {
        $success = $db->deleteMedicalImage($image_id, $user_id, $user_role);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Imagen eliminada exitosamente' : 'Error al eliminar la imagen'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID de imagen no proporcionado']);
    }
}

?>
