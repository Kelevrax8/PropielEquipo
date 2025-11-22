<?php
session_start();
require_once('../../database_connection.php');

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 3) {
    $_SESSION['error'] = 'Acceso no autorizado';
    header('Location: ../../Landing/login.html');
    exit();
}

// Validate POST data
if (!isset($_POST['id_cita']) || empty($_POST['id_cita'])) {
    $_SESSION['error'] = 'ID de cita no válido';
    header('Location: ../reservas.php');
    exit();
}

$id_cita = intval($_POST['id_cita']);
$id_usuario = $_SESSION['user_id'];
$notas = isset($_POST['notas']) ? trim($_POST['notas']) : null;

// Verify the appointment belongs to this user
$query = "SELECT id_cita, id_usuario FROM citas WHERE id_cita = ? AND id_usuario = ?";
$stmt = $conex->prepare($query);
$stmt->bind_param("ii", $id_cita, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Cita no encontrada o no tienes permiso para modificarla';
    header('Location: ../reservas.php');
    exit();
}

// Validate file upload
if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Error al subir el archivo. Por favor intenta nuevamente.';
    header('Location: ../subir_comprobante.php?cita=' . $id_cita);
    exit();
}

$file = $_FILES['comprobante'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

// Get file extension
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

// Validate file extension
if (!in_array($fileExt, $allowedExtensions)) {
    $_SESSION['error'] = 'Formato de archivo no válido. Solo se permiten JPG, PNG o PDF.';
    header('Location: ../subir_comprobante.php?cita=' . $id_cita);
    exit();
}

// Validate file size (max 5MB)
$maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
if ($fileSize > $maxFileSize) {
    $_SESSION['error'] = 'El archivo es demasiado grande. Tamaño máximo: 5MB.';
    header('Location: ../subir_comprobante.php?cita=' . $id_cita);
    exit();
}

// Additional validation for images
if (in_array($fileExt, ['jpg', 'jpeg', 'png'])) {
    $imageInfo = getimagesize($fileTmpName);
    if ($imageInfo === false) {
        $_SESSION['error'] = 'El archivo no es una imagen válida.';
        header('Location: ../subir_comprobante.php?cita=' . $id_cita);
        exit();
    }
}

// Create upload directory if it doesn't exist
$uploadDir = '../../comprobantes_pago/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$newFileName = 'comprobante_cita_' . $id_cita . '_' . time() . '.' . $fileExt;
$uploadPath = $uploadDir . $newFileName;

// Move uploaded file
if (!move_uploaded_file($fileTmpName, $uploadPath)) {
    $_SESSION['error'] = 'Error al guardar el archivo. Por favor intenta nuevamente.';
    header('Location: ../subir_comprobante.php?cita=' . $id_cita);
    exit();
}

// Update database
$query = "UPDATE citas 
          SET comprobante_pago = ?, 
              fecha_pago = NOW(), 
              estado = 'pendiente',
              notas_pago = ?
          WHERE id_cita = ? AND id_usuario = ?";

$stmt = $conex->prepare($query);
$stmt->bind_param("ssii", $newFileName, $notas, $id_cita, $id_usuario);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Comprobante subido correctamente. El personal médico revisará tu pago en las próximas 24-48 horas.';
    header('Location: ../reservas.php');
} else {
    // If database update fails, delete the uploaded file
    unlink($uploadPath);
    $_SESSION['error'] = 'Error al actualizar la base de datos. Por favor intenta nuevamente.';
    header('Location: ../subir_comprobante.php?cita=' . $id_cita);
}

$stmt->close();
$conex->close();
exit();
?>
