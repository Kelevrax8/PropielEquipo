<?php
//Aqui puedes colocar el código para la página de reservas de paciente

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
        
    } catch (Exception $e) {
        error_log("Error en reservas paciente: " . $e->getMessage());
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
    <title>Mis Citas - PropielEquipo</title>
    <link href="../output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
</head>
<body class="bg-gradient-to-r from-emerald-50 to-emerald-100 flex flex-col min-h-screen">
    
    <!-- Navbar Moderna -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <img class="h-10 w-auto" src="../Images/logopropieel.png" alt="PropielEquipo">
                    <span class="ml-3 text-xl font-bold text-gray-800">PropielEquipo</span>
                </div>
                
                <!-- Navegación Desktop -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="dashboardpaciente.php" class="text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium transition duration-200">
                        <ion-icon name="person" class="mr-2"></ion-icon>Perfil
                    </a>
                    <a href="reservar.php" class="text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium transition duration-200">
                        <ion-icon name="calendar" class="mr-2"></ion-icon>Reservar Cita
                    </a>
                    <a href="reservas.php" class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="list" class="mr-2"></ion-icon>Mis Citas
                    </a>
                    <a href="imagenes_medicas.php" class="text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium transition duration-200">
                        <ion-icon name="images" class="mr-2"></ion-icon>Imágenes
                    </a>
                </div>
                
                <!-- Botón Cerrar Sesión -->
                <div class="flex items-center">
                    <form action="php_action/logout.php" method="post" class="hidden md:block">
                        <button class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg font-medium transition duration-200">
                            <ion-icon name="log-out" class="mr-2"></ion-icon>Cerrar Sesión
                        </button>
                    </form>
                    
                    <!-- Menú Mobile -->
                    <button class="md:hidden text-gray-600 hover:text-gray-800" onclick="toggleMobileMenu()">
                        <ion-icon name="menu" class="text-2xl" id="mobile-menu-icon"></ion-icon>
                    </button>
                </div>
            </div>
            
            <!-- Navegación Mobile -->
            <div id="mobile-menu" class="md:hidden hidden border-t border-gray-200 py-4">
                <div class="space-y-2">
                    <a href="dashboardpaciente.php" class="block text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="person" class="mr-2"></ion-icon>Perfil
                    </a>
                    <a href="reservar.php" class="block text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="calendar" class="mr-2"></ion-icon>Reservar Cita
                    </a>
                    <a href="reservas.php" class="block bg-emerald-100 text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="list" class="mr-2"></ion-icon>Mis Citas
                    </a>
                    <a href="imagenes_medicas.php" class="block text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="images" class="mr-2"></ion-icon>Imágenes
                    </a>
                    <form action="php_action/logout.php" method="post" class="mt-4">
                        <button class="w-full bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg font-medium">
                            <ion-icon name="log-out" class="mr-2"></ion-icon>Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header de Mis Citas -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-2xl p-8 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Mis Citas Médicas</h1>
                    <p class="text-emerald-100"><?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-emerald-200 text-sm">Gestiona y consulta tus citas programadas</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="calendar" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
            <?php
            try {
                $appointments = $db_queries->getAppointmentsByUserId($uid);
                
                // Configurar zona horaria para consistencia
                date_default_timezone_set('America/Mexico_City');
                $datetime_actual = new DateTime();
                $hoy = $datetime_actual->format('Y-m-d');
                
                // Filtrar citas considerando fecha y hora con comparación robusta
                $citas_hoy = array_filter($appointments, function($cita) use ($hoy, $datetime_actual) {
                    // Usar comparación robusta de fechas
                    $fecha_cita_obj = DateTime::createFromFormat('Y-m-d', $cita['fecha']);
                    $fecha_hoy_obj = DateTime::createFromFormat('Y-m-d', $hoy);
                    
                    if (!$fecha_cita_obj || !$fecha_hoy_obj) return false;
                    if ($fecha_cita_obj->format('Y-m-d') !== $fecha_hoy_obj->format('Y-m-d')) return false;
                    
                    // Si es hoy, verificar que no haya pasado la hora
                    $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $cita['fecha'] . ' ' . $cita['horario']);
                    return $datetime_cita && $datetime_cita >= $datetime_actual;
                });
                
                $citas_futuras = array_filter($appointments, function($cita) use ($datetime_actual) {
                    $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $cita['fecha'] . ' ' . $cita['horario']);
                    return $datetime_cita && $datetime_cita > $datetime_actual;
                });
                
                // Citas completadas (pasadas)
                $citas_completadas = array_filter($appointments, function($cita) use ($datetime_actual) {
                    $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $cita['fecha'] . ' ' . $cita['horario']);
                    return $datetime_cita && $datetime_cita < $datetime_actual;
                });
                
                // Citas canceladas
                $citas_canceladas = array_filter($appointments, function($cita) {
                    return isset($cita['estado']) && $cita['estado'] === 'cancelada';
                });
                
                // Filtrar citas activas (excluir canceladas de otros conteos)
                $appointments_activas = array_filter($appointments, function($cita) {
                    return !isset($cita['estado']) || $cita['estado'] !== 'cancelada';
                });
                
                // Recalcular conteos excluyendo canceladas
                $citas_hoy = array_filter($appointments_activas, function($cita) use ($hoy, $datetime_actual) {
                    $fecha_cita_obj = DateTime::createFromFormat('Y-m-d', $cita['fecha']);
                    $fecha_hoy_obj = DateTime::createFromFormat('Y-m-d', $hoy);
                    
                    if (!$fecha_cita_obj || !$fecha_hoy_obj) return false;
                    if ($fecha_cita_obj->format('Y-m-d') !== $fecha_hoy_obj->format('Y-m-d')) return false;
                    
                    $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $cita['fecha'] . ' ' . $cita['horario']);
                    return $datetime_cita && $datetime_cita >= $datetime_actual;
                });
                
                $citas_futuras = array_filter($appointments_activas, function($cita) use ($datetime_actual) {
                    $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $cita['fecha'] . ' ' . $cita['horario']);
                    return $datetime_cita && $datetime_cita > $datetime_actual;
                });
                
                $citas_completadas = array_filter($appointments_activas, function($cita) use ($datetime_actual) {
                    $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $cita['fecha'] . ' ' . $cita['horario']);
                    return $datetime_cita && $datetime_cita < $datetime_actual;
                });
            } catch (Exception $e) {
                $appointments = [];
                $citas_hoy = [];
                $citas_futuras = [];
                $citas_completadas = [];
                $citas_canceladas = [];
            }
            ?>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total de Citas</p>
                        <p class="text-2xl font-bold text-emerald-600"><?php echo count($appointments); ?></p>
                    </div>
                    <ion-icon name="calendar" class="text-3xl text-emerald-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Citas Hoy</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo count($citas_hoy); ?></p>
                    </div>
                    <ion-icon name="today" class="text-3xl text-blue-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Próximas Citas</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo count($citas_futuras); ?></p>
                    </div>
                    <ion-icon name="time" class="text-3xl text-purple-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Completadas</p>
                        <p class="text-2xl font-bold text-gray-600"><?php echo count($citas_completadas); ?></p>
                    </div>
                    <ion-icon name="checkmark-done" class="text-3xl text-gray-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Canceladas</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo count($citas_canceladas); ?></p>
                    </div>
                    <ion-icon name="close-circle" class="text-3xl text-red-500"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Lista de Citas -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-emerald-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold flex items-center">
                        <ion-icon name="list" class="mr-2"></ion-icon>
                        Listado de Citas Médicas
                    </h2>
                    <div class="text-sm text-emerald-100">
                        <ion-icon name="information-circle" class="mr-1"></ion-icon>
                        Cancelación permitida hasta 2 horas antes
                    </div>
                </div>
            </div>
            
            <?php if (count($appointments) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <ion-icon name="document-text" class="mr-1"></ion-icon>ID Cita
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <ion-icon name="medical" class="mr-1"></ion-icon>Servicio
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <ion-icon name="calendar-outline" class="mr-1"></ion-icon>Fecha
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <ion-icon name="time-outline" class="mr-1"></ion-icon>Horario
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <ion-icon name="flag" class="mr-1"></ion-icon>Estado
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <ion-icon name="settings" class="mr-1"></ion-icon>Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($appointments as $appointment): 
                                $fecha_cita = $appointment['fecha'];
                                $hora_cita = $appointment['horario'];
                                
                                // Crear datetime completo de la cita con validación mejorada
                                $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $fecha_cita . ' ' . $hora_cita);
                                if (!$datetime_cita) {
                                    $datetime_cita = DateTime::createFromFormat('Y-m-d H:i:s', $fecha_cita . ' ' . $hora_cita . ':00');
                                }
                                
                                // Usar comparación robusta de fechas
                                $fecha_cita_obj = DateTime::createFromFormat('Y-m-d', $fecha_cita);
                                $fecha_hoy_obj = DateTime::createFromFormat('Y-m-d', $hoy);
                                
                                $es_hoy = ($fecha_cita_obj && $fecha_hoy_obj && $fecha_cita_obj->format('Y-m-d') === $fecha_hoy_obj->format('Y-m-d'));
                                $es_pasada = ($datetime_cita && $datetime_cita < $datetime_actual);
                                $es_futura = ($datetime_cita && $datetime_cita > $datetime_actual);
                                $es_cancelada = (isset($appointment['estado']) && $appointment['estado'] === 'cancelada');
                                
                                // Verificar si se puede cancelar (al menos 2 horas de anticipación)
                                $puede_cancelar = false;
                                if ($datetime_cita && !$es_cancelada && !$es_pasada) {
                                    $diferencia_segundos = $datetime_cita->getTimestamp() - $datetime_actual->getTimestamp();
                                    $diferencia_horas = $diferencia_segundos / 3600;
                                    $puede_cancelar = ($diferencia_horas >= 2);
                                }
                            ?>
                                <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="text-emerald-600 font-semibold text-sm"><?php echo htmlspecialchars($appointment['id_cita']); ?></span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">#<?php echo htmlspecialchars($appointment['id_cita']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php 
                                            $servicio = $appointment['servicio'];
                                            $color_servicio = 'blue';
                                            $icono_servicio = 'medical';
                                            
                                            switch(strtolower($servicio)) {
                                                case 'dermatología':
                                                    $color_servicio = 'blue';
                                                    $icono_servicio = 'body';
                                                    break;
                                                case 'podología':
                                                    $color_servicio = 'green';
                                                    $icono_servicio = 'footsteps';
                                                    break;
                                                case 'tamiz':
                                                    $color_servicio = 'purple';
                                                    $icono_servicio = 'analytics';
                                                    break;
                                            }
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-<?php echo $color_servicio; ?>-100 text-<?php echo $color_servicio; ?>-800">
                                                <ion-icon name="<?php echo $icono_servicio; ?>" class="mr-1"></ion-icon>
                                                <?php echo htmlspecialchars(ucfirst($servicio)); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <ion-icon name="calendar" class="mr-2 text-gray-400"></ion-icon>
                                            <?php echo date('d/m/Y', strtotime($appointment['fecha'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <ion-icon name="time" class="mr-2 text-gray-400"></ion-icon>
                                            <?php echo htmlspecialchars($appointment['horario']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($es_cancelada): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <ion-icon name="close-circle" class="mr-1"></ion-icon>Cancelada
                                            </span>
                                        <?php elseif ($es_hoy && !$es_pasada): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <ion-icon name="today" class="mr-1"></ion-icon>Hoy - <?php echo htmlspecialchars($appointment['horario']); ?>
                                            </span>
                                        <?php elseif ($es_futura): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <ion-icon name="checkmark-circle" class="mr-1"></ion-icon>Programada
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <ion-icon name="checkmark-done" class="mr-1"></ion-icon>Completada
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($es_cancelada): ?>
                                            <span class="text-gray-400 text-xs">
                                                <ion-icon name="close-circle" class="mr-1"></ion-icon>
                                                Cita cancelada
                                            </span>
                                        <?php elseif ($puede_cancelar): ?>
                                            <!-- Solo mostrar botón de cancelar para citas que cumplen con la regla de 2 horas -->
                                            <button onclick="showCancelModal(<?php echo $appointment['id_cita']; ?>, '<?php echo htmlspecialchars($appointment['servicio']); ?>', '<?php echo $appointment['fecha']; ?>', '<?php echo $appointment['horario']; ?>')" 
                                                    class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center">
                                                <ion-icon name="close-circle" class="mr-1"></ion-icon>
                                                Cancelar
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">
                                                <ion-icon name="lock-closed" class="mr-1"></ion-icon>
                                                <?php 
                                                if ($es_pasada) {
                                                    echo "Cita completada";
                                                } elseif (!$es_cancelada && $datetime_cita) {
                                                    $diferencia_segundos = $datetime_cita->getTimestamp() - $datetime_actual->getTimestamp();
                                                    $diferencia_horas = round($diferencia_segundos / 3600, 1);
                                                    if ($diferencia_horas > 0 && $diferencia_horas < 2) {
                                                        echo "Faltan {$diferencia_horas}h (mín. 2h)";
                                                    } else {
                                                        echo "No se puede cancelar";
                                                    }
                                                } else {
                                                    echo "No se puede cancelar";
                                                }
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <div class="text-gray-500">
                        <ion-icon name="calendar-outline" class="text-6xl text-gray-300 mb-4"></ion-icon>
                        <p class="text-lg font-medium">No tienes citas programadas</p>
                        <p class="text-sm mt-2">¡Reserva tu primera cita médica!</p>
                        <div class="mt-6">
                            <a href="reservar.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 inline-flex items-center">
                                <ion-icon name="add-circle" class="mr-2"></ion-icon>
                                Reservar Cita
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Acciones Rápidas -->
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="reservar.php" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="add-circle" class="mr-2"></ion-icon>
                Nueva Cita
            </a>
            
            <a href="dashboardpaciente.php" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                Volver al Perfil
            </a>
            
            <a href="imagenes_medicas.php" 
               class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="images" class="mr-2"></ion-icon>
                Mis Imágenes
            </a>
        </div>
    </div>

    <!-- Modal de Confirmación de Cancelación -->
    <div id="cancel-modal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50 hidden p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <!-- Header del Modal -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <ion-icon name="warning" class="text-2xl mr-3"></ion-icon>
                        <h2 class="text-xl font-bold">Cancelar Cita</h2>
                    </div>
                    <button onclick="closeCancelModal()" class="text-white hover:text-gray-200 text-2xl">
                        <ion-icon name="close"></ion-icon>
                    </button>
                </div>
            </div>

            <!-- Contenido del Modal -->
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <ion-icon name="trash" class="text-2xl text-red-600"></ion-icon>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">¿Confirmas la cancelación?</h3>
                    <p class="text-sm text-gray-600 mb-4">Esta acción no se puede deshacer.</p>
                </div>

                <!-- Detalles de la cita a cancelar -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="font-semibold text-gray-800 mb-2">Detalles de la cita:</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID Cita:</span>
                            <span class="font-medium" id="cancel-cita-id">#--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Servicio:</span>
                            <span class="font-medium" id="cancel-servicio">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Fecha:</span>
                            <span class="font-medium" id="cancel-fecha">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Horario:</span>
                            <span class="font-medium" id="cancel-horario">--</span>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" 
                            onclick="closeCancelModal()" 
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-4 rounded-lg transition duration-200">
                        <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                        Mantener Cita
                    </button>
                    
                    <button type="button" 
                            onclick="confirmCancelation()" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                        <ion-icon name="trash" class="mr-2"></ion-icon>
                        Sí, Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variable global para almacenar el ID de la cita a cancelar
        let citaParaCancelar = null;

        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');
            
            mobileMenu.classList.toggle('hidden');
            
            if (mobileMenu.classList.contains('hidden')) {
                menuIcon.name = 'menu';
            } else {
                menuIcon.name = 'close';
            }
        }

        // Función para mostrar el modal de cancelación
        function showCancelModal(citaId, servicio, fecha, horario) {
            citaParaCancelar = citaId;
            
            // Actualizar los detalles en el modal
            document.getElementById('cancel-cita-id').textContent = '#' + citaId;
            document.getElementById('cancel-servicio').textContent = servicio;
            document.getElementById('cancel-fecha').textContent = formatDateForDisplay(fecha);
            document.getElementById('cancel-horario').textContent = horario;
            
            // Mostrar el modal
            const modal = document.getElementById('cancel-modal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            
            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        }

        // Función para cerrar el modal de cancelación
        function closeCancelModal() {
            const modal = document.getElementById('cancel-modal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            
            // Restaurar scroll del body
            document.body.style.overflow = 'auto';
            
            // Limpiar la variable
            citaParaCancelar = null;
        }

        // Función para confirmar la cancelación
        async function confirmCancelation() {
            if (!citaParaCancelar) {
                alert('Error: No se ha seleccionado una cita para cancelar.');
                return;
            }

            try {
                // Mostrar mensaje de procesamiento
                const confirmButton = document.querySelector('button[onclick="confirmCancelation()"]');
                const originalText = confirmButton.innerHTML;
                confirmButton.innerHTML = '<ion-icon name="hourglass" class="mr-2"></ion-icon>Cancelando...';
                confirmButton.disabled = true;

                // Enviar petición de cancelación
                const response = await fetch('php_action/cancel_appointment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cita_id: citaParaCancelar
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Éxito - recargar la página para mostrar cambios
                    alert('✅ Cita cancelada exitosamente.\\n\\nLa cita ha sido eliminada de tu agenda.');
                    window.location.reload();
                } else {
                    // Error - mostrar mensaje
                    alert('❌ Error: ' + (data.message || 'No se pudo cancelar la cita.'));
                    
                    // Restaurar botón
                    confirmButton.innerHTML = originalText;
                    confirmButton.disabled = false;
                }
            } catch (error) {
                console.error('Error al cancelar cita:', error);
                alert('❌ Error de conexión. Por favor intenta nuevamente.');
                
                // Restaurar botón
                const confirmButton = document.querySelector('button[onclick="confirmCancelation()"]');
                confirmButton.innerHTML = '<ion-icon name="trash" class="mr-2"></ion-icon>Sí, Cancelar';
                confirmButton.disabled = false;
            }
        }

        // Función auxiliar para formatear fechas
        function formatDateForDisplay(dateString) {
            try {
                const date = new Date(dateString + 'T00:00:00');
                const options = { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                };
                return date.toLocaleDateString('es-ES', options);
            } catch (error) {
                return dateString; // Fallback en caso de error
            }
        }

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCancelModal();
            }
        });
    </script>

</body>
</html>
