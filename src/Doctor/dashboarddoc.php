<?php
// Verificar si el usuario ha iniciado sesión
session_start();

if (isset($_SESSION['telefono'])) {
    $telefono = $_SESSION['telefono'];
    $nombre = $_SESSION['nombre'];
    
    // Obtener la información del usuario desde la base de datos
    require_once '../database_connection.php';
    require_once '../database_queries.php';
    
    try {
        $db_queries = new PropielEquipoQueries();
        $user = $db_queries->getUserById($_SESSION['user_id']);
        
        if (!$user) {
            // Usuario no encontrado, cerrar sesión
            session_destroy();
            header('location: ../Landing/login.html');
            exit();
        }
        
        // Asignar variables para usar en el HTML
        $uid = $user['user_id'];
        $rol = $user['rol_nombre'];
        $nombre = $user["nombre"];
        $apellido = $user["apellido"];
        $edad = $user["edad"];
        $telefono = $user["telefono"];
        $genero = $user['genero_nombre'];
        
        // Obtener especialidades del doctor si no están en sesión
        if (!isset($_SESSION['especialidades'])) {
            $_SESSION['especialidades'] = $db_queries->getDoctorSpecialties($uid);
        }
        
        // Establecer especialidad actual si no existe
        if (!isset($_SESSION['especialidad_actual']) && !empty($_SESSION['especialidades'])) {
            $_SESSION['especialidad_actual'] = $_SESSION['especialidades'][0];
        }
        
        $especialidades = $_SESSION['especialidades'];
        $especialidad_actual = $_SESSION['especialidad_actual'] ?? null;
        
    } catch (Exception $e) {
        error_log("Error en dashboard doctor: " . $e->getMessage());
        header('location: ../Landing/login.html');
        exit();
    }
} else {
    // Si el usuario no ha iniciado sesión, redirigirlo a la página de inicio de sesión
    header('location: ../Landing/login.html');
    exit();
}

?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../../src/output.css" rel="stylesheet">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
</head>
<body class="bg-gradient-to-r from-emerald-200/50 from-80% to-white flex flex-col min-h-screen">
    <?php include 'shared/navbar_doctor.php'; ?>

    <!-- Dashboard Principal con Especialidades -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-lg p-8 max-w-4xl mx-auto">
            
            <!-- Información del Doctor -->
            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <div class="text-center md:text-left">
                    <h1 class="text-3xl font-bold text-gray-800 mb-4">
                        Dr. <?php echo htmlspecialchars($nombre . " " . $apellido); ?>
                    </h1>
                    <div class="space-y-2 text-gray-600">
                        <p><span class="font-medium">Teléfono:</span> <?php echo htmlspecialchars($telefono); ?></p>
                        <p><span class="font-medium">Género:</span> <?php echo htmlspecialchars($genero); ?></p>
                        <p><span class="font-medium">Edad:</span> <?php echo htmlspecialchars($edad); ?> años</p>
                    </div>
                </div>
                
                <!-- Especialidades del Doctor -->
                <div>
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Mis Especialidades</h2>
                    <div class="space-y-3">
                        <?php foreach ($especialidades as $esp): ?>
                            <div class="flex items-center p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                                <div class="w-3 h-3 bg-emerald-500 rounded-full mr-3"></div>
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($esp['nombre_especialidad']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($esp['descripcion'] ?? ''); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Accesos Directos por Especialidad (Solo Ver) -->
            <div class="border-t pt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Mis Especialidades</h2>
                
                <div class="grid md:grid-cols-<?php echo min(count($especialidades), 3); ?> gap-6">
                    <?php foreach ($especialidades as $esp): 
                        $codigo_especialidad = '';
                        $color_class = '';
                        $icon = '';
                        
                        switch($esp['id_especialidad']) {
                            case 1: // Dermatología
                                $codigo_especialidad = 'dermatologia';
                                $color_class = 'from-blue-500 to-blue-600';
                                $icon = 'medical';
                                break;
                            case 2: // Podología
                                $codigo_especialidad = 'podologia';
                                $color_class = 'from-green-500 to-green-600';
                                $icon = 'body';
                                break;
                            case 3: // Tamizaje
                                $codigo_especialidad = 'tamizaje';
                                $color_class = 'from-purple-500 to-purple-600';
                                $icon = 'analytics';
                                break;
                        }
                    ?>
                        <div class="bg-gradient-to-br <?php echo $color_class; ?> rounded-xl p-6 text-white">
                            <div class="flex items-center mb-4">
                                <ion-icon name="<?php echo $icon; ?>" class="text-3xl mr-3"></ion-icon>
                                <h3 class="text-lg font-bold"><?php echo htmlspecialchars($esp['nombre_especialidad']); ?></h3>
                            </div>
                            
                            <p class="text-sm mb-4 text-white/80">
                                <?php echo htmlspecialchars($esp['descripcion'] ?? 'Especialidad médica'); ?>
                            </p>
                            
                            <div class="space-y-2">
                                <a href="especialidades/<?php echo $codigo_especialidad; ?>/dashboard_<?php echo $codigo_especialidad; ?>.php" 
                                   class="block bg-white/20 hover:bg-white/30 rounded-lg p-3 text-center transition duration-200 font-medium">
                                    🏥 Ver Dashboard
                                </a>
                                <a href="especialidades/<?php echo $codigo_especialidad; ?>/citas_<?php echo $codigo_especialidad; ?>.php" 
                                   class="block bg-white/20 hover:bg-white/30 rounded-lg p-3 text-center transition duration-200 font-medium">
                                    📅 Mis Citas
                                </a>
                                <a href="especialidades/<?php echo $codigo_especialidad; ?>/pacientes_<?php echo $codigo_especialidad; ?>.php" 
                                   class="block bg-white/20 hover:bg-white/30 rounded-lg p-3 text-center transition duration-200 font-medium">
                                    👥 Mis Pacientes
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($especialidades) > 1): ?>
                    <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-amber-800 text-sm">
                            <ion-icon name="information-circle" class="mr-1"></ion-icon>
                            Tienes múltiples especialidades. Cada sección muestra únicamente la información relacionada con esa especialidad específica.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Resumen General -->
            <div class="border-t pt-8 mt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Resumen de Hoy</h2>
                
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-emerald-50 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-emerald-600 mb-2">
                            <?php 
                                try {
                                    $citas_hoy = $db_queries->getAppointmentsByDate(date('Y-m-d'));
                                    echo count($citas_hoy);
                                } catch (Exception $e) {
                                    echo "0";
                                }
                            ?>
                        </div>
                        <p class="text-gray-600">Citas Hoy</p>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-blue-600 mb-2">
                            <?php echo count($especialidades); ?>
                        </div>
                        <p class="text-gray-600">Especialidades</p>
                    </div>
                    
                    <div class="bg-purple-50 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-purple-600 mb-2">
                            <?php 
                                try {
                                    $imagenes = $db_queries->getDoctorPatientImages($uid);
                                    echo count($imagenes);
                                } catch (Exception $e) {
                                    echo "0";
                                }
                            ?>
                        </div>
                        <p class="text-gray-600">Imágenes Médicas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </body>
</html>