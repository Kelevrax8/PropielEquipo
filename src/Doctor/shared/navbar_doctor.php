<?php
// Verificar especialidades del doctor logueado
if (!isset($_SESSION['especialidades']) || empty($_SESSION['especialidades'])) {
    header('location: ../../Landing/login.html');
    exit();
}

$especialidades = $_SESSION['especialidades'];
$es_multiespecialista = count($especialidades) > 1;

// Get pending payments count for badge
$pending_count = 0;
$appointments_count = 0;
if (isset($_SESSION['user_id'])) {
    require_once(__DIR__ . '/../../database_queries.php');
    $db_queries = new PropielEquipoQueries();
    
    // If single specialty, count for that specialty only
    if (!$es_multiespecialista) {
        $especialidad_nombre = $especialidades[0]['nombre_especialidad'];
        $pending_count = $db_queries->getPendingPaymentsCount($_SESSION['user_id'], $especialidad_nombre);
        $appointments_count = $db_queries->getNewAppointmentsCount($_SESSION['user_id'], $especialidad_nombre);
    } else {
        // If multi-specialty, count all
        $pending_count = $db_queries->getPendingPaymentsCount($_SESSION['user_id']);
        $appointments_count = $db_queries->getNewAppointmentsCount($_SESSION['user_id']);
    }
}
?>

<!-- Navbar moderno para doctores -->
<nav class="bg-gradient-to-r from-blue-600 to-blue-800 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo y marca -->
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <?php 
                    // Determinar la ruta correcta al logo según la ubicación del archivo actual
                    $logo_path = '';
                    if (strpos($_SERVER['PHP_SELF'], '/especialidades/') !== false) {
                        // Estamos en una especialidad (ej: especialidades/dermatologia/)
                        $logo_path = '../../../Images/logopropieel.png';
                    } else {
                        // Estamos en el directorio raíz de Doctor
                        $logo_path = '../Images/logopropieel.png';
                    }
                    ?>
                    <img class="h-10 w-10 rounded-lg bg-white p-1" src="<?php echo $logo_path; ?>" alt="PropielEquipo">
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 text-white">
                        <h1 class="text-lg font-bold">PropielEquipo</h1>
                        <p class="text-xs text-blue-200">
                            <?php if ($es_multiespecialista): ?>
                                Doctor Multiespecialista
                            <?php else: ?>
                                Dr. en <?php echo $especialidades[0]['nombre_especialidad']; ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navegación escritorio -->
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">
                    <?php if ($es_multiespecialista): ?>
                        <!-- Doctor con múltiples especialidades - mostrar dashboard general -->
                        <?php 
                        // Determinar rutas base según la ubicación
                        $base_path = '';
                        if (strpos($_SERVER['PHP_SELF'], '/especialidades/') !== false) {
                            $base_path = '../../';
                        } else {
                            $base_path = '';
                        }
                        ?>
                        <a href="<?php echo $base_path; ?>dashboarddoc.php" 
                           class="text-blue-100 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                            <ion-icon name="home" class="mr-2"></ion-icon>
                            Dashboard
                        </a>
                        <a href="<?php echo $base_path; ?>Citas.php" 
                           class="text-blue-100 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center relative">
                            <ion-icon name="calendar" class="mr-2"></ion-icon>
                            Todas las Citas
                            <?php if ($appointments_count > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                    <?php echo $appointments_count > 9 ? '9+' : $appointments_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <!-- Doctor con una especialidad - mostrar opciones específicas -->
                        <?php 
                        $especialidad = $especialidades[0];
                        $codigo_especialidad = '';
                        
                        switch($especialidad['id_especialidad']) {
                            case 1: $codigo_especialidad = 'dermatologia'; break;
                            case 2: $codigo_especialidad = 'podologia'; break;
                            case 3: $codigo_especialidad = 'tamizaje'; break;
                        }
                        ?>
                        <a href="dashboard_<?php echo $codigo_especialidad; ?>.php" 
                           class="text-blue-100 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                            <ion-icon name="home" class="mr-2"></ion-icon>
                            Dashboard
                        </a>
                        <a href="citas_<?php echo $codigo_especialidad; ?>.php" 
                           class="text-blue-100 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center relative">
                            <ion-icon name="calendar" class="mr-2"></ion-icon>
                            Mis Citas
                            <?php if ($appointments_count > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                    <?php echo $appointments_count > 9 ? '9+' : $appointments_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                        <a href="pacientes_<?php echo $codigo_especialidad; ?>.php" 
                           class="text-blue-100 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                            <ion-icon name="people" class="mr-2"></ion-icon>
                            Pacientes
                        </a>
                        <a href="imagenes_<?php echo $codigo_especialidad; ?>.php" 
                           class="text-blue-100 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                            <ion-icon name="images" class="mr-2"></ion-icon>
                            Imágenes
                        </a>
                        <a href="consentimientos_<?php echo $codigo_especialidad; ?>.php" 
                           class="text-blue-100 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                            <ion-icon name="document" class="mr-2"></ion-icon>
                            Consentimientos
                        </a>
                    <?php endif; ?>
                    
                    <!-- Verificar Pagos - Visible para todos los doctores -->
                    <?php if (!$es_multiespecialista): ?>
                        <a href="verificar_pagos_<?php echo $codigo_especialidad; ?>.php" 
                           class="text-blue-100 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center relative">
                            <ion-icon name="card" class="mr-2"></ion-icon>
                            Verificar Pagos
                            <?php if ($pending_count > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                                    <?php echo $pending_count > 9 ? '9+' : $pending_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $base_path; ?>especialidades/verificar_pagos.php" 
                           class="text-blue-100 hover:bg-blue-500 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center relative">
                            <ion-icon name="card" class="mr-2"></ion-icon>
                            Verificar Pagos
                            <?php if ($pending_count > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                                    <?php echo $pending_count > 9 ? '9+' : $pending_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Perfil y logout -->
            <div class="hidden md:block">
                <div class="ml-4 flex items-center md:ml-6">
                    <!-- Información del doctor -->
                    <div class="text-right mr-4">
                        <p class="text-sm font-medium text-white">
                            Dr. <?php echo $_SESSION['apellido'] ?? 'Doctor'; ?>
                        </p>
                        <p class="text-xs text-blue-200">
                            <?php if ($es_multiespecialista): ?>
                                <?php 
                                $nombres = array_map(function($esp) { return $esp['nombre_especialidad']; }, $especialidades);
                                echo implode(', ', array_slice($nombres, 0, 2));
                                if (count($nombres) > 2) echo '...';
                                ?>
                            <?php else: ?>
                                <?php echo $especialidades[0]['nombre_especialidad']; ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Botón de logout -->
                    <?php 
                    // Determinar la ruta correcta al logout según la ubicación del archivo actual
                    $logout_path = '';
                    if (strpos($_SERVER['PHP_SELF'], '/especialidades/') !== false) {
                        // Estamos en una especialidad (ej: especialidades/dermatologia/, especialidades/podologia/, especialidades/tamizaje/)
                        $logout_path = '../../php_action/logout.php';
                    } else {
                        // Estamos en el directorio raíz de Doctor
                        $logout_path = 'php_action/logout.php';
                    }
                    ?>
                    <form action="<?php echo $logout_path; ?>" method="get">
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                            <ion-icon name="log-out" class="mr-2"></ion-icon>
                            Salir
                        </button>
                    </form>
                </div>
            </div>

            <!-- Botón menú móvil -->
            <div class="md:hidden">
                <button type="button" 
                        id="mobile-menu-button"
                        class="bg-blue-700 inline-flex items-center justify-center p-2 rounded-md text-blue-200 hover:text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                        aria-controls="mobile-menu" 
                        aria-expanded="false"
                        onclick="toggleMobileMenu()">
                    <span class="sr-only">Abrir menú principal</span>
                    <ion-icon name="menu" id="mobile-menu-icon" class="text-xl"></ion-icon>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú móvil -->
    <div class="md:hidden hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-blue-700">
            <!-- Información del doctor en móvil -->
            <div class="px-3 py-2 text-white border-b border-blue-600 mb-2">
                <p class="text-sm font-medium">Dr. <?php echo $_SESSION['nombre'] ?? 'Doctor'; ?></p>
                <p class="text-xs text-blue-200">
                    <?php if ($es_multiespecialista): ?>
                        Multiespecialista
                    <?php else: ?>
                        <?php echo $especialidades[0]['nombre_especialidad']; ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Enlaces de navegación móvil -->
            <?php if ($es_multiespecialista): ?>
                <!-- Doctor con múltiples especialidades -->
                <?php 
                // Determinar rutas base según la ubicación
                $base_path = '';
                if (strpos($_SERVER['PHP_SELF'], '/especialidades/') !== false) {
                    $base_path = '../../';
                } else {
                    $base_path = '';
                }
                ?>
                <a href="<?php echo $base_path; ?>dashboarddoc.php" 
                   class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center">
                    <ion-icon name="home" class="mr-3"></ion-icon>
                    Dashboard General
                </a>
                <a href="<?php echo $base_path; ?>Citas.php" 
                   class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center justify-between">
                    <div class="flex items-center">
                        <ion-icon name="calendar" class="mr-3"></ion-icon>
                        Todas las Citas
                    </div>
                    <?php if ($appointments_count > 0): ?>
                        <span class="bg-orange-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center">
                            <?php echo $appointments_count > 9 ? '9+' : $appointments_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <!-- Doctor con una especialidad -->
                <?php 
                $especialidad = $especialidades[0];
                $codigo_especialidad = '';
                
                switch($especialidad['id_especialidad']) {
                    case 1: $codigo_especialidad = 'dermatologia'; break;
                    case 2: $codigo_especialidad = 'podologia'; break;
                    case 3: $codigo_especialidad = 'tamizaje'; break;
                }
                ?>
                <a href="dashboard_<?php echo $codigo_especialidad; ?>.php" 
                   class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center">
                    <ion-icon name="home" class="mr-3"></ion-icon>
                    Dashboard
                </a>
                <a href="citas_<?php echo $codigo_especialidad; ?>.php" 
                   class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center justify-between">
                    <div class="flex items-center">
                        <ion-icon name="calendar" class="mr-3"></ion-icon>
                        Mis Citas
                    </div>
                    <?php if ($appointments_count > 0): ?>
                        <span class="bg-orange-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center">
                            <?php echo $appointments_count > 9 ? '9+' : $appointments_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="pacientes_<?php echo $codigo_especialidad; ?>.php" 
                   class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center">
                    <ion-icon name="people" class="mr-3"></ion-icon>
                    Mis Pacientes
                </a>
                <a href="imagenes_<?php echo $codigo_especialidad; ?>.php" 
                   class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center">
                    <ion-icon name="images" class="mr-3"></ion-icon>
                    Imágenes Médicas
                </a>
                <a href="consentimientos_<?php echo $codigo_especialidad; ?>.php" 
                   class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center">
                    <ion-icon name="document" class="mr-3"></ion-icon>
                    Consentimientos
                </a>
            <?php endif; ?>
            
            <!-- Verificar Pagos - Visible para todos los doctores -->
            <?php if (!$es_multiespecialista): ?>
                <a href="verificar_pagos_<?php echo $codigo_especialidad; ?>.php" 
                   class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center justify-between">
                    <div class="flex items-center">
                        <ion-icon name="card" class="mr-3"></ion-icon>
                        Verificar Pagos
                    </div>
                    <?php if ($pending_count > 0): ?>
                        <span class="bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center">
                            <?php echo $pending_count > 9 ? '9+' : $pending_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>especialidades/verificar_pagos.php" 
                   class="text-blue-100 hover:bg-blue-600 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center justify-between">
                    <div class="flex items-center">
                        <ion-icon name="card" class="mr-3"></ion-icon>
                        Verificar Pagos
                    </div>
                    <?php if ($pending_count > 0): ?>
                        <span class="bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center">
                            <?php echo $pending_count > 9 ? '9+' : $pending_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            
            <!-- Logout en móvil -->
            <div class="border-t border-blue-600 pt-2 mt-2">
                <form action="<?php echo $logout_path; ?>" method="get">
                    <button type="submit" 
                            class="w-full text-left text-blue-100 hover:bg-red-600 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center">
                        <ion-icon name="log-out" class="mr-3"></ion-icon>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

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
