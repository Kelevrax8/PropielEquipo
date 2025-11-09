<?php
// Endpoint para eliminar imágenes médicas
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
    
    if (!$input || !isset($input['imagen_id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de imagen requerido']);
        exit();
    }
    
    $imagen_id = intval($input['imagen_id']);
    $user_id = $_SESSION['user_id'];
    
    if ($imagen_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de imagen inválido']);
        exit();
    }
    
    // Verificar que la imagen existe y pertenece al usuario
    $imagen = $db_queries->getImageById($imagen_id);
    
    if (!$imagen) {
        echo json_encode(['success' => false, 'message' => 'Imagen no encontrada']);
        exit();
    }
    
    if ($imagen['id_paciente'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar esta imagen']);
        exit();
    }
    
    // Obtener la ruta del archivo físico
    $ruta_archivo = '../../Images/ImgMedicas/' . $imagen['nombre_archivo'];
    
    // Eliminar la imagen de la base de datos
    $resultado = $db_queries->deleteImage($imagen_id, $user_id);
    
    if ($resultado) {
        // Intentar eliminar el archivo físico si existe
        if (file_exists($ruta_archivo)) {
            if (unlink($ruta_archivo)) {
                error_log("Archivo de imagen eliminado: " . $ruta_archivo);
            } else {
                error_log("No se pudo eliminar el archivo físico: " . $ruta_archivo);
                // No fallar la operación por esto, la BD ya se actualizó
            }
        }
        
        // Log de la eliminación
        error_log("Imagen médica eliminada - ID: $imagen_id, Usuario: $user_id, Archivo: " . $imagen['nombre_archivo'] . ", Tipo: " . $imagen['tipo_imagen']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Imagen eliminada exitosamente',
            'imagen_id' => $imagen_id,
            'nombre_archivo' => $imagen['nombre_archivo'],
            'tipo_imagen' => $imagen['tipo_imagen']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la imagen de la base de datos']);
    }
    
} catch (Exception $e) {
    error_log("Error al eliminar imagen médica: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor'
    ]);
}
?>
