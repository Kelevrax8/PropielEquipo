<?php
// Citas específicas para Tamizaje
session_start();

// Verificar autenticación y especialidad
if (!isset($_SESSION['telefono']) || !isset($_SESSION['especialidades'])) {
    header('location: ../../../Landing/login.html');
    exit();
}

// Verificar que el doctor tiene especialidad en tamizaje
$tiene_tamizaje = false;
foreach ($_SESSION['especialidades'] as $esp) {
    if ($esp['id_especialidad'] == 3) { // ID 3 = Tamizaje
        $tiene_tamizaje = true;
        break;
    }
}

if (!$tiene_tamizaje) {
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
    
    // Obtener todas las citas de tamizaje asignadas a este doctor
    $citas_tamizaje = [];
    
    try {
        $todas_citas = $db_queries->getAppointmentsWithPatientInfo(null, $uid);
        foreach ($todas_citas as $cita) {
            // Comparar con 'tamiz' (como está en la base de datos)
            if (strtolower($cita['servicio']) === 'tamiz') {
                $citas_tamizaje[] = $cita;
            }
        }
        
        // Ordenar por fecha más reciente
        usort($citas_tamizaje, function($a, $b) {
            return strtotime($b['fecha'] . ' ' . $b['horario']) - strtotime($a['fecha'] . ' ' . $a['horario']);
        });
        
    } catch (Exception $e) {
        error_log("Error obteniendo citas de tamizaje: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Error en citas tamizaje: " . $e->getMessage());
    header('location: ../../../Landing/login.html');
    exit();
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas Tamizaje - PropielEquipo</title>
    <link href="../../../output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
</head>
<body class="bg-gradient-to-r from-purple-50 to-purple-100 flex flex-col min-h-screen">
    
    <?php include '../../shared/navbar_doctor.php'; ?>

    <!-- Header de Citas de Tamizaje -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl p-6 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Citas de Tamizaje</h1>
                    <p class="text-purple-100">Dr. <?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-purple-200 text-sm">Gestión de citas de tamizaje y evaluación</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="search" class="text-6xl opacity-30"></ion-icon>
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
                        <p class="text-2xl font-bold text-purple-600"><?php echo count($citas_tamizaje); ?></p>
                    </div>
                    <ion-icon name="calendar-outline" class="text-3xl text-purple-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pendientes Hoy</p>
                        <p class="text-2xl font-bold text-green-600">
                            <?php 
                            $citas_hoy_pendientes = array_filter($citas_tamizaje, function($cita) use ($hoy, $datetime_actual) {
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
                            $citas_futuras = array_filter($citas_tamizaje, function($cita) use ($datetime_actual) {
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
                            $citas_completadas = array_filter($citas_tamizaje, function($cita) use ($datetime_actual) {
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
            <div class="bg-purple-600 text-white p-6">
                <h2 class="text-xl font-bold flex items-center">
                    <ion-icon name="list" class="mr-2"></ion-icon>
                    Todas las Citas de Tamizaje
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Cita</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($citas_tamizaje) > 0): ?>
                            <?php foreach ($citas_tamizaje as $cita): ?>
                                <?php 
                                $observaciones = htmlspecialchars($cita['notas'] ?? '');
                                $observacionesCortas = strlen($observaciones) > 50 ? substr($observaciones, 0, 50) . '...' : $observaciones;
                                
                                // Calcular estado basado en fecha y hora - VERSIÓN MEJORADA
                                $fecha_cita = $cita['fecha'];
                                $hora_cita = $cita['horario'];
                                
                                // Crear DateTime de la cita
                                $datetime_cita = DateTime::createFromFormat('Y-m-d H:i', $fecha_cita . ' ' . $hora_cita);
                                if (!$datetime_cita) {
                                    // Si falla el formato, intentar con otro formato común
                                    $datetime_cita = DateTime::createFromFormat('Y-m-d H:i:s', $fecha_cita . ' ' . $hora_cita . ':00');
                                }
                                
                                // Usar comparación de DateTime objects directamente para mayor precisión
                                $fecha_cita_obj = DateTime::createFromFormat('Y-m-d', $fecha_cita);
                                $fecha_hoy_obj = DateTime::createFromFormat('Y-m-d', $hoy);
                                
                                $es_hoy = ($fecha_cita_obj && $fecha_hoy_obj && $fecha_cita_obj->format('Y-m-d') === $fecha_hoy_obj->format('Y-m-d'));
                                $es_pasada = ($datetime_cita && $datetime_cita < $datetime_actual);
                                $es_futura = ($datetime_cita && $datetime_cita > $datetime_actual);
                                
                                // Determinar estado y color
                                $estado_display = '';
                                $color_estado = '';
                                
                                if ($es_hoy && !$es_pasada) {
                                    $estado_display = 'Hoy - ' . $hora_cita;
                                    $color_estado = 'bg-orange-100 text-orange-800';
                                } elseif ($es_futura) {
                                    $estado_display = 'Programada';
                                    $color_estado = 'bg-green-100 text-green-800';
                                } else {
                                    $estado_display = 'Completada';
                                    $color_estado = 'bg-gray-100 text-gray-800';
                                }
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #<?php echo htmlspecialchars($cita['id_cita']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Tel: <?php echo htmlspecialchars($cita['paciente_telefono']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($cita['fecha'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($cita['horario']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $color_estado; ?>">
                                            <?php echo $estado_display; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600 max-w-xs" title="<?php echo $observaciones; ?>">
                                            <?php echo $observacionesCortas ?: 'Sin observaciones'; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="openObservationsModal(<?php echo $cita['id_cita']; ?>)" 
                                                class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm transition duration-200 flex items-center">
                                            <ion-icon name="create" class="mr-1"></ion-icon>
                                            Editar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <ion-icon name="calendar-outline" class="text-6xl text-gray-300 mb-4"></ion-icon>
                                        <p class="text-lg">No hay citas de tamizaje registradas</p>
                                        <p class="text-sm">Las citas aparecerán aquí cuando los pacientes las reserven</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="dashboard_tamizaje.php" 
               class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                Volver al Dashboard
            </a>
            
            <a href="pacientes_tamizaje.php" 
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="people" class="mr-2"></ion-icon>
                Ver Pacientes
            </a>
            
            <a href="imagenes_tamizaje.php" 
               class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="images" class="mr-2"></ion-icon>
                Imágenes Médicas
            </a>
        </div>
    </div>

<!-- Modal para Observaciones Médicas -->
<div id="observations-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[95vh] flex flex-col">
        <!-- Header del Modal (Fijo) -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-4 sm:p-6 flex-shrink-0 rounded-t-lg">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg sm:text-xl font-bold truncate">Observaciones de Tamizaje</h2>
                    <p id="patient-info" class="text-purple-100 text-xs sm:text-sm truncate">Cargando información del paciente...</p>
                </div>
                <button onclick="closeObservationsModal()" class="ml-4 text-white hover:text-gray-200 text-xl sm:text-2xl flex-shrink-0">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>
        </div>

        <!-- Contenido del Modal (Con Scroll) -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6">
            <!-- Información de la Cita -->
            <div class="bg-purple-50 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2 sm:mb-3">Información de la Cita de Tamizaje</h3>
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
                        <span id="modal-appointment-service" class="ml-2">Tamizaje</span>
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
                    <ion-icon name="medical" class="mr-2 text-purple-600"></ion-icon>
                    Observaciones y Evaluación de Tamizaje
                </label>
                <textarea 
                    id="observations-textarea" 
                    rows="6"
                    placeholder="Escriba aquí las observaciones del tamizaje, evaluación de síntomas, factores de riesgo, recomendaciones de seguimiento, derivaciones, etc..."
                    class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none text-sm"
                ></textarea>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    <ion-icon name="information-circle" class="mr-1"></ion-icon>
                    Incluya información sobre evaluaciones de tamizaje, factores de riesgo identificados, recomendaciones preventivas y seguimiento requerido.
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
                    class="px-4 sm:px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition duration-200 text-sm sm:text-base">
                    <ion-icon name="save" class="mr-2"></ion-icon>
                    Guardar Observaciones
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentAppointmentId = null;

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
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const firstCell = row.querySelector('td');
            if (firstCell && firstCell.textContent.includes(appointmentId.toString())) {
                // Encontrar la celda de observaciones (columna 6, índice 5)
                const observationsCell = row.children[5];
                if (observationsCell) {
                    const observacionesCortas = observations.length > 50 ? 
                        observations.substring(0, 50) + '...' : observations;
                    
                    observationsCell.innerHTML = `
                        <div class="text-sm text-gray-600 max-w-xs" title="${observations.replace(/"/g, '&quot;')}">
                            ${observacionesCortas || 'Sin observaciones'}
                        </div>
                    `;
                }
            }
        });
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
