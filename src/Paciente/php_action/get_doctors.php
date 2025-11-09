<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar si se proporciona la especialidad
if (!isset($_GET['specialty'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Especialidad requerida']);
    exit();
}

$specialty = $_GET['specialty'];

try {
    require_once '../../database_connection.php';
    require_once '../../database_queries.php';
    
    $db_queries = new PropielEquipoQueries();
    
    // Mapear nombres de servicios a IDs de especialidades
    $specialty_map = [
        'dermatología' => 1,
        'podología' => 2,
        'tamiz' => 3
    ];
    
    if (!isset($specialty_map[$specialty])) {
        http_response_code(404);
        echo json_encode(['error' => 'Especialidad no encontrada']);
        exit();
    }
    
    $specialty_id = $specialty_map[$specialty];
    
    // Consulta para obtener médicos por especialidad
    $query = "
        SELECT 
            u.user_id,
            u.nombre,
            u.apellido,
            u.cedula_profesional,
            e.nombre_especialidad,
            CASE 
                WHEN u.genero = 1 THEN 'Dr.'
                WHEN u.genero = 2 THEN 'Dra.'
                ELSE 'Dr./Dra.'
            END as titulo
        FROM usuarios u
        INNER JOIN doctor_especialidades de ON u.user_id = de.id_doctor
        INNER JOIN especialidades e ON de.id_especialidad = e.id_especialidad
        WHERE u.rol = 1 
        AND e.id_especialidad = ?
        AND u.user_id IS NOT NULL
    ";
    
    // Usar la conexión PDO de la clase Database
    $database = new Database();
    $conn = $database->getConnection();
    $stmt = $conn->prepare($query);
    $stmt->bindParam(1, $specialty_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $doctors = [];
    foreach ($result as $row) {
        // Usar cédula real si existe, sino generar una automática
        $cedula = !empty($row['cedula_profesional']) 
            ? $row['cedula_profesional'] 
            : generateCedula($row['nombre_especialidad'], $row['user_id']);
            
        $doctors[] = [
            'id' => $row['user_id'],
            'titulo' => $row['titulo'],
            'nombre' => $row['nombre'],
            'apellido' => $row['apellido'],
            'nombre_completo' => $row['titulo'] . ' ' . $row['nombre'] . ' ' . $row['apellido'],
            'especialidad' => $row['nombre_especialidad'],
            'cedula' => $cedula,
            'cedula_real' => !empty($row['cedula_profesional'])
        ];
    }
    
    // Si no hay médicos registrados para esa especialidad, retornar datos por defecto
    if (empty($doctors)) {
        $doctors[] = getDefaultDoctor($specialty);
    }
    
    echo json_encode([
        'success' => true,
        'doctors' => $doctors,
        'specialty' => $specialty
    ]);
    
} catch (Exception $e) {
    error_log("Error en get_doctors.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'doctors' => [getDefaultDoctor($specialty)]
    ]);
}

function generateCedula($especialidad, $user_id) {
    $prefixes = [
        'Dermatología' => 'DER',
        'Podología' => 'POD', 
        'Tamizaje' => 'TAM'
    ];
    
    $prefix = isset($prefixes[$especialidad]) ? $prefixes[$especialidad] : 'MED';
    return $prefix . str_pad($user_id, 8, '0', STR_PAD_LEFT);
}

function getDefaultDoctor($specialty) {
    // Intentar obtener un médico real de la BD para el fallback
    try {
        $specialty_map = [
            'dermatología' => 1,
            'podología' => 2,
            'tamiz' => 3
        ];
        
        if (isset($specialty_map[$specialty])) {
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "
                SELECT 
                    u.user_id,
                    u.nombre,
                    u.apellido,
                    u.cedula_profesional,
                    e.nombre_especialidad,
                    CASE 
                        WHEN u.genero = 1 THEN 'Dr.'
                        WHEN u.genero = 2 THEN 'Dra.'
                        ELSE 'Dr./Dra.'
                    END as titulo
                FROM usuarios u
                INNER JOIN doctor_especialidades de ON u.user_id = de.id_doctor
                INNER JOIN especialidades e ON de.id_especialidad = e.id_especialidad
                WHERE u.rol = 1 AND e.id_especialidad = ?
                LIMIT 1
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, $specialty_map[$specialty], PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $cedula = !empty($result['cedula_profesional']) 
                    ? $result['cedula_profesional'] 
                    : generateCedula($result['nombre_especialidad'], $result['user_id']);
                    
                return [
                    'id' => $result['user_id'],
                    'titulo' => $result['titulo'],
                    'nombre' => $result['nombre'],
                    'apellido' => $result['apellido'],
                    'nombre_completo' => $result['titulo'] . ' ' . $result['nombre'] . ' ' . $result['apellido'],
                    'especialidad' => $result['nombre_especialidad'],
                    'cedula' => $cedula,
                    'cedula_real' => !empty($result['cedula_profesional'])
                ];
            }
        }
    } catch (Exception $e) {
        // Si hay error, continuar con los datos estáticos
        error_log("Error obteniendo médico real para fallback: " . $e->getMessage());
    }
    
    // Fallback a datos completamente estáticos
    $defaults = [
        'dermatología' => [
            'id' => 0,
            'titulo' => 'Dra.',
            'nombre' => 'María',
            'apellido' => 'García',
            'nombre_completo' => 'Dra. María García',
            'especialidad' => 'Dermatología',
            'cedula' => 'DER12345678',
            'cedula_real' => false
        ],
        'podología' => [
            'id' => 0,
            'titulo' => 'Dr.',
            'nombre' => 'Juan',
            'apellido' => 'López',
            'nombre_completo' => 'Dr. Juan López',
            'especialidad' => 'Podología',
            'cedula' => 'POD12345678',
            'cedula_real' => false
        ],
        'tamiz' => [
            'id' => 0,
            'titulo' => 'Dr.',
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'nombre_completo' => 'Dr. Carlos Mendoza',
            'especialidad' => 'Medicina General',
            'cedula' => 'TAM12345678',
            'cedula_real' => false
        ]
    ];
    
    return isset($defaults[$specialty]) ? $defaults[$specialty] : $defaults['tamiz'];
}
?>
