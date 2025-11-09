<?php
// Incluir archivos de configuración
require_once "../../database_connection.php";
require_once "../../database_queries.php";

// Verificar si se enviaron los datos desde el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Instanciar la clase de consultas
        $db_queries = new PropielEquipoQueries();
        
        // Obtener y validar los datos desde el formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $edad = intval($_POST['edad'] ?? 0);
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';
        $genero = intval($_POST['genero'] ?? 0);
        $especialidades = $_POST['especialidades'] ?? [];
        $cedula_profesional = trim($_POST['cedula_profesional'] ?? '');
        
        // Validación básica
        if (empty($nombre) || empty($apellido) || empty($telefono) || empty($password) || empty($cedula_profesional)) {
            throw new Exception("Todos los campos son obligatorios");
        }
        
        // Validar especialidades
        if (empty($especialidades) || !is_array($especialidades)) {
            throw new Exception("Debe seleccionar al menos una especialidad médica");
        }
        
        // Validar edad
        if ($edad < 25 || $edad > 75) {
            throw new Exception("La edad debe estar entre 25 y 75 años para personal médico");
        }
        
        // Validar formato de nombre y apellido (solo letras, espacios y acentos)
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre)) {
            throw new Exception("El nombre solo debe contener letras y espacios");
        }
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $apellido)) {
            throw new Exception("El apellido solo debe contener letras y espacios");
        }
        
        // Validar formato de teléfono (10 dígitos)
        if (!preg_match('/^[0-9]{10}$/', $telefono)) {
            throw new Exception("El teléfono debe contener exactamente 10 dígitos");
        }
        
        // Validar cédula profesional (solo números, mínimo 6 dígitos)
        if (!preg_match('/^[0-9]{6,20}$/', $cedula_profesional)) {
            throw new Exception("La cédula profesional debe contener entre 6 y 20 dígitos");
        }
        
        // Validar contraseña (mínimo 6 caracteres)
        if (strlen($password) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres");
        }
        
        // Validar género
        if (!in_array($genero, [1, 2, 3])) {
            throw new Exception("Debe seleccionar un género válido");
        }
        
        // Validar especialidades (solo valores permitidos: 1, 2, 3)
        $especialidades_validas = [1, 2, 3]; // 1=Dermatología, 2=Podología, 3=Tamizaje
        foreach ($especialidades as $especialidad) {
            if (!in_array((int)$especialidad, $especialidades_validas)) {
                throw new Exception("Especialidad médica no válida");
            }
        }
        
        // Registrar doctor con especialidades usando el nuevo método
        $doctor_id = $db_queries->registerMedicalStaff(
            $nombre, 
            $apellido, 
            $edad, 
            $telefono, 
            null, // email no requerido
            $password, 
            $genero, 
            $especialidades,
            $cedula_profesional
        );
        
        if ($doctor_id) {
            // Obtener nombres de especialidades para el mensaje
            $especialidades_nombres = [];
            $especialidades_map = [
                1 => 'Dermatología',
                2 => 'Podología', 
                3 => 'Tamizaje'
            ];
            
            foreach ($especialidades as $esp_id) {
                $especialidades_nombres[] = $especialidades_map[(int)$esp_id];
            }
            
            $especialidades_texto = implode(', ', $especialidades_nombres);
            $mensaje = "Doctor registrado correctamente con especialidades: $especialidades_texto";
            
            echo "<script>
                alert('$mensaje');
                window.location.replace('../login.html');
            </script>";
            exit();
        } else {
            throw new Exception("Error al registrar el doctor. Verifique que el teléfono no esté en uso.");
        }
        
    } catch (Exception $e) {
        // Manejo de errores
        error_log("Error en registrarroot.php: " . $e->getMessage());
        $mensaje = "Error: " . addslashes($e->getMessage());
        echo "<script>
            alert('$mensaje');
            window.history.back();
        </script>";
    }
} else {
    // Si no es una petición POST, redirigir al formulario
    header('Location: ../registrarroot.html');
    exit();
}
?>