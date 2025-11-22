<?php
/**
 * Secure PDF Viewer for Consent Forms
 * Only authenticated doctors can view consent forms
 */
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
    http_response_code(403);
    die('Acceso denegado - Debe iniciar sesión');
}

// Permitir acceso a doctores (rol = 1) o pacientes (rol = 3)
$is_doctor = ($_SESSION['rol'] == 1);
$is_patient = ($_SESSION['rol'] == 3);

if (!$is_doctor && !$is_patient) {
    http_response_code(403);
    die('Acceso denegado - Solo personal médico o pacientes autorizados');
}

// Obtener el nombre del archivo solicitado
$filename = $_GET['file'] ?? '';

if (empty($filename)) {
    http_response_code(400);
    die('Error - Archivo no especificado');
}

// Sanitizar el nombre del archivo para prevenir ataques de directory traversal
$filename = basename($filename);

// Verificar que sea un archivo PDF
if (!preg_match('/\.pdf$/i', $filename)) {
    http_response_code(400);
    die('Error - Solo se permiten archivos PDF');
}

// Construir la ruta completa del archivo
$filepath = __DIR__ . '/' . $filename;

// Verificar que el archivo existe
if (!file_exists($filepath)) {
    http_response_code(404);
    die('Error - Archivo no encontrado');
}

// Verificar que es realmente un archivo y no un directorio
if (!is_file($filepath)) {
    http_response_code(400);
    die('Error - Ruta inválida');
}

// Si es un paciente, verificar que el archivo le pertenece
if ($is_patient) {
    $user_id = $_SESSION['user_id'];
    // Verificar que el nombre del archivo contenga el ID del usuario
    if (strpos($filename, '_usuario_' . $user_id . '_') === false) {
        http_response_code(403);
        error_log(sprintf(
            "Unauthorized PDF Access Attempt: Patient ID %d tried to access file: %s",
            $user_id,
            $filename
        ));
        die('Error - No tiene permiso para ver este documento');
    }
}

// Log de acceso (opcional, para auditoría)
$user_type = $is_doctor ? 'Doctor' : 'Patient';
error_log(sprintf(
    "PDF Access: %s ID %d (%s %s) accessed file: %s",
    $user_type,
    $_SESSION['user_id'],
    $_SESSION['nombre'] ?? 'Unknown',
    $_SESSION['apellido'] ?? '',
    $filename
));

// Obtener el tipo MIME del archivo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $filepath);
finfo_close($finfo);

// Verificar que sea realmente un PDF
if ($mime_type !== 'application/pdf') {
    http_response_code(400);
    die('Error - El archivo no es un PDF válido');
}

// Obtener información del archivo
$filesize = filesize($filepath);

// Limpiar cualquier output previo
if (ob_get_level()) {
    ob_end_clean();
}

// Configurar headers para visualización en navegador (inline) o descarga
$disposition = isset($_GET['download']) && $_GET['download'] === '1' ? 'attachment' : 'inline';

// Enviar headers HTTP
header('Content-Type: ' . $mime_type);
header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
header('Content-Length: ' . $filesize);
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');

// Headers de seguridad adicionales
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Cache control para archivos médicos sensibles
header('Cache-Control: private, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enviar el archivo
readfile($filepath);
exit;
?>
