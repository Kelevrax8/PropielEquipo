<?php
// Verificar sesión de administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    header('Location: ../login_admin.php?error=unauthorized');
    exit();
}

// Obtener la página actual para resaltar en el navbar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="bg-gradient-to-r from-red-600 to-red-700 shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <!-- Logo y Título -->
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <ion-icon name="shield-checkmark" class="text-3xl text-white mr-3"></ion-icon>
                    <span class="text-white text-xl font-bold hidden sm:block">Admin Panel</span>
                </div>
            </div>

            <!-- Navigation Links (Desktop) -->
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">
                    <a href="dashboard_admin.php" 
                       class="<?php echo $current_page === 'dashboard_admin.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white px-3 py-2 rounded-md text-sm font-medium transition flex items-center">
                        <ion-icon name="home" class="mr-2"></ion-icon>
                        Dashboard
                    </a>
                    
                    <a href="configuracion_pagos.php" 
                       class="<?php echo $current_page === 'configuracion_pagos.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white px-3 py-2 rounded-md text-sm font-medium transition flex items-center">
                        <ion-icon name="card" class="mr-2"></ion-icon>
                        Pagos
                    </a>
                    
                    <a href="gestionar_doctores.php" 
                       class="<?php echo $current_page === 'gestionar_doctores.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white px-3 py-2 rounded-md text-sm font-medium transition flex items-center">
                        <ion-icon name="medical" class="mr-2"></ion-icon>
                        Doctores
                    </a>
                    
                    <a href="gestionar_pacientes.php" 
                       class="<?php echo $current_page === 'gestionar_pacientes.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white px-3 py-2 rounded-md text-sm font-medium transition flex items-center">
                        <ion-icon name="people" class="mr-2"></ion-icon>
                        Pacientes
                    </a>
                    
                    <a href="gestionar_horarios.php" 
                       class="<?php echo $current_page === 'gestionar_horarios.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white px-3 py-2 rounded-md text-sm font-medium transition flex items-center">
                        <ion-icon name="time" class="mr-2"></ion-icon>
                        Horarios
                    </a>
                    
                    <a href="reportes_pagos.php" 
                       class="<?php echo $current_page === 'reportes_pagos.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white px-3 py-2 rounded-md text-sm font-medium transition flex items-center">
                        <ion-icon name="bar-chart" class="mr-2"></ion-icon>
                        Reportes
                    </a>
                </div>
            </div>

            <!-- User Menu -->
            <div class="hidden md:block">
                <div class="ml-4 flex items-center md:ml-6">
                    <!-- Admin Info -->
                    <div class="text-white mr-4 text-sm">
                        <p class="font-semibold"><?php echo htmlspecialchars($_SESSION['admin_nombre']); ?></p>
                        <p class="text-red-200 text-xs">Administrador</p>
                    </div>
                    
                    <!-- Logout Button -->
                    <a href="php_action/logout_admin.php" 
                       class="bg-red-800 hover:bg-red-900 text-white px-4 py-2 rounded-md text-sm font-medium transition flex items-center">
                        <ion-icon name="log-out" class="mr-2"></ion-icon>
                        Salir
                    </a>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button type="button" 
                        onclick="toggleMobileMenu()"
                        class="bg-red-800 inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-red-900 focus:outline-none transition">
                    <ion-icon name="menu" id="mobile-menu-icon" class="text-2xl"></ion-icon>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="md:hidden hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <a href="dashboard_admin.php" 
               class="<?php echo $current_page === 'dashboard_admin.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white block px-3 py-2 rounded-md text-base font-medium flex items-center">
                <ion-icon name="home" class="mr-2"></ion-icon>
                Dashboard
            </a>
            
            <a href="configuracion_pagos.php" 
               class="<?php echo $current_page === 'configuracion_pagos.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white block px-3 py-2 rounded-md text-base font-medium flex items-center">
                <ion-icon name="card" class="mr-2"></ion-icon>
                Configuración de Pagos
            </a>
            
            <a href="gestionar_doctores.php" 
               class="<?php echo $current_page === 'gestionar_doctores.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white block px-3 py-2 rounded-md text-base font-medium flex items-center">
                <ion-icon name="medical" class="mr-2"></ion-icon>
                Gestionar Doctores
            </a>
            
            <a href="gestionar_pacientes.php" 
               class="<?php echo $current_page === 'gestionar_pacientes.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white block px-3 py-2 rounded-md text-base font-medium flex items-center">
                <ion-icon name="people" class="mr-2"></ion-icon>
                Gestionar Pacientes
            </a>
            
            <a href="gestionar_horarios.php" 
               class="<?php echo $current_page === 'gestionar_horarios.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white block px-3 py-2 rounded-md text-base font-medium flex items-center">
                <ion-icon name="time" class="mr-2"></ion-icon>
                Gestionar Horarios
            </a>
            
            <a href="reportes_pagos.php" 
               class="<?php echo $current_page === 'reportes_pagos.php' ? 'bg-red-800' : 'hover:bg-red-800'; ?> text-white block px-3 py-2 rounded-md text-base font-medium flex items-center">
                <ion-icon name="bar-chart" class="mr-2"></ion-icon>
                Reportes de Pagos
            </a>
            
            <div class="border-t border-red-500 my-2"></div>
            
            <div class="px-3 py-2 text-white">
                <p class="font-semibold"><?php echo htmlspecialchars($_SESSION['admin_nombre']); ?></p>
                <p class="text-red-200 text-sm">Administrador</p>
            </div>
            
            <a href="php_action/logout_admin.php" 
               class="bg-red-800 hover:bg-red-900 text-white block px-3 py-2 rounded-md text-base font-medium flex items-center">
                <ion-icon name="log-out" class="mr-2"></ion-icon>
                Cerrar Sesión
            </a>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    const icon = document.getElementById('mobile-menu-icon');
    
    menu.classList.toggle('hidden');
    
    if (menu.classList.contains('hidden')) {
        icon.name = 'menu';
    } else {
        icon.name = 'close';
    }
}
</script>
