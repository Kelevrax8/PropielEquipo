<?php
require_once("../../database_connection.php");
require_once("../../database_queries.php");
session_start();

// Verificar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Instanciar la clase de consultas
        $db_queries = new PropielEquipoQueries();
        
        // Obtener y validar datos del formulario
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validación básica
        if (empty($telefono) || empty($password)) {
            throw new Exception("Teléfono y contraseña son obligatorios");
        }
        
        // Validar formato de teléfono (solo números, 10 dígitos)
        if (!preg_match('/^[0-9]{10}$/', $telefono)) {
            throw new Exception("El teléfono debe contener exactamente 10 dígitos numéricos");
        }
        
        // Validar longitud de contraseña (seguridad básica)
        if (strlen($password) < 4) {
            throw new Exception("La contraseña debe tener al menos 4 caracteres");
        }
        
        // Intentar autenticar usuario
        $user = $db_queries->loginUser($telefono, $password);
        
        if ($user) {
            // Autenticación exitosa - configurar sesión
            $_SESSION['telefono'] = $telefono;
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellido'] = $user['apellido'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['rol'] = $user['rol'];
            
            // Redirigir según el rol del usuario
            switch ($user['rol']) {
                case 1: // Doctor/Admin
                    // Obtener especialidades del doctor
                    $especialidades = $db_queries->getDoctorSpecialties($user['user_id']);
                    
                    // Validar que se obtuvieron especialidades
                    if (!is_array($especialidades)) {
                        $especialidades = [];
                    }
                    
                    // Guardar especialidades en la sesión
                    $_SESSION['especialidades'] = $especialidades;
                    
                    // Redirigir según la especialidad del doctor
                    if (count($especialidades) == 1) {
                        // Doctor con una sola especialidad - ir directo a su dashboard específico
                        $especialidad = $especialidades[0];
                        $codigo_especialidad = '';
                        
                        switch($especialidad['id_especialidad']) {
                            case 1: // Dermatología
                                $codigo_especialidad = 'dermatologia';
                                break;
                            case 2: // Podología
                                $codigo_especialidad = 'podologia';
                                break;
                            case 3: // Tamizaje
                                $codigo_especialidad = 'tamizaje';
                                break;
                        }
                        
                        if ($codigo_especialidad) {
                            header("Location: ../../Doctor/especialidades/{$codigo_especialidad}/dashboard_{$codigo_especialidad}.php");
                        } else {
                            header('Location: ../../Doctor/dashboarddoc.php');
                        }
                    } elseif (count($especialidades) > 1) {
                        // Doctor con múltiples especialidades - ir al dashboard general
                        header('Location: ../../Doctor/dashboarddoc.php');
                    } else {
                        // Doctor sin especialidades asignadas - ir al dashboard general
                        error_log("Doctor ID {$user['user_id']} no tiene especialidades asignadas");
                        header('Location: ../../Doctor/dashboarddoc.php');
                    }
                    break;
                case 3: // Paciente
                    header('Location: ../../Paciente/dashboardpaciente.php');
                    break;
                default:
                    throw new Exception("Tipo de usuario no válido");
            }
            exit();
        } else {
            // Credenciales incorrectas
            echo "<script>
                alert('Teléfono o contraseña incorrectos');
                window.location.replace('../login.html');
            </script>";
        }
        
    } catch (Exception $e) {
        // Manejo de errores
        error_log("Error en login: " . $e->getMessage());
        echo "<script>
            alert('Error: " . addslashes($e->getMessage()) . "');
            window.location.replace('../login.html');
        </script>";
    }
}
?>