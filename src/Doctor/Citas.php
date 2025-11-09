<?php
//Aqui puedes colocar el código para la página de citas de doctor

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
        error_log("Error en citas doctor: " . $e->getMessage());
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
    <header class="bg-zinc-100">
        <nav class="flex justify-between items-center w-[92%] mx-auto">
            <div>
                <img class="w-16 cursor-pointer" src="../../src/Images/logopropieel.png" alt="...">
            </div>
            <div class="nav-links duration-500 md:static absolute bg-zinc-100 md:min-h-fit min-h-[26vh] left-0 top-[-100%] md:w-auto w-full flex items-center px-5">
                <ul class="flex md:flex-row flex-col md:items-center md:gap-[4vw] gap-8">
                    <li>
                        <a class="md:hover:bg-emerald-200/50 md:p-6" href="dashboarddoc.php">Perfil</a>
                    </li>
                    <li>
                        <a class="md:hover:bg-emerald-200/50 md:p-6" href="Citas.php">Registro Citas</a>
                    </li>
                    <li>
                        <a class="md:hover:bg-emerald-200/50 md:p-6" href="#"></a>
                    </li>
                </ul>
            </div>
            <div class="flex items-center gap-6">
            <form action="php_action/logout.php"><button class="bg-zinc-100 hover:bg-emerald-200/50 text-black px-5 py-2 rounded-full">Cerrar Sesión</button></form>
                <ion-icon onclick="onToggleMenu(this)" name="menu" class="text-3xl cursor-pointer md:hidden"></ion-icon>
            </div>
        </nav>
    </header>

    <script>
        const navLinks = document.querySelector('.nav-links')
        function onToggleMenu(e){
            e.name = e.name === 'menu' ? 'close' : 'menu'
            navLinks.classList.toggle('top-[9%]')
        }
    </script>

    <h1 class="text-center font-bold text-4xl mt-5 mb-4">Citas de Hoy</h1>

<div class="overflow-x-auto flex justify-center">
    <table class="w-3/4 bg-white border border-gray-200">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="py-3 px-6 text-center">ID Cita</th>
                <th class="py-3 px-6 text-center">Paciente</th>
                <th class="py-3 px-6 text-center">Fecha</th>
                <th class="py-3 px-6 text-center">Horario</th>
                <th class="py-3 px-6 text-center">Servicio</th>
                <th class="py-3 px-6 text-center">Estado</th>
                <th class="py-3 px-6 text-center">Observaciones</th>
                <th class="py-3 px-6 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php
                try {
                    $currentDate = date('Y-m-d');
                    $appointments = $db_queries->getAppointmentsWithPatientInfo($currentDate);
                    
                    if (empty($appointments)) {
                        echo "<tr><td colspan='8' class='py-3 px-6 text-center text-gray-500'>No hay citas programadas para hoy</td></tr>";
                    } else {
                        foreach ($appointments as $appointment) {
                            $observaciones = htmlspecialchars($appointment['notas'] ?? '');
                            $observacionesCortas = strlen($observaciones) > 50 ? substr($observaciones, 0, 50) . '...' : $observaciones;
                            ?>
                            <tr class="border-b border-gray-200 text-center hover:bg-gray-50">
                                <td class="py-3 px-6"><?php echo htmlspecialchars($appointment['id_cita']); ?></td>
                                <td class="py-3 px-6">
                                    <div class="text-sm">
                                        <div class="font-medium"><?php echo htmlspecialchars($appointment['paciente_nombre'] . ' ' . $appointment['paciente_apellido']); ?></div>
                                        <div class="text-gray-500"><?php echo htmlspecialchars($appointment['paciente_telefono']); ?></div>
                                    </div>
                                </td>
                                <td class="py-3 px-6"><?php echo htmlspecialchars($appointment['fecha']); ?></td>
                                <td class="py-3 px-6"><?php echo htmlspecialchars($appointment['horario']); ?></td>
                                <td class="py-3 px-6">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars(ucfirst($appointment['servicio'])); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-6">
                                    <?php 
                                    $estado = $appointment['estado'];
                                    $colorEstado = '';
                                    switch($estado) {
                                        case 'pendiente':
                                            $colorEstado = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'confirmada':
                                            $colorEstado = 'bg-green-100 text-green-800';
                                            break;
                                        case 'completada':
                                            $colorEstado = 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'cancelada':
                                            $colorEstado = 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            $colorEstado = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $colorEstado; ?>">
                                        <?php echo htmlspecialchars(ucfirst($estado)); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-6">
                                    <div class="text-sm text-gray-600" title="<?php echo $observaciones; ?>">
                                        <?php echo $observacionesCortas ?: 'Sin observaciones'; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-6">
                                    <button onclick="openObservationsModal(<?php echo $appointment['id_cita']; ?>)" 
                                            class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                        <ion-icon name="create" class="mr-1"></ion-icon>
                                        Editar
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                } catch (Exception $e) {
                    echo "<tr><td colspan='8' class='py-3 px-6 text-center text-red-500'>Error al cargar las citas</td></tr>";
                }
            ?>
        </tbody>
    </table>
</div>

<h1 class="text-center font-bold text-4xl mt-5">Citas de Mañana</h1>

<div class="overflow-x-auto flex justify-center mt-8">
    <table class="w-3/4 bg-white border border-gray-200">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="py-3 px-6 text-center">ID Cita</th>
                <th class="py-3 px-6 text-center">Paciente</th>
                <th class="py-3 px-6 text-center">Fecha</th>
                <th class="py-3 px-6 text-center">Horario</th>
                <th class="py-3 px-6 text-center">Servicio</th>
                <th class="py-3 px-6 text-center">Estado</th>
                <th class="py-3 px-6 text-center">Observaciones</th>
                <th class="py-3 px-6 text-center">Acciones</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php
                try {
                    $nextDate = date('Y-m-d', strtotime('+1 day'));
                    $appointments = $db_queries->getAppointmentsWithPatientInfo($nextDate);
                    
                    if (empty($appointments)) {
                        echo "<tr><td colspan='8' class='py-3 px-6 text-center text-gray-500'>No hay citas programadas para mañana</td></tr>";
                    } else {
                        foreach ($appointments as $appointment) {
                            $observaciones = htmlspecialchars($appointment['notas'] ?? '');
                            $observacionesCortas = strlen($observaciones) > 50 ? substr($observaciones, 0, 50) . '...' : $observaciones;
                            ?>
                            <tr class="border-b border-gray-200 text-center hover:bg-gray-50">
                                <td class="py-3 px-6"><?php echo htmlspecialchars($appointment['id_cita']); ?></td>
                                <td class="py-3 px-6">
                                    <div class="text-sm">
                                        <div class="font-medium"><?php echo htmlspecialchars($appointment['paciente_nombre'] . ' ' . $appointment['paciente_apellido']); ?></div>
                                        <div class="text-gray-500"><?php echo htmlspecialchars($appointment['paciente_telefono']); ?></div>
                                    </div>
                                </td>
                                <td class="py-3 px-6"><?php echo htmlspecialchars($appointment['fecha']); ?></td>
                                <td class="py-3 px-6"><?php echo htmlspecialchars($appointment['horario']); ?></td>
                                <td class="py-3 px-6">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars(ucfirst($appointment['servicio'])); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-6">
                                    <?php 
                                    $estado = $appointment['estado'];
                                    $colorEstado = '';
                                    switch($estado) {
                                        case 'pendiente':
                                            $colorEstado = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'confirmada':
                                            $colorEstado = 'bg-green-100 text-green-800';
                                            break;
                                        case 'completada':
                                            $colorEstado = 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'cancelada':
                                            $colorEstado = 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            $colorEstado = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $colorEstado; ?>">
                                        <?php echo htmlspecialchars(ucfirst($estado)); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-6">
                                    <div class="text-sm text-gray-600" title="<?php echo $observaciones; ?>">
                                        <?php echo $observacionesCortas ?: 'Sin observaciones'; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-6">
                                    <button onclick="openObservationsModal(<?php echo $appointment['id_cita']; ?>)" 
                                            class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded text-sm transition duration-200">
                                        <ion-icon name="create" class="mr-1"></ion-icon>
                                        Editar
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                } catch (Exception $e) {
                    echo "<tr><td colspan='8' class='py-3 px-6 text-center text-red-500'>Error al cargar las citas</td></tr>";
                }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal para Observaciones Médicas -->
<div id="observations-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
        <!-- Header del Modal -->
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold">Observaciones Médicas</h2>
                    <p id="patient-info" class="text-emerald-100 text-sm">Cargando información del paciente...</p>
                </div>
                <button onclick="closeObservationsModal()" class="text-white hover:text-gray-200 text-2xl">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>
        </div>

        <!-- Contenido del Modal -->
        <div class="p-6">
            <!-- Información de la Cita -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Información de la Cita</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-600">Cita ID:</strong>
                        <span id="modal-appointment-id" class="ml-2">-</span>
                    </div>
                    <div>
                        <strong class="text-gray-600">Paciente:</strong>
                        <span id="modal-patient-name" class="ml-2">-</span>
                    </div>
                    <div>
                        <strong class="text-gray-600">Fecha:</strong>
                        <span id="modal-appointment-date" class="ml-2">-</span>
                    </div>
                    <div>
                        <strong class="text-gray-600">Hora:</strong>
                        <span id="modal-appointment-time" class="ml-2">-</span>
                    </div>
                    <div>
                        <strong class="text-gray-600">Servicio:</strong>
                        <span id="modal-appointment-service" class="ml-2">-</span>
                    </div>
                    <div>
                        <strong class="text-gray-600">Estado:</strong>
                        <span id="modal-appointment-status" class="ml-2">-</span>
                    </div>
                </div>
            </div>

            <!-- Área de Observaciones -->
            <div class="mb-6">
                <label for="observations-textarea" class="block text-sm font-semibold text-gray-700 mb-2">
                    <ion-icon name="medical" class="mr-2 text-emerald-600"></ion-icon>
                    Observaciones y Diagnóstico
                </label>
                <textarea 
                    id="observations-textarea" 
                    rows="8" 
                    placeholder="Escriba aquí las observaciones médicas, diagnóstico, tratamiento recomendado, evolución del paciente, etc..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"
                ></textarea>
                <p class="text-sm text-gray-500 mt-1">
                    <ion-icon name="information-circle" class="mr-1"></ion-icon>
                    Ingrese información detallada sobre el diagnóstico, tratamiento y recomendaciones para el paciente.
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

        <!-- Footer del Modal -->
        <div class="border-t bg-gray-50 px-6 py-4">
            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <button 
                    type="button" 
                    onclick="closeObservationsModal()" 
                    class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition duration-200">
                    Cancelar
                </button>
                
                <button 
                    type="button" 
                    id="save-observations-btn"
                    onclick="saveObservations()" 
                    class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition duration-200">
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
            const response = await fetch(`php_action/update_observations.php?id_cita=${appointmentId}`);
            const data = await response.json();
            
            if (data.success && data.appointment) {
                const appointment = data.appointment;
                
                // Actualizar información en el modal
                document.getElementById('modal-appointment-id').textContent = appointment.id_cita;
                document.getElementById('modal-patient-name').textContent = 
                    `${appointment.paciente_nombre} ${appointment.paciente_apellido}`;
                document.getElementById('modal-appointment-date').textContent = appointment.fecha;
                document.getElementById('modal-appointment-time').textContent = appointment.horario;
                document.getElementById('modal-appointment-service').textContent = appointment.servicio;
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
            const response = await fetch('php_action/update_observations.php', {
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
        // Buscar la fila correspondiente en ambas tablas
        const tables = document.querySelectorAll('table tbody');
        
        tables.forEach(tbody => {
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                const firstCell = row.querySelector('td');
                if (firstCell && firstCell.textContent.trim() === appointmentId.toString()) {
                    // Encontrar la celda de observaciones (columna 7, índice 6)
                    const observationsCell = row.children[6];
                    if (observationsCell) {
                        const observacionesCortas = observations.length > 50 ? 
                            observations.substring(0, 50) + '...' : observations;
                        
                        observationsCell.innerHTML = `
                            <div class="text-sm text-gray-600" title="${observations.replace(/"/g, '&quot;')}">
                                ${observacionesCortas || 'Sin observaciones'}
                            </div>
                        `;
                    }
                }
            });
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
