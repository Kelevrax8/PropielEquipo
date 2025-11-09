<?php
/**
 * User Registration Handler - Updated for new database structure
 * PropielEquipo Medical System
 */

// Include the new database queries class
require_once "../../database_queries.php";

// Verify if data was sent from the form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get data from the form
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $edad = (int)$_POST['edad'];
    $telefono = trim($_POST['telefono']);
    $password = $_POST['password'];
    $genero = (int)$_POST['genero'];
    $rol = 3; // Default role: Patient
    
    // Validate input data
    $errors = [];
    
    // Validate name (letters only)
    if (!preg_match("/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)) {
        $errors[] = "El nombre solo puede contener letras";
    }
    
    // Validate last name (letters only)
    if (!preg_match("/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/", $apellido)) {
        $errors[] = "El apellido solo puede contener letras";
    }
    
    // Validate age
    if ($edad < 1 || $edad > 99) {
        $errors[] = "La edad debe estar entre 1 y 99 años";
    }
    
    // Validate phone (10 digits)
    if (!preg_match("/^[0-9]{10}$/", $telefono)) {
        $errors[] = "El teléfono debe tener exactamente 10 dígitos";
    }
    
    // Validate password (minimum 6 characters)
    if (strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    // Validate gender
    if (!in_array($genero, [1, 2, 3])) {
        $errors[] = "Género no válido";
    }
    
    // If there are validation errors, show them
    if (!empty($errors)) {
        $mensaje = "Errores de validación:\n" . implode("\n", $errors);
        echo "<script>alert(" . json_encode($mensaje) . ");window.history.back();</script>";
        exit();
    }
    
    try {
        // Create database connection
        $db = new PropielEquipoQueries();
        
        // Attempt to register the user
        $result = $db->registerUser($nombre, $apellido, $edad, $telefono, $password, $genero, $rol);
        
        if ($result) {
            $mensaje = "¡Registro exitoso! Ahora puedes iniciar sesión con tu número de teléfono.";
            echo "<script>alert(" . json_encode($mensaje) . ");window.location.replace('../login.html');</script>";
            exit();
        } else {
            $mensaje = "Error al registrar el usuario. Es posible que el número de teléfono ya esté registrado.";
            echo "<script>alert(" . json_encode($mensaje) . ");window.history.back();</script>";
            exit();
        }
        
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Registration Error: " . $e->getMessage());
        
        $mensaje = "Error interno del servidor. Por favor, intenta más tarde.";
        echo "<script>alert(" . json_encode($mensaje) . ");window.history.back();</script>";
        exit();
    }
    
} else {
    // If not POST request, redirect back
    header("Location: ../registrar.html");
    exit();
}

?>