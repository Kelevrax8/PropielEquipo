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
        
    } catch (Exception $e) {
        error_log("Error en dashboard paciente: " . $e->getMessage());
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
    <title>Mi Perfil - PropielEquipo</title>
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
                    <a href="dashboardpaciente.php" class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="person" class="mr-2"></ion-icon>Perfil
                    </a>
                    <a href="reservar.php" class="text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium transition duration-200">
                        <ion-icon name="calendar" class="mr-2"></ion-icon>Reservar Cita
                    </a>
                    <a href="reservas.php" class="text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium transition duration-200">
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
                    <a href="dashboardpaciente.php" class="block bg-emerald-100 text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="person" class="mr-2"></ion-icon>Perfil
                    </a>
                    <a href="reservar.php" class="block text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="calendar" class="mr-2"></ion-icon>Reservar Cita
                    </a>
                    <a href="reservas.php" class="block text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium">
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

    <!-- Header de Perfil -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-2xl p-8 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Mi Perfil</h1>
                    <p class="text-emerald-100"><?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-emerald-200 text-sm">Paciente de PropielEquipo</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="person-circle" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Información del Paciente -->
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Datos Personales -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <ion-icon name="person" class="mr-2 text-emerald-600"></ion-icon>
                    Información Personal
                </h2>
                
                <div class="space-y-4">
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <ion-icon name="person-outline" class="text-2xl text-emerald-600 mr-4"></ion-icon>
                        <div>
                            <p class="text-sm text-gray-600">Nombre Completo</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <ion-icon name="call-outline" class="text-2xl text-emerald-600 mr-4"></ion-icon>
                        <div>
                            <p class="text-sm text-gray-600">Teléfono</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($telefono); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <ion-icon name="people-outline" class="text-2xl text-emerald-600 mr-4"></ion-icon>
                        <div>
                            <p class="text-sm text-gray-600">Género</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($genero); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                        <ion-icon name="calendar-outline" class="text-2xl text-emerald-600 mr-4"></ion-icon>
                        <div>
                            <p class="text-sm text-gray-600">Edad</p>
                            <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($edad); ?> años</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <ion-icon name="flash" class="mr-2 text-emerald-600"></ion-icon>
                    Acciones Rápidas
                </h2>
                
                <div class="space-y-4">
                    <a href="reservar.php" class="flex items-center p-4 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition duration-200 group">
                        <ion-icon name="calendar" class="text-2xl text-emerald-600 mr-4 group-hover:scale-110 transition-transform"></ion-icon>
                        <div>
                            <p class="font-semibold text-gray-800">Reservar Nueva Cita</p>
                            <p class="text-sm text-gray-600">Agenda tu próxima consulta médica</p>
                        </div>
                        <ion-icon name="chevron-forward" class="text-emerald-600 ml-auto"></ion-icon>
                    </a>
                    
                    <a href="reservas.php" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-200 group">
                        <ion-icon name="list" class="text-2xl text-blue-600 mr-4 group-hover:scale-110 transition-transform"></ion-icon>
                        <div>
                            <p class="font-semibold text-gray-800">Ver Mis Citas</p>
                            <p class="text-sm text-gray-600">Consulta tus citas programadas</p>
                        </div>
                        <ion-icon name="chevron-forward" class="text-blue-600 ml-auto"></ion-icon>
                    </a>
                    
                    <a href="imagenes_medicas.php" class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-200 group">
                        <ion-icon name="images" class="text-2xl text-purple-600 mr-4 group-hover:scale-110 transition-transform"></ion-icon>
                        <div>
                            <p class="font-semibold text-gray-800">Gestionar Imágenes</p>
                            <p class="text-sm text-gray-600">Sube y administra tus imágenes médicas</p>
                        </div>
                        <ion-icon name="chevron-forward" class="text-purple-600 ml-auto"></ion-icon>
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas del Paciente -->
        <div class="mt-8 grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Citas Totales</p>
                        <p class="text-2xl font-bold text-emerald-600">
                            <?php 
                            try {
                                $total_citas = $db_queries->getAppointmentsByUserId($uid);
                                echo count($total_citas);
                            } catch (Exception $e) {
                                echo "0";
                            }
                            ?>
                        </p>
                    </div>
                    <ion-icon name="calendar" class="text-3xl text-emerald-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Imágenes Subidas</p>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php 
                            try {
                                $total_imagenes = $db_queries->getPatientImages($uid);
                                echo count($total_imagenes);
                            } catch (Exception $e) {
                                echo "0";
                            }
                            ?>
                        </p>
                    </div>
                    <ion-icon name="images" class="text-3xl text-blue-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Estado</p>
                        <p class="text-lg font-semibold text-green-600">Activo</p>
                    </div>
                    <ion-icon name="checkmark-circle" class="text-3xl text-green-500"></ion-icon>
                </div>
            </div>
        </div>
    </div>

    <script>
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
    </script>

</body>
</html>