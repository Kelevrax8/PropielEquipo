<?php
// Citas específicas para Dermatología
session_start();

// Verificar autenticación y especialidad
if (!isset($_SESSION['telefono']) || !isset($_SESSION['especialidades'])) {
    header('location: ../../../Landing/login.html');
    exit();
}

// Verificar que el doctor tiene especialidad en dermatología
$tiene_dermatologia = false;
foreach ($_SESSION['especialidades'] as $esp) {
    if ($esp['id_especialidad'] == 1) { // ID 1 = Dermatología
        $tiene_dermatologia = true;
        break;
    }
}

if (!$tiene_dermatologia) {
    header('location: ../../dashboarddoc.php');
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
        header('location: ../../../Landing/login.html');
        exit();
    }
    
    $uid = $user['user_id'];
    $nombre = $user["nombre"];
    $apellido = $user["apellido"];
    
    // Obtener todas las citas de dermatología asignadas a este doctor
    $citas_dermatologia = [];
    
    try {
        // Usar la nueva función que incluye información del paciente y filtrar por doctor
        $todas_citas = $db_queries->getAppointmentsWithPatientInfo(null, $uid);
        foreach ($todas_citas as $cita) {
            if (strtolower($cita['servicio']) === 'dermatología') {
                $citas_dermatologia[] = $cita;
            }
        }
        
        // Ordenar por fecha más reciente
        usort($citas_dermatologia, function($a, $b) {
            // Ordenar por fecha de creación (más reciente primero)
            return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
        });
        
    } catch (Exception $e) {
        error_log("Error obteniendo citas de dermatología: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Error en citas dermatología: " . $e->getMessage());
    header('location: ../../../Landing/login.html');
    exit();
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas Dermatología - PropielEquipo</title>
    <link href="../../../output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-blue-100 flex flex-col min-h-screen">
    
    <?php include '../../shared/navbar_doctor.php'; ?>

    <!-- Header de Citas de Dermatología -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Citas de Dermatología</h1>
                    <p class="text-blue-100">Dr. <?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-blue-200 text-sm">Gestión de citas dermatológicas</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="calendar" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Filtros y Estadísticas -->
        <?php 
        // Variables para cálculos de estadísticas con fecha y hora - VERSIÓN MEJORADA
        // Forzar zona horaria para evitar problemas
        date_default_timezone_set('America/Mexico_City'); // O la zona horaria que corresponda
        
        $datetime_actual = new DateTime();
        $hoy = $datetime_actual->format('Y-m-d'); // Usar el mismo objeto DateTime para consistencia
        ?>
        
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Citas</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo count($citas_dermatologia); ?></p>
                    </div>
                    <ion-icon name="calendar-outline" class="text-3xl text-blue-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pendientes Hoy</p>
                        <p class="text-2xl font-bold text-green-600">
                            <?php 
                            $citas_hoy_pendientes = array_filter($citas_dermatologia, function($cita) use ($hoy, $datetime_actual) {
                                // Usar comparación robusta de fechas
                                $fecha_cita_obj = DateTime::createFromFormat('Y-m-d', $cita['fecha']);
                                $fecha_hoy_obj = DateTime::createFromFormat('Y-m-d', $hoy);
                                
                                if (!$fecha_cita_obj || !$fecha_hoy_obj) return false;
                                if ($fecha_cita_obj->format('Y-m-d') !== $fecha_hoy_obj->format('Y-m-d')) return false;
                                
                                // Si es hoy, verificar que no haya pasado la hora
                                $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $cita['fecha'] . ' ' . $cita['horario']);
                                return $datetime_cita && $datetime_cita >= $datetime_actual;
                            });
                            echo count($citas_hoy_pendientes);
                            ?>
                        </p>
                    </div>
                    <ion-icon name="today" class="text-3xl text-green-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Próximas</p>
                        <p class="text-2xl font-bold text-yellow-600">
                            <?php 
                            $citas_futuras = array_filter($citas_dermatologia, function($cita) use ($datetime_actual) {
                                $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $cita['fecha'] . ' ' . $cita['horario']);
                                return $datetime_cita > $datetime_actual;
                            });
                            echo count($citas_futuras);
                            ?>
                        </p>
                    </div>
                    <ion-icon name="time" class="text-3xl text-yellow-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Completadas</p>
                        <p class="text-2xl font-bold text-emerald-600">
                            <?php 
                            $citas_completadas = array_filter($citas_dermatologia, function($cita) use ($datetime_actual) {
                                $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $cita['fecha'] . ' ' . $cita['horario']);
                                return $datetime_cita < $datetime_actual;
                            });
                            echo count($citas_completadas);
                            ?>
                        </p>
                    </div>
                    <ion-icon name="checkmark-circle" class="text-3xl text-emerald-500"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Tabla de Citas -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-blue-600 text-white p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h2 class="text-xl font-bold flex items-center">
                        <ion-icon name="list" class="mr-2"></ion-icon>
                        Citas de Dermatología
                    </h2>
                    
                    <!-- Week Navigator -->
                    <div class="flex items-center gap-2 bg-blue-700 rounded-lg px-4 py-2">
                        <button onclick="changeWeek(-1)" class="text-white hover:text-blue-200 transition">
                            <ion-icon name="arrow-back" class="text-xl"></ion-icon>
                        </button>
                        <span id="week-display" class="text-white font-medium text-sm sm:text-base min-w-[200px] text-center">
                            Cargando...
                        </span>
                        <button onclick="changeWeek(1)" class="text-white hover:text-blue-200 transition">
                            <ion-icon name="arrow-forward" class="text-xl"></ion-icon>
                        </button>
                        <button onclick="goToToday()" class="ml-2 text-white hover:text-blue-200 transition text-sm underline">
                            Hoy
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- View Toggle -->
            <div class="px-6 py-4 bg-gray-50 border-b flex gap-2">
                <button onclick="setViewMode('week')" id="week-view-btn" class="px-4 py-2 rounded-lg text-sm font-medium transition bg-blue-600 text-white">
                    <ion-icon name="calendar" class="mr-1"></ion-icon>
                    Vista Semanal
                </button>
                <button onclick="setViewMode('all')" id="all-view-btn" class="px-4 py-2 rounded-lg text-sm font-medium transition bg-gray-200 text-gray-700 hover:bg-gray-300">
                    <ion-icon name="list" class="mr-1"></ion-icon>
                    Todas las Citas
                </button>
            </div>
            
            <div class="overflow-x-auto" id="appointments-container">
                <!-- Week view will be rendered here -->
            </div>
            
            <!-- Pagination Controls -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200" id="pagination-info">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Mostrando <span id="showing-start">0</span> a <span id="showing-end">0</span> de <span id="total-appointments"><?php echo count($citas_dermatologia); ?></span> citas
                    </div>
                    <div class="flex gap-2" id="pagination-controls"></div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="dashboard_dermatologia.php" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                Volver al Dashboard
            </a>
            
            <a href="pacientes_dermatologia.php" 
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="people" class="mr-2"></ion-icon>
                Ver Pacientes
            </a>
            
            <a href="imagenes_dermatologia.php" 
               class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="images" class="mr-2"></ion-icon>
                Imágenes Médicas
            </a>
        </div>
    </div>

<!-- Modal para Observaciones Médicas -->
<div id="observations-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[95vh] flex flex-col">
        <!-- Header del Modal (Fijo) -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 sm:p-6 flex-shrink-0 rounded-t-lg">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg sm:text-xl font-bold truncate">Observaciones Dermatológicas</h2>
                    <p id="patient-info" class="text-blue-100 text-xs sm:text-sm truncate">Cargando información del paciente...</p>
                </div>
                <button onclick="closeObservationsModal()" class="ml-4 text-white hover:text-gray-200 text-xl sm:text-2xl flex-shrink-0">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>
        </div>

        <!-- Contenido del Modal (Con Scroll) -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6">
            <!-- Información de la Cita -->
            <div class="bg-blue-50 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2 sm:mb-3">Información de la Cita Dermatológica</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4 text-xs sm:text-sm">
                    <div class="break-words">
                        <strong class="text-gray-600">Cita ID:</strong>
                        <span id="modal-appointment-id" class="ml-2">-</span>
                    </div>
                    <div class="break-words">
                        <strong class="text-gray-600">Paciente:</strong>
                        <span id="modal-patient-name" class="ml-2">-</span>
                    </div>
                    <div class="break-words">
                        <strong class="text-gray-600">Fecha:</strong>
                        <span id="modal-appointment-date" class="ml-2">-</span>
                    </div>
                    <div class="break-words">
                        <strong class="text-gray-600">Hora:</strong>
                        <span id="modal-appointment-time" class="ml-2">-</span>
                    </div>
                    <div class="break-words">
                        <strong class="text-gray-600">Servicio:</strong>
                        <span id="modal-appointment-service" class="ml-2">Dermatología</span>
                    </div>
                    <div class="break-words">
                        <strong class="text-gray-600">Estado:</strong>
                        <span id="modal-appointment-status" class="ml-2">-</span>
                    </div>
                </div>
            </div>

            <!-- Área de Observaciones -->
            <div class="mb-4 sm:mb-6">
                <label for="observations-textarea" class="block text-sm font-semibold text-gray-700 mb-2">
                    <ion-icon name="medical" class="mr-2 text-blue-600"></ion-icon>
                    Observaciones y Diagnóstico Dermatológico
                </label>
                <textarea 
                    id="observations-textarea" 
                    rows="6"
                    placeholder="Escriba aquí las observaciones dermatológicas, diagnóstico de lesiones cutáneas, tratamiento recomendado, evolución de la piel, medicamentos tópicos, etc..."
                    class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm"
                ></textarea>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    <ion-icon name="information-circle" class="mr-1"></ion-icon>
                    Incluya información sobre lesiones, tratamientos dermatológicos, medicamentos y recomendaciones específicas para el cuidado de la piel.
                    <br><strong>Nota:</strong> Los estados de citas se calculan automáticamente basándose en fecha y hora actual.
                </p>
            </div>

            <!-- Estado de Guardado -->
            <div id="save-status" class="hidden mb-4">
                <div class="flex items-center text-sm">
                    <ion-icon name="checkmark-circle" class="text-green-500 mr-2"></ion-icon>
                    <span class="text-green-600">Observaciones guardadas correctamente</span>
                </div>
            </div>
        </div>

        <!-- Footer del Modal (Fijo) -->
        <div class="border-t bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 flex-shrink-0 rounded-b-lg">
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 justify-end">
                <button 
                    type="button" 
                    onclick="closeObservationsModal()" 
                    class="px-4 sm:px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition duration-200 text-sm sm:text-base">
                    Cancelar
                </button>
                
                <button 
                    type="button" 
                    id="save-observations-btn"
                    onclick="saveObservations()" 
                    class="px-4 sm:px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 text-sm sm:text-base">
                    <ion-icon name="save" class="mr-2"></ion-icon>
                    Guardar Observaciones
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentAppointmentId = null;
    
    // Store all appointments data in JavaScript
    const allAppointments = <?php echo json_encode($citas_dermatologia); ?>;
    const currentDate = new Date('<?php echo $datetime_actual->format('Y-m-d H:i:s'); ?>');
    
    // Week navigation variables
    let currentWeekStart = null;
    let viewMode = 'week'; // 'week' or 'all'
    
    // Pagination variables (for 'all' view)
    let currentPage = 1;
    const itemsPerPage = 10;
    
    // Initialize week to current week
    function initializeWeek() {
        const today = new Date(currentDate);
        currentWeekStart = getWeekStart(today);
        updateWeekDisplay();
    }
    
    // Get the Monday of a given date
    function getWeekStart(date) {
        const d = new Date(date);
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Adjust when day is Sunday
        return new Date(d.setDate(diff));
    }
    
    // Format date range for display
    function formatWeekRange(startDate) {
        const endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + 6);
        
        const options = { day: '2-digit', month: 'short' };
        const startStr = startDate.toLocaleDateString('es-MX', options);
        const endStr = endDate.toLocaleDateString('es-MX', options);
        
        return `${startStr} - ${endStr}`;
    }
    
    // Update week display text
    function updateWeekDisplay() {
        const weekDisplay = document.getElementById('week-display');
        weekDisplay.textContent = formatWeekRange(currentWeekStart);
    }
    
    // Change week
    function changeWeek(direction) {
        const newDate = new Date(currentWeekStart);
        newDate.setDate(newDate.getDate() + (direction * 7));
        currentWeekStart = newDate;
        updateWeekDisplay();
        renderView();
    }
    
    // Go to current week
    function goToToday() {
        const today = new Date(currentDate);
        currentWeekStart = getWeekStart(today);
        updateWeekDisplay();
        renderView();
    }
    
    // Set view mode
    function setViewMode(mode) {
        viewMode = mode;
        
        // Update button styles
        const weekBtn = document.getElementById('week-view-btn');
        const allBtn = document.getElementById('all-view-btn');
        
        if (mode === 'week') {
            weekBtn.className = 'px-4 py-2 rounded-lg text-sm font-medium transition bg-blue-600 text-white';
            allBtn.className = 'px-4 py-2 rounded-lg text-sm font-medium transition bg-gray-200 text-gray-700 hover:bg-gray-300';
        } else {
            weekBtn.className = 'px-4 py-2 rounded-lg text-sm font-medium transition bg-gray-200 text-gray-700 hover:bg-gray-300';
            allBtn.className = 'px-4 py-2 rounded-lg text-sm font-medium transition bg-blue-600 text-white';
            currentPage = 1; // Reset to first page when switching to 'all' view
        }
        
        renderView();
    }
    
    // Main render function
    function renderView() {
        if (viewMode === 'week') {
            renderWeekView();
        } else {
            renderAllView();
        }
    }
    
    // Render week view grouped by date
    function renderWeekView() {
        const container = document.getElementById('appointments-container');
        
        // Get appointments for current week (Monday to Sunday)
        const weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(weekEnd.getDate() + 7);
        weekEnd.setHours(0, 0, 0, 0); // Set to midnight
        
        const weekAppointments = allAppointments.filter(apt => {
            const aptDate = new Date(apt.fecha + 'T00:00:00'); // Add time to avoid timezone issues
            aptDate.setHours(0, 0, 0, 0); // Normalize to midnight
            
            const weekStartNormalized = new Date(currentWeekStart);
            weekStartNormalized.setHours(0, 0, 0, 0);
            
            // Check if appointment is within the week (Monday to Sunday inclusive)
            return aptDate >= weekStartNormalized && aptDate < weekEnd;
        });
        
        if (weekAppointments.length === 0) {
            container.innerHTML = `
                <div class="px-6 py-12 text-center">
                    <ion-icon name="calendar-outline" class="text-6xl text-gray-300 mb-4"></ion-icon>
                    <p class="text-lg text-gray-500">No hay citas en esta semana</p>
                    <p class="text-sm text-gray-400">Usa las flechas para navegar a otra semana</p>
                </div>
            `;
            
            // Hide pagination
            document.getElementById('pagination-info').classList.add('hidden');
            return;
        }
        
        // Group by date
        const groupedByDate = {};
        weekAppointments.forEach(apt => {
            const dateKey = apt.fecha;
            if (!groupedByDate[dateKey]) {
                groupedByDate[dateKey] = [];
            }
            groupedByDate[dateKey].push(apt);
        });
        
        // Sort dates
        const sortedDates = Object.keys(groupedByDate).sort();
        
        // Build HTML
        let html = '';
        
        sortedDates.forEach(dateKey => {
            const appointments = groupedByDate[dateKey];
            const date = new Date(dateKey + 'T00:00:00'); // Add time to avoid timezone issues
            const dateStr = date.toLocaleDateString('es-MX', { 
                weekday: 'long', 
                day: '2-digit', 
                month: 'long',
                year: 'numeric'
            });
            
            // Check if this is today
            const today = new Date(currentDate);
            const isToday = date.toDateString() === today.toDateString();
            
            html += `
                <div class="border-b border-gray-200 last:border-b-0">
                    <div class="bg-gradient-to-r ${isToday ? 'from-orange-50 to-orange-100' : 'from-gray-50 to-gray-100'} px-6 py-3 sticky top-0 z-10">
                        <h3 class="text-lg font-semibold ${isToday ? 'text-orange-800' : 'text-gray-800'} capitalize flex items-center">
                            ${isToday ? '<ion-icon name="today" class="mr-2 text-orange-600"></ion-icon>' : ''}
                            ${dateStr}
                            <span class="ml-3 text-sm font-normal ${isToday ? 'text-orange-600' : 'text-gray-600'}">
                                (${appointments.length} ${appointments.length === 1 ? 'cita' : 'citas'})
                            </span>
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100">
            `;
            
            // Sort appointments by time
            appointments.sort((a, b) => a.horario.localeCompare(b.horario));
            
            appointments.forEach(cita => {
                const status = getAppointmentStatus(cita);
                const observaciones = (cita.notas || '').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                const observacionesCortas = observaciones.length > 50 ? 
                    observaciones.substring(0, 50) + '...' : observaciones;
                
                html += `
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <!-- Time and Patient Info -->
                            <div class="flex items-start gap-4 flex-1">
                                <!-- Time Badge -->
                                <div class="flex-shrink-0">
                                    <div class="bg-blue-100 text-blue-800 font-bold text-lg px-3 py-2 rounded-lg text-center min-w-[70px]">
                                        ${cita.horario}
                                    </div>
                                </div>
                                
                                <!-- Patient Details -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-gray-900">
                                            ${cita.paciente_nombre} ${cita.paciente_apellido}
                                        </h4>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${status.color}">
                                            ${status.display}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        <ion-icon name="call" class="text-xs"></ion-icon>
                                        ${cita.paciente_telefono}
                                    </p>
                                    ${observaciones ? `
                                        <p class="text-sm text-gray-500 mt-2">
                                            <ion-icon name="document-text" class="text-xs"></ion-icon>
                                            ${observacionesCortas}
                                        </p>
                                    ` : ''}
                                </div>
                            </div>
                            
                            <!-- Action Button -->
                            <div class="flex-shrink-0">
                                <button onclick="openObservationsModal(${cita.id_cita})" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200 flex items-center w-full lg:w-auto justify-center">
                                    <ion-icon name="create" class="mr-2"></ion-icon>
                                    Ver/Editar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Update pagination info
        document.getElementById('pagination-info').classList.remove('hidden');
        document.getElementById('showing-start').textContent = 1;
        document.getElementById('showing-end').textContent = weekAppointments.length;
        document.getElementById('total-appointments').textContent = weekAppointments.length;
        document.getElementById('pagination-controls').innerHTML = '';
    }
    
    // Render all appointments view (original table with pagination)
    function renderAllView() {
        const container = document.getElementById('appointments-container');
        
        // Create table structure
        container.innerHTML = `
            <table class="w-full" id="appointments-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="appointments-tbody"></tbody>
            </table>
        `;
        
        renderAppointmentsTable();
    }
    
    // Calculate appointment status
    function getAppointmentStatus(appointment) {
        const appointmentDateTime = new Date(appointment.fecha + ' ' + appointment.horario);
        const appointmentDate = new Date(appointment.fecha + 'T00:00:00');
        const today = new Date(currentDate.toDateString());
        
        const isToday = appointmentDate.toDateString() === today.toDateString();
        const isPast = appointmentDateTime < currentDate;
        const isFuture = appointmentDateTime > currentDate;
        
        let statusDisplay = '';
        let statusColor = '';
        
        if (isToday && !isPast) {
            statusDisplay = 'Hoy - ' + appointment.horario;
            statusColor = 'bg-orange-100 text-orange-800';
        } else if (isFuture) {
            if (appointment.estado === 'confirmada') {
                statusDisplay = 'Confirmada';
                statusColor = 'bg-green-100 text-green-800';
            } else {
                statusDisplay = 'Pendiente';
                statusColor = 'bg-yellow-100 text-yellow-800';
            }
        } else {
            statusDisplay = 'Completada';
            statusColor = 'bg-gray-100 text-gray-800';
        }
        
        return { display: statusDisplay, color: statusColor };
    }
    
    // Render appointments table (for 'all' view)
    function renderAppointmentsTable() {
        const tbody = document.getElementById('appointments-tbody');
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageAppointments = allAppointments.slice(startIndex, endIndex);
        
        if (allAppointments.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="text-gray-500">
                            <ion-icon name="calendar-outline" class="text-6xl text-gray-300 mb-4"></ion-icon>
                            <p class="text-lg">No hay citas de dermatología registradas</p>
                            <p class="text-sm">Las citas aparecerán aquí cuando los pacientes las reserven</p>
                        </div>
                    </td>
                </tr>
            `;
            document.getElementById('pagination-info').classList.add('hidden');
            return;
        }
        
        tbody.innerHTML = pageAppointments.map(cita => {
            const status = getAppointmentStatus(cita);
            const observaciones = (cita.notas || '').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const observacionesCortas = observaciones.length > 50 ? 
                observaciones.substring(0, 50) + '...' : observaciones;
            
            const fecha = new Date(cita.fecha + 'T00:00:00');
            const fechaFormateada = fecha.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
            
            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            ${cita.paciente_nombre} ${cita.paciente_apellido}
                        </div>
                        <div class="text-sm text-gray-500">
                            Tel: ${cita.paciente_telefono}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${fechaFormateada}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${cita.horario}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${status.color}">
                            ${status.display}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600 max-w-xs" title="${observaciones}">
                            ${observacionesCortas || 'Sin observaciones'}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="openObservationsModal(${cita.id_cita})" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-200 flex items-center">
                            <ion-icon name="create" class="mr-1"></ion-icon>
                            Editar
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Update pagination info
        document.getElementById('pagination-info').classList.remove('hidden');
        document.getElementById('showing-start').textContent = allAppointments.length > 0 ? startIndex + 1 : 0;
        document.getElementById('showing-end').textContent = Math.min(endIndex, allAppointments.length);
        document.getElementById('total-appointments').textContent = allAppointments.length;
        
        renderPagination();
    }
    
    // Render appointments table - LEGACY, redirects to new logic
    function renderAppointments() {
        if (viewMode === 'week') {
            renderWeekView();
        } else {
            renderAppointmentsTable();
        }
    }
    
    // Render pagination controls
    function renderPagination() {
        const totalPages = Math.ceil(allAppointments.length / itemsPerPage);
        const paginationControls = document.getElementById('pagination-controls');
        
        if (totalPages <= 1) {
            paginationControls.innerHTML = '';
            return;
        }
        
        let buttons = [];
        
        // Previous button
        buttons.push(`
            <button onclick="changePage(${currentPage - 1})" 
                    ${currentPage === 1 ? 'disabled' : ''}
                    class="px-3 py-1 rounded ${currentPage === 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-100 border'} text-sm">
                Anterior
            </button>
        `);
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                buttons.push(`
                    <button onclick="changePage(${i})" 
                            class="px-3 py-1 rounded text-sm ${i === currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border'}">
                        ${i}
                    </button>
                `);
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                buttons.push('<span class="px-2 text-gray-500">...</span>');
            }
        }
        
        // Next button
        buttons.push(`
            <button onclick="changePage(${currentPage + 1})" 
                    ${currentPage === totalPages ? 'disabled' : ''}
                    class="px-3 py-1 rounded ${currentPage === totalPages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-100 border'} text-sm">
                Siguiente
            </button>
        `);
        
        paginationControls.innerHTML = buttons.join('');
    }
    
    // Change page
    function changePage(page) {
        const totalPages = Math.ceil(allAppointments.length / itemsPerPage);
        if (page < 1 || page > totalPages) return;
        
        currentPage = page;
        renderAppointments();
        
        // Scroll to table
        document.getElementById('appointments-table').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    // Initialize table on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeWeek();
        renderView();
    });

    // Abrir modal de observaciones
    async function openObservationsModal(appointmentId) {
        currentAppointmentId = appointmentId;
        const modal = document.getElementById('observations-modal');
        
        // Mostrar modal
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        try {
            // Cargar detalles de la cita
            const response = await fetch(`../../php_action/update_observations.php?id_cita=${appointmentId}`);
            const data = await response.json();
            
            if (data.success && data.appointment) {
                const appointment = data.appointment;
                
                // Actualizar información en el modal
                document.getElementById('modal-appointment-id').textContent = appointment.id_cita;
                document.getElementById('modal-patient-name').textContent = 
                    `${appointment.paciente_nombre} ${appointment.paciente_apellido}`;
                document.getElementById('modal-appointment-date').textContent = appointment.fecha;
                document.getElementById('modal-appointment-time').textContent = appointment.horario;
                document.getElementById('modal-appointment-status').textContent = appointment.estado;
                
                // Cargar observaciones existentes
                document.getElementById('observations-textarea').value = appointment.notas || '';
                
                // Actualizar información del paciente en el header
                document.getElementById('patient-info').textContent = 
                    `${appointment.paciente_nombre} ${appointment.paciente_apellido} - ${appointment.paciente_edad} años - Tel: ${appointment.paciente_telefono}`;
                    
            } else {
                alert('Error al cargar los detalles de la cita');
                closeObservationsModal();
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al cargar los detalles de la cita');
            closeObservationsModal();
        }
    }

    // Cerrar modal de observaciones
    function closeObservationsModal() {
        const modal = document.getElementById('observations-modal');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Limpiar formulario
        document.getElementById('observations-textarea').value = '';
        document.getElementById('save-status').classList.add('hidden');
        currentAppointmentId = null;
    }

    // Guardar observaciones
    async function saveObservations() {
        if (!currentAppointmentId) {
            alert('Error: No se ha seleccionado una cita');
            return;
        }
        
        const observations = document.getElementById('observations-textarea').value.trim();
        const saveBtn = document.getElementById('save-observations-btn');
        const originalText = saveBtn.innerHTML;
        
        // Mostrar indicador de carga
        saveBtn.innerHTML = '<ion-icon name="hourglass" class="mr-2"></ion-icon>Guardando...';
        saveBtn.disabled = true;
        
        try {
            const response = await fetch('../../php_action/update_observations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_cita: currentAppointmentId,
                    observaciones: observations
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Mostrar mensaje de éxito
                document.getElementById('save-status').classList.remove('hidden');
                
                // Actualizar la tabla sin recargar la página
                updateAppointmentInTable(currentAppointmentId, observations);
                
                // Cerrar modal después de un momento
                setTimeout(() => {
                    closeObservationsModal();
                }, 1500);
                
            } else {
                alert('Error al guardar observaciones: ' + (data.message || 'Error desconocido'));
            }
            
        } catch (error) {
            console.error('Error:', error);
            alert('Error al guardar observaciones. Por favor intenta nuevamente.');
        } finally {
            // Restaurar botón
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    }

    // Actualizar la fila de la cita en la tabla
    function updateAppointmentInTable(appointmentId, observations) {
        // Update in allAppointments array
        const appointment = allAppointments.find(a => a.id_cita == appointmentId);
        if (appointment) {
            appointment.notas = observations;
        }
        
        // Re-render current view
        renderView();
    }

    // Cerrar modal al hacer click fuera de él
    document.getElementById('observations-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeObservationsModal();
        }
    });

    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('observations-modal');
        
        if (!modal.classList.contains('hidden')) {
            // Escape para cerrar
            if (e.key === 'Escape') {
                closeObservationsModal();
            }
            
            // Ctrl+S para guardar
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveObservations();
            }
        }
    });
</script>

</body>
</html>
