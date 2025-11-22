<?php
// Verificar Pagos - Tamizaje
session_start();

// Verificar autenticación
if (!isset($_SESSION['telefono']) || !isset($_SESSION['especialidades'])) {
    header('location: ../../../../Landing/login.html');
    exit();
}

// Obtener información del doctor
require_once '../../../database_connection.php';
require_once '../../../database_queries.php';

try {
    $db_queries = new PropielEquipoQueries();
    $user = $db_queries->getUserById($_SESSION['user_id']);
    
    if (!$user) {
        session_destroy();
        header('location: ../../../../Landing/login.html');
        exit();
    }
    
    $uid = $user['user_id'];
    $nombre = $user["nombre"];
    $apellido = $user["apellido"];
    
    // Verificar que el doctor tenga la especialidad de Tamizaje (id_especialidad = 3)
    $especialidades_ids = array_column($_SESSION['especialidades'], 'id_especialidad');
    if (!in_array(3, $especialidades_ids)) {
        header('location: ../dashboard_tamizaje.php');
        exit();
    }
    
    // Obtener citas pendientes de verificación de pago SOLO del doctor actual
    // Incluye tanto 'pendiente_pago' (sin comprobante) como 'pendiente' (con comprobante subido)
    $query = "SELECT c.*, 
                     u.nombre as paciente_nombre, 
                     u.apellido as paciente_apellido,
                     u.telefono as paciente_telefono
              FROM citas c
              JOIN usuarios u ON c.id_usuario = u.user_id
              WHERE c.id_doctor = $uid
              AND LOWER(c.servicio) = 'tamiz'
              AND c.estado IN ('pendiente_pago', 'pendiente')
              AND c.verificado_por IS NULL
              ORDER BY 
                CASE 
                    WHEN c.comprobante_pago IS NOT NULL THEN 0
                    ELSE 1
                END,
                c.fecha_pago DESC, 
                c.fecha_creacion DESC";
    
    $result = $conex->query($query);
    $citas_pendientes = [];
    $citas_sin_comprobante = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['comprobante_pago'] !== null) {
                $citas_pendientes[] = $row;
            } else {
                $citas_sin_comprobante[] = $row;
            }
        }
    }
    
    // Paginación para citas pendientes
    $page_pendientes = isset($_GET['page_pendientes']) ? max(1, intval($_GET['page_pendientes'])) : 1;
    $per_page = 5;
    $total_pendientes = count($citas_pendientes);
    $total_pages_pendientes = ceil($total_pendientes / $per_page);
    $offset_pendientes = ($page_pendientes - 1) * $per_page;
    $citas_pendientes_paginadas = array_slice($citas_pendientes, $offset_pendientes, $per_page);
    
    // Paginación para citas sin comprobante
    $page_sin_comprobante = isset($_GET['page_sin_comprobante']) ? max(1, intval($_GET['page_sin_comprobante'])) : 1;
    $total_sin_comprobante = count($citas_sin_comprobante);
    $total_pages_sin_comprobante = ceil($total_sin_comprobante / $per_page);
    $offset_sin_comprobante = ($page_sin_comprobante - 1) * $per_page;
    $citas_sin_comprobante_paginadas = array_slice($citas_sin_comprobante, $offset_sin_comprobante, $per_page);
    
    // Obtener citas ya verificadas (últimas 10)
    $query_verificadas = "SELECT c.*, 
                     u.nombre as paciente_nombre, 
                     u.apellido as paciente_apellido,
                     u.telefono as paciente_telefono,
                     v.nombre as verificador_nombre,
                     v.apellido as verificador_apellido
              FROM citas c
              JOIN usuarios u ON c.id_usuario = u.user_id
              LEFT JOIN usuarios v ON c.verificado_por = v.user_id
              WHERE c.id_doctor = $uid
              AND LOWER(c.servicio) = 'tamiz'
              AND c.verificado_por IS NOT NULL
              ORDER BY c.fecha_verificacion DESC
              LIMIT 10";
    
    $result_verificadas = $conex->query($query_verificadas);
    $citas_verificadas = [];
    if ($result_verificadas && $result_verificadas->num_rows > 0) {
        while ($row = $result_verificadas->fetch_assoc()) {
            $citas_verificadas[] = $row;
        }
    }
    
} catch (Exception $e) {
    error_log("Error en verificar pagos tamizaje: " . $e->getMessage());
    header('location: ../../../../Landing/login.html');
    exit();
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Pagos - Tamizaje - PropielEquipo</title>
    <link href="../../../output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
</head>
<body class="bg-gradient-to-r from-purple-50 to-purple-100 flex flex-col min-h-screen">
    
    <?php include '../../shared/navbar_doctor.php'; ?>

    <div class="container mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl p-8 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Verificación de Pagos - Tamizaje</h1>
                    <p class="text-purple-100">Dr. <?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-purple-200 text-sm">Revisa y aprueba comprobantes de pago</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="card" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <ion-icon name="checkmark-circle" class="text-green-500 text-2xl mr-3"></ion-icon>
                <p class="text-green-800"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <ion-icon name="close-circle" class="text-red-500 text-2xl mr-3"></ion-icon>
                <p class="text-red-800"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Pagos Pendientes</p>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo count($citas_pendientes); ?></p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-4">
                        <ion-icon name="time" class="text-yellow-600 text-3xl"></ion-icon>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Total a Verificar</p>
                        <p class="text-3xl font-bold text-purple-600">
                            $<?php 
                                $total = 0;
                                foreach ($citas_pendientes as $cita) {
                                    $total += $cita['monto'];
                                }
                                echo number_format($total, 2);
                            ?> MXN
                        </p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <ion-icon name="cash" class="text-purple-600 text-3xl"></ion-icon>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-semibold">Estado</p>
                        <p class="text-lg font-bold text-gray-700">
                            <?php echo count($citas_pendientes) > 0 ? 'Requiere Atención' : 'Todo al Día'; ?>
                        </p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <ion-icon name="checkmark-done" class="text-purple-600 text-3xl"></ion-icon>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payments List -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <ion-icon name="list" class="mr-2"></ion-icon>
                    Comprobantes Pendientes de Verificación
                </h2>
            </div>

            <div class="p-6">
                <?php if (count($citas_pendientes) > 0): ?>
                    <div class="space-y-6">
                        <?php foreach ($citas_pendientes_paginadas as $cita): ?>
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition duration-200">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                
                                <!-- Left Column: Appointment Info -->
                                <div>
                                    <div class="flex items-center mb-4">
                                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                            <ion-icon name="person" class="text-purple-600 text-2xl"></ion-icon>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-lg text-gray-800">
                                                <?php echo htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-500">Tel: <?php echo htmlspecialchars($cita['paciente_telefono']); ?></p>
                                        </div>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center">
                                            <ion-icon name="calendar" class="text-gray-400 mr-2"></ion-icon>
                                            <span class="text-gray-600">Fecha:</span>
                                            <span class="ml-2 font-semibold"><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></span>
                                        </div>
                                        <div class="flex items-center">
                                            <ion-icon name="time" class="text-gray-400 mr-2"></ion-icon>
                                            <span class="text-gray-600">Horario:</span>
                                            <span class="ml-2 font-semibold"><?php echo htmlspecialchars($cita['horario']); ?></span>
                                        </div>
                                        <div class="flex items-center">
                                            <ion-icon name="clipboard" class="text-gray-400 mr-2"></ion-icon>
                                            <span class="text-gray-600">Servicio:</span>
                                            <span class="ml-2 font-semibold capitalize"><?php echo htmlspecialchars($cita['servicio']); ?></span>
                                        </div>
                                        <div class="flex items-center">
                                            <ion-icon name="cash" class="text-gray-400 mr-2"></ion-icon>
                                            <span class="text-gray-600">Monto:</span>
                                            <span class="ml-2 font-bold text-green-600">$<?php echo number_format($cita['monto'], 2); ?> MXN</span>
                                        </div>
                                        <div class="flex items-center">
                                            <ion-icon name="document" class="text-gray-400 mr-2"></ion-icon>
                                            <span class="text-gray-600">Subido:</span>
                                            <span class="ml-2 text-gray-700"><?php echo date('d/m/Y H:i', strtotime($cita['fecha_pago'])); ?></span>
                                        </div>
                                    </div>

                                    <?php if (!empty($cita['notas_pago'])): ?>
                                    <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                                        <p class="text-xs text-yellow-700 font-semibold mb-1">Nota del Paciente:</p>
                                        <p class="text-sm text-yellow-800"><?php echo nl2br(htmlspecialchars($cita['notas_pago'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Right Column: Payment Proof -->
                                <div>
                                    <p class="text-sm font-semibold text-gray-700 mb-3">Comprobante de Pago:</p>
                                    <div class="border-2 border-gray-200 rounded-lg overflow-hidden">
                                        <?php 
                                        $comprobante_path = '../../../comprobantes_pago/' . $cita['comprobante_pago'];
                                        $file_ext = strtolower(pathinfo($cita['comprobante_pago'], PATHINFO_EXTENSION));
                                        ?>
                                        
                                        <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png'])): ?>
                                            <img src="<?php echo $comprobante_path; ?>" 
                                                 alt="Comprobante" 
                                                 class="w-full h-64 object-contain bg-gray-50 cursor-pointer"
                                                 onclick="verComprobanteCompleto('<?php echo $comprobante_path; ?>')">
                                            <p class="text-center text-xs text-gray-500 p-2 bg-gray-50">
                                                Haz clic para ver en tamaño completo
                                            </p>
                                        <?php elseif ($file_ext === 'pdf'): ?>
                                            <div class="flex items-center justify-center h-64 bg-gray-50">
                                                <div class="text-center">
                                                    <ion-icon name="document" class="text-6xl text-gray-400"></ion-icon>
                                                    <p class="text-gray-600 mt-2">Archivo PDF</p>
                                                    <a href="<?php echo $comprobante_path; ?>" 
                                                       target="_blank" 
                                                       class="inline-block mt-3 bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                                                        Abrir PDF
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="mt-4 grid grid-cols-2 gap-3">
                                        <button 
                                            onclick="verificarPago(<?php echo $cita['id_cita']; ?>, 'aprobar')"
                                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center transition duration-200">
                                            <ion-icon name="checkmark-circle" class="mr-2"></ion-icon>
                                            Aprobar
                                        </button>
                                        <button 
                                            onclick="verificarPago(<?php echo $cita['id_cita']; ?>, 'rechazar')"
                                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg flex items-center justify-center transition duration-200">
                                            <ion-icon name="close-circle" class="mr-2"></ion-icon>
                                            Rechazar
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Paginación -->
                    <?php if ($total_pages_pendientes > 1): ?>
                    <div class="mt-6 flex items-center justify-between border-t pt-4">
                        <div class="text-sm text-gray-700">
                            Mostrando <span class="font-medium"><?php echo $offset_pendientes + 1; ?></span>
                            a <span class="font-medium"><?php echo min($offset_pendientes + $per_page, $total_pendientes); ?></span>
                            de <span class="font-medium"><?php echo $total_pendientes; ?></span> pendientes
                        </div>
                        <div class="flex items-center space-x-2">
                            <?php if ($page_pendientes > 1): ?>
                            <a href="?page_pendientes=<?php echo $page_pendientes - 1; ?>" 
                               class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                                <ion-icon name="chevron-back"></ion-icon>
                            </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages_pendientes; $i++): ?>
                                <a href="?page_pendientes=<?php echo $i; ?>" 
                                   class="px-4 py-2 rounded-lg transition <?php echo $i === $page_pendientes ? 'bg-purple-600 text-white font-bold' : 'bg-gray-200 hover:bg-gray-300 text-gray-700'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page_pendientes < $total_pages_pendientes): ?>
                            <a href="?page_pendientes=<?php echo $page_pendientes + 1; ?>" 
                               class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                                <ion-icon name="chevron-forward"></ion-icon>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-12">
                        <ion-icon name="checkmark-done-circle" class="text-6xl text-green-500 mb-4"></ion-icon>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">No hay pagos pendientes</h3>
                        <p class="text-gray-500">Todos los comprobantes han sido verificados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Appointments Waiting for Payment Proof -->
        <?php if (count($citas_sin_comprobante) > 0): ?>
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <ion-icon name="hourglass" class="mr-2"></ion-icon>
                    Citas Esperando Comprobante de Pago
                </h2>
            </div>

            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach ($citas_sin_comprobante_paginadas as $cita): ?>
                    <div class="border border-amber-200 bg-amber-50 rounded-lg p-5">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center mr-3">
                                    <ion-icon name="person" class="text-amber-600 text-xl"></ion-icon>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">
                                        <?php echo htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-600">Tel: <?php echo htmlspecialchars($cita['paciente_telefono']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-4 text-sm">
                                <div class="flex items-center">
                                    <ion-icon name="calendar" class="text-gray-500 mr-1"></ion-icon>
                                    <span><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <ion-icon name="time" class="text-gray-500 mr-1"></ion-icon>
                                    <span><?php echo htmlspecialchars($cita['horario']); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <ion-icon name="cash" class="text-gray-500 mr-1"></ion-icon>
                                    <span class="font-semibold text-green-600">$<?php echo number_format($cita['monto'], 2); ?> MXN</span>
                                </div>
                            </div>
                            
                            <div class="bg-amber-200 text-amber-800 px-4 py-2 rounded-full text-sm font-semibold">
                                <ion-icon name="hourglass" class="mr-1"></ion-icon>
                                Esperando Comprobante
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación -->
                <?php if ($total_pages_sin_comprobante > 1): ?>
                <div class="mt-6 flex items-center justify-between border-t pt-4">
                    <div class="text-sm text-gray-700">
                        Mostrando <span class="font-medium"><?php echo $offset_sin_comprobante + 1; ?></span>
                        a <span class="font-medium"><?php echo min($offset_sin_comprobante + $per_page, $total_sin_comprobante); ?></span>
                        de <span class="font-medium"><?php echo $total_sin_comprobante; ?></span> en espera
                    </div>
                    <div class="flex items-center space-x-2">
                        <?php if ($page_sin_comprobante > 1): ?>
                        <a href="?page_sin_comprobante=<?php echo $page_sin_comprobante - 1; ?>" 
                           class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                            <ion-icon name="chevron-back"></ion-icon>
                        </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages_sin_comprobante; $i++): ?>
                            <a href="?page_sin_comprobante=<?php echo $i; ?>" 
                               class="px-4 py-2 rounded-lg transition <?php echo $i === $page_sin_comprobante ? 'bg-purple-600 text-white font-bold' : 'bg-gray-200 hover:bg-gray-300 text-gray-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page_sin_comprobante < $total_pages_sin_comprobante): ?>
                        <a href="?page_sin_comprobante=<?php echo $page_sin_comprobante + 1; ?>" 
                           class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                            <ion-icon name="chevron-forward"></ion-icon>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-6 bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <p class="text-sm text-purple-800">
                        <ion-icon name="information-circle" class="mr-1"></ion-icon>
                        <strong>Nota:</strong> Estas citas aparecerán aquí arriba una vez que los pacientes suban sus comprobantes de pago.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Verified Appointments History -->
        <?php if (!empty($citas_verificadas)): ?>
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="flex items-center mb-6">
                <ion-icon name="checkmark-circle" class="text-green-500 text-3xl mr-3"></ion-icon>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Historial de Verificaciones</h2>
                    <p class="text-gray-600 text-sm">Últimas 10 citas verificadas</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Paciente</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Fecha Cita</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Monto</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Estado</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Verificado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citas_verificadas as $cita): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="py-4 px-4">
                                <div>
                                    <p class="font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($cita['paciente_telefono']); ?></p>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-sm">
                                    <p class="font-semibold text-gray-700"><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></p>
                                    <p class="text-gray-500"><?php echo htmlspecialchars($cita['horario']); ?></p>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-green-600">$<?php echo number_format($cita['monto'], 2); ?></span>
                            </td>
                            <td class="py-4 px-4">
                                <?php if ($cita['estado'] == 'confirmada'): ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                        <ion-icon name="checkmark-circle" class="text-sm"></ion-icon>
                                        Aprobada
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                        <ion-icon name="close-circle" class="text-sm"></ion-icon>
                                        Rechazada
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-sm">
                                    <p class="text-gray-700"><?php echo date('d/m/Y', strtotime($cita['fecha_verificacion'])); ?></p>
                                    <p class="text-xs text-gray-500">
                                        Dr. <?php echo htmlspecialchars($cita['verificador_nombre'] . ' ' . $cita['verificador_apellido']); ?>
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal for full image view -->
    <div id="modalComprobante" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4" onclick="cerrarModal()">
            <div class="relative max-w-4xl max-h-full" onclick="event.stopPropagation()">
                <button onclick="cerrarModal()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 z-10">
                    <ion-icon name="close" class="text-3xl"></ion-icon>
                </button>
                <img id="imagenComprobanteCompleto" src="" alt="Comprobante" class="max-w-full max-h-screen rounded-lg">
            </div>
        </div>
    </div>

    <script>
        function verificarPago(idCita, accion) {
            const mensaje = accion === 'aprobar' 
                ? '¿Estás seguro de APROBAR este pago? La cita será confirmada.' 
                : '¿Estás seguro de RECHAZAR este pago? El paciente deberá subir otro comprobante.';
            
            if (confirm(mensaje)) {
                window.location.href = '../../php_action/verificar_pago.php?id_cita=' + idCita + '&accion=' + accion + '&especialidad=tamizaje';
            }
        }

        function verComprobanteCompleto(rutaImagen) {
            document.getElementById('imagenComprobanteCompleto').src = rutaImagen;
            const modal = document.getElementById('modalComprobante');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }

        function cerrarModal() {
            const modal = document.getElementById('modalComprobante');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModal();
            }
        });
    </script>
</body>
</html>
