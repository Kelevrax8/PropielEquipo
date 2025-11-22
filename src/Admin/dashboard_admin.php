<?php
session_start();

// Verificar sesión de administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    header('Location: login_admin.php?error=unauthorized');
    exit();
}

require_once '../database_connection.php';

// Obtener estadísticas del sistema
// Inicializar variables por defecto
$total_doctores = 0;
$total_pacientes = 0;
$total_citas = 0;
$pagos_pendientes = 0;
$total_pagos_mes = 0;
$monto_total_mes = 0;
$citas_hoy = 0;
$stmt_ultimos_pacientes = null;
$stmt_ultimas_citas = null;

try {
    // Total de doctores activos
    $stmt_doctores = $conex->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 1");
    if ($stmt_doctores) {
        $total_doctores = $stmt_doctores->fetch_assoc()['total'];
    }
    
    // Total de pacientes activos
    $stmt_pacientes = $conex->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 3");
    if ($stmt_pacientes) {
        $total_pacientes = $stmt_pacientes->fetch_assoc()['total'];
    }
    
    // Total de citas
    $stmt_citas = $conex->query("SELECT COUNT(*) as total FROM citas");
    if ($stmt_citas) {
        $total_citas = $stmt_citas->fetch_assoc()['total'];
    }
    
    // Pagos pendientes de verificación
    $stmt_pagos_pendientes = $conex->query("
        SELECT COUNT(*) as total 
        FROM citas 
        WHERE estado = 'pendiente' AND comprobante_pago IS NOT NULL
    ");
    if ($stmt_pagos_pendientes) {
        $pagos_pendientes = $stmt_pagos_pendientes->fetch_assoc()['total'];
    }
    
    // Pagos verificados este mes
    $stmt_pagos_mes = $conex->query("
        SELECT COUNT(*) as total, SUM(monto) as total_monto
        FROM citas 
        WHERE estado = 'confirmada' 
        AND MONTH(fecha_verificacion) = MONTH(CURRENT_DATE())
        AND YEAR(fecha_verificacion) = YEAR(CURRENT_DATE())
    ");
    if ($stmt_pagos_mes) {
        $pagos_mes = $stmt_pagos_mes->fetch_assoc();
        $total_pagos_mes = $pagos_mes['total'] ?? 0;
        $monto_total_mes = $pagos_mes['total_monto'] ?? 0;
    }
    
    // Citas de hoy
    $stmt_citas_hoy = $conex->query("
        SELECT COUNT(*) as total 
        FROM citas 
        WHERE DATE(fecha) = CURDATE()
    ");
    if ($stmt_citas_hoy) {
        $citas_hoy = $stmt_citas_hoy->fetch_assoc()['total'];
    }
    
    // Últimos registros de pacientes
    $stmt_ultimos_pacientes = $conex->query("
        SELECT nombre, apellido, telefono, fecha_creacion 
        FROM usuarios 
        WHERE rol = 3
        ORDER BY fecha_creacion DESC 
        LIMIT 5
    ");
    
    // Últimas citas
    $stmt_ultimas_citas = $conex->query("
        SELECT c.fecha, c.horario, c.estado, c.servicio,
               CONCAT(p.nombre, ' ', p.apellido) as paciente,
               CONCAT(d.nombre, ' ', d.apellido) as doctor
        FROM citas c
        JOIN usuarios p ON c.id_usuario = p.user_id
        JOIN usuarios d ON c.id_doctor = d.user_id
        ORDER BY c.fecha_creacion DESC
        LIMIT 5
    ");
    
} catch (Exception $e) {
    error_log("Error en dashboard admin: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - PropielEquipo</title>
    <link href="../output.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body class="bg-gray-50">

    <?php include 'shared/navbar_admin.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Panel de Administración
            </h1>
            <p class="text-gray-600">
                Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?>
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <!-- Doctores -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">Doctores</p>
                        <h3 class="text-3xl font-bold text-gray-900"><?php echo $total_doctores; ?></h3>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <ion-icon name="medical" class="text-3xl text-blue-600"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- Pacientes -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">Pacientes</p>
                        <h3 class="text-3xl font-bold text-gray-900"><?php echo $total_pacientes; ?></h3>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <ion-icon name="people" class="text-3xl text-green-600"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- Citas -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">Total Citas</p>
                        <h3 class="text-3xl font-bold text-gray-900"><?php echo $total_citas; ?></h3>
                        <p class="text-xs text-gray-500 mt-1">Hoy: <?php echo $citas_hoy; ?></p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full">
                        <ion-icon name="calendar" class="text-3xl text-purple-600"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- Pagos Pendientes -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">Pagos Pendientes</p>
                        <h3 class="text-3xl font-bold text-gray-900"><?php echo $pagos_pendientes; ?></h3>
                        <p class="text-xs text-gray-500 mt-1">Requieren verificación</p>
                    </div>
                    <div class="bg-red-100 p-4 rounded-full">
                        <ion-icon name="card" class="text-3xl text-red-600"></ion-icon>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            
            <!-- Ingresos del Mes -->
            <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Pagos Verificados Este Mes</h3>
                    <ion-icon name="trending-up" class="text-3xl"></ion-icon>
                </div>
                <div>
                    <p class="text-4xl font-bold mb-2">$<?php echo number_format($monto_total_mes, 2); ?> MXN</p>
                    <p class="text-red-100"><?php echo $total_pagos_mes; ?> pagos confirmados</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Accesos Rápidos</h3>
                <div class="space-y-3">
                    <a href="configuracion_pagos.php" class="flex items-center p-3 bg-red-50 rounded-lg hover:bg-red-100 transition">
                        <ion-icon name="card" class="text-2xl text-red-600 mr-3"></ion-icon>
                        <span class="font-semibold text-gray-700">Configurar Datos Bancarios</span>
                    </a>
                    <a href="gestionar_doctores.php" class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                        <ion-icon name="medical" class="text-2xl text-blue-600 mr-3"></ion-icon>
                        <span class="font-semibold text-gray-700">Gestionar Doctores</span>
                    </a>
                    <a href="gestionar_pacientes.php" class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition">
                        <ion-icon name="people" class="text-2xl text-green-600 mr-3"></ion-icon>
                        <span class="font-semibold text-gray-700">Gestionar Pacientes</span>
                    </a>
                    <a href="reportes_pagos.php" class="flex items-center p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                        <ion-icon name="bar-chart" class="text-2xl text-purple-600 mr-3"></ion-icon>
                        <span class="font-semibold text-gray-700">Ver Reportes</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Últimos Pacientes Registrados -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <ion-icon name="person-add" class="mr-2 text-green-600"></ion-icon>
                    Últimos Pacientes Registrados
                </h3>
                <div class="space-y-3">
                    <?php if ($stmt_ultimos_pacientes && $stmt_ultimos_pacientes->num_rows > 0): ?>
                        <?php while ($paciente = $stmt_ultimos_pacientes->fetch_assoc()): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-semibold text-gray-700">
                                        <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($paciente['telefono']); ?></p>
                                </div>
                                <span class="text-xs text-gray-500">
                                    <?php echo date('d/m/Y', strtotime($paciente['fecha_creacion'])); ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">No hay pacientes registrados aún</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Últimas Citas -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <ion-icon name="calendar" class="mr-2 text-purple-600"></ion-icon>
                    Últimas Citas Registradas
                </h3>
                <div class="space-y-3">
                    <?php if ($stmt_ultimas_citas && $stmt_ultimas_citas->num_rows > 0): ?>
                        <?php while ($cita = $stmt_ultimas_citas->fetch_assoc()): 
                            $badge_color = 'gray';
                            switch($cita['estado']) {
                                case 'pendiente': case 'pendiente_pago': $badge_color = 'yellow'; break;
                                case 'confirmada': $badge_color = 'green'; break;
                                case 'completada': $badge_color = 'blue'; break;
                                case 'cancelada': $badge_color = 'red'; break;
                            }
                        ?>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="font-semibold text-gray-700 text-sm">
                                        <?php echo htmlspecialchars($cita['paciente']); ?>
                                    </p>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-<?php echo $badge_color; ?>-100 text-<?php echo $badge_color; ?>-700">
                                        <?php echo htmlspecialchars($cita['estado']); ?>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Dr. <?php echo htmlspecialchars($cita['doctor']); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo htmlspecialchars($cita['servicio']); ?> - 
                                    <?php echo date('d/m/Y', strtotime($cita['fecha'])); ?> 
                                    <?php echo htmlspecialchars($cita['horario']); ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">No hay citas registradas aún</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
