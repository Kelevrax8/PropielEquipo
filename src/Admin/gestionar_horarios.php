<?php
// Gestión de Horarios de Doctores
session_start();

// Verificar que el usuario ha iniciado sesión y es admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    header("Location: login_admin.php");
    exit();
}

require_once '../database_connection.php';

// Obtener información del admin
$admin_id = $_SESSION['admin_id'];
$stmt = $conex->prepare("SELECT nombre, apellido FROM usuarios WHERE user_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Obtener lista de doctores
$stmt_doctores = $conex->query("SELECT user_id, nombre, apellido, cedula_profesional FROM usuarios WHERE rol = 1 ORDER BY nombre, apellido");
$doctores = [];
if ($stmt_doctores) {
    while ($doctor = $stmt_doctores->fetch_assoc()) {
        $doctores[] = $doctor;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Horarios - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body class="bg-gray-50">
    
    <?php include 'shared/navbar_admin.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-2xl p-8 text-white mb-8 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Gestión de Horarios</h1>
                    <p class="text-indigo-100">Configura los horarios de atención de cada doctor</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="time" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-lg mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button onclick="showTab('horarios')" id="tab-horarios" class="tab-button active px-6 py-4 text-sm font-medium border-b-2 border-indigo-500 text-indigo-600">
                        <ion-icon name="calendar" class="mr-2"></ion-icon>
                        Horarios Semanales
                    </button>
                    <button onclick="showTab('bloqueos')" id="tab-bloqueos" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        <ion-icon name="close-circle" class="mr-2"></ion-icon>
                        Bloqueos de Horarios
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab: Horarios Semanales -->
        <div id="content-horarios" class="tab-content">
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <ion-icon name="person" class="mr-2"></ion-icon>
                    Seleccionar Doctor
                </h2>
                <select id="doctor-select" onchange="loadDoctorSchedule(this.value)" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Selecciona un doctor</option>
                    <?php foreach ($doctores as $doctor): ?>
                        <option value="<?php echo $doctor['user_id']; ?>">
                            Dr(a). <?php echo htmlspecialchars($doctor['nombre'] . ' ' . $doctor['apellido']); ?>
                            <?php if ($doctor['cedula_profesional']): ?>
                                - Cédula: <?php echo htmlspecialchars($doctor['cedula_profesional']); ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="schedule-container" class="hidden">
                <!-- Form para agregar/editar horarios -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Configurar Horario Semanal</h3>
                    <form id="schedule-form" class="space-y-4">
                        <input type="hidden" id="doctor-id" name="doctor_id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Día de la semana -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Día de la Semana</label>
                                <select name="dia_semana" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="lunes">Lunes</option>
                                    <option value="martes">Martes</option>
                                    <option value="miercoles">Miércoles</option>
                                    <option value="jueves">Jueves</option>
                                    <option value="viernes">Viernes</option>
                                    <option value="sabado">Sábado</option>
                                    <option value="domingo">Domingo</option>
                                </select>
                            </div>

                            <!-- Intervalo -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Duración de cada cita (minutos)</label>
                                <select name="intervalo_minutos" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="30">30 minutos</option>
                                    <option value="45">45 minutos</option>
                                    <option value="60" selected>60 minutos (1 hora)</option>
                                    <option value="90">90 minutos (1.5 horas)</option>
                                    <option value="120">120 minutos (2 horas)</option>
                                </select>
                            </div>

                            <!-- Hora inicio -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Hora de Inicio</label>
                                <input type="time" name="hora_inicio" value="09:00" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Hora fin -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Hora de Fin</label>
                                <input type="time" name="hora_fin" value="18:00" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                <ion-icon name="add-circle" class="mr-2"></ion-icon>
                                Agregar Horario
                            </button>
                            <button type="button" onclick="applyToAllWeek()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                <ion-icon name="copy" class="mr-2"></ion-icon>
                                Aplicar a Toda la Semana
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de horarios actuales -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Horarios Configurados</h3>
                    <div id="schedules-list" class="space-y-3">
                        <p class="text-gray-500 text-center py-8">Cargando horarios...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Bloqueos de Horarios -->
        <div id="content-bloqueos" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <ion-icon name="person" class="mr-2"></ion-icon>
                    Seleccionar Doctor
                </h2>
                <select id="doctor-select-bloqueos" onchange="loadDoctorBlocks(this.value)" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Selecciona un doctor</option>
                    <?php foreach ($doctores as $doctor): ?>
                        <option value="<?php echo $doctor['user_id']; ?>">
                            Dr(a). <?php echo htmlspecialchars($doctor['nombre'] . ' ' . $doctor['apellido']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="blocks-container" class="hidden">
                <!-- Form para agregar bloqueos -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Nuevo Bloqueo de Horario</h3>
                    <form id="block-form" class="space-y-4">
                        <input type="hidden" id="doctor-id-block" name="doctor_id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Tipo de bloqueo -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Bloqueo</label>
                                <select name="tipo_bloqueo" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="vacaciones">Vacaciones</option>
                                    <option value="conferencia">Conferencia / Evento Médico</option>
                                    <option value="urgencia">Urgencia Personal</option>
                                    <option value="personal">Asunto Personal</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <!-- Fecha inicio -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Inicio</label>
                                <input type="date" name="fecha_inicio" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Fecha fin -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Fin</label>
                                <input type="date" name="fecha_fin" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- Hora inicio (opcional) -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Hora de Inicio (Opcional)</label>
                                <input type="time" name="hora_inicio" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <p class="text-xs text-gray-500 mt-1">Dejar vacío para bloquear todo el día</p>
                            </div>

                            <!-- Hora fin (opcional) -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Hora de Fin (Opcional)</label>
                                <input type="time" name="hora_fin" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <p class="text-xs text-gray-500 mt-1">Dejar vacío para bloquear todo el día</p>
                            </div>

                            <!-- Motivo -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Motivo del Bloqueo</label>
                                <textarea name="motivo" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Descripción opcional del motivo del bloqueo"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                            <ion-icon name="lock-closed" class="mr-2"></ion-icon>
                            Crear Bloqueo
                        </button>
                    </form>
                </div>

                <!-- Lista de bloqueos actuales -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Bloqueos Activos</h3>
                    <div id="blocks-list" class="space-y-3">
                        <p class="text-gray-500 text-center py-8">Cargando bloqueos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentDoctorId = null;
        let currentDoctorIdBlock = null;

        // Gestión de tabs
        function showTab(tabName) {
            // Ocultar todos los contenidos
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remover clase active de todos los botones
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });

            // Mostrar contenido seleccionado
            document.getElementById(`content-${tabName}`).classList.remove('hidden');

            // Activar botón seleccionado
            const activeButton = document.getElementById(`tab-${tabName}`);
            activeButton.classList.add('active', 'border-indigo-500', 'text-indigo-600');
            activeButton.classList.remove('border-transparent', 'text-gray-500');
        }

        // Cargar horarios del doctor
        async function loadDoctorSchedule(doctorId) {
            if (!doctorId) {
                document.getElementById('schedule-container').classList.add('hidden');
                return;
            }

            currentDoctorId = doctorId;
            document.getElementById('doctor-id').value = doctorId;
            document.getElementById('schedule-container').classList.remove('hidden');

            // Cargar horarios existentes
            try {
                const response = await fetch(`php_action/get_doctor_schedule.php?doctor_id=${doctorId}`);
                const data = await response.json();

                const listContainer = document.getElementById('schedules-list');

                if (!data.success || data.horarios.length === 0) {
                    listContainer.innerHTML = '<p class="text-gray-500 text-center py-8">No hay horarios configurados para este doctor</p>';
                    return;
                }

                // Mostrar horarios
                listContainer.innerHTML = data.horarios.map(horario => `
                    <div class="border border-gray-200 rounded-lg p-4 flex items-center justify-between hover:bg-gray-50 transition">
                        <div class="flex items-center space-x-4">
                            <div class="bg-indigo-100 text-indigo-600 rounded-lg p-3">
                                <ion-icon name="calendar" class="text-2xl"></ion-icon>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 capitalize">${horario.dia_semana}</h4>
                                <p class="text-sm text-gray-600">${horario.hora_inicio.substring(0,5)} - ${horario.hora_fin.substring(0,5)}</p>
                                <p class="text-xs text-gray-500">Citas de ${horario.intervalo_minutos} minutos</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full ${horario.activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${horario.activo ? 'Activo' : 'Inactivo'}
                            </span>
                            <button onclick="toggleScheduleStatus(${horario.id_horario}, ${!horario.activo})" class="text-gray-600 hover:text-indigo-600 p-2">
                                <ion-icon name="${horario.activo ? 'eye-off' : 'eye'}"></ion-icon>
                            </button>
                            <button onclick="deleteSchedule(${horario.id_horario})" class="text-red-600 hover:text-red-800 p-2">
                                <ion-icon name="trash"></ion-icon>
                            </button>
                        </div>
                    </div>
                `).join('');

            } catch (error) {
                console.error('Error cargando horarios:', error);
                alert('Error al cargar horarios del doctor');
            }
        }

        // Guardar nuevo horario
        document.getElementById('schedule-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);

            try {
                const response = await fetch('php_action/save_schedule.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('Horario guardado exitosamente');
                    loadDoctorSchedule(currentDoctorId);
                    e.target.reset();
                    document.getElementById('doctor-id').value = currentDoctorId;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al guardar horario');
            }
        });

        // Aplicar horario a toda la semana
        async function applyToAllWeek() {
            if (!confirm('¿Aplicar este horario a todos los días de la semana (Lunes-Viernes)?')) {
                return;
            }

            const form = document.getElementById('schedule-form');
            const hora_inicio = form.querySelector('[name="hora_inicio"]').value;
            const hora_fin = form.querySelector('[name="hora_fin"]').value;
            const intervalo = form.querySelector('[name="intervalo_minutos"]').value;

            const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

            for (const dia of dias) {
                const formData = new FormData();
                formData.append('doctor_id', currentDoctorId);
                formData.append('dia_semana', dia);
                formData.append('hora_inicio', hora_inicio);
                formData.append('hora_fin', hora_fin);
                formData.append('intervalo_minutos', intervalo);

                try {
                    await fetch('php_action/save_schedule.php', {
                        method: 'POST',
                        body: formData
                    });
                } catch (error) {
                    console.error(`Error guardando ${dia}:`, error);
                }
            }

            alert('Horarios aplicados a toda la semana');
            loadDoctorSchedule(currentDoctorId);
        }

        // Toggle estado del horario
        async function toggleScheduleStatus(scheduleId, newStatus) {
            try {
                const response = await fetch('php_action/toggle_schedule.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: scheduleId, activo: newStatus })
                });

                const data = await response.json();

                if (data.success) {
                    loadDoctorSchedule(currentDoctorId);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al cambiar estado del horario');
            }
        }

        // Eliminar horario
        async function deleteSchedule(scheduleId) {
            if (!confirm('¿Eliminar este horario? Las citas existentes no se verán afectadas.')) {
                return;
            }

            try {
                const response = await fetch('php_action/delete_schedule.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: scheduleId })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Horario eliminado');
                    loadDoctorSchedule(currentDoctorId);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar horario');
            }
        }

        // Cargar bloqueos del doctor
        async function loadDoctorBlocks(doctorId) {
            if (!doctorId) {
                document.getElementById('blocks-container').classList.add('hidden');
                return;
            }

            currentDoctorIdBlock = doctorId;
            document.getElementById('doctor-id-block').value = doctorId;
            document.getElementById('blocks-container').classList.remove('hidden');

            try {
                const response = await fetch(`php_action/get_doctor_blocks.php?doctor_id=${doctorId}`);
                const data = await response.json();

                const listContainer = document.getElementById('blocks-list');

                if (!data.success || data.bloqueos.length === 0) {
                    listContainer.innerHTML = '<p class="text-gray-500 text-center py-8">No hay bloqueos configurados</p>';
                    return;
                }

                // Mostrar bloqueos
                listContainer.innerHTML = data.bloqueos.map(bloqueo => {
                    const tipoColors = {
                        'vacaciones': 'bg-blue-100 text-blue-800',
                        'conferencia': 'bg-purple-100 text-purple-800',
                        'urgencia': 'bg-red-100 text-red-800',
                        'personal': 'bg-yellow-100 text-yellow-800',
                        'otro': 'bg-gray-100 text-gray-800'
                    };

                    return `
                        <div class="border border-gray-200 rounded-lg p-4 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-4">
                                <div class="bg-red-100 text-red-600 rounded-lg p-3">
                                    <ion-icon name="lock-closed" class="text-2xl"></ion-icon>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">${bloqueo.fecha_inicio} - ${bloqueo.fecha_fin}</h4>
                                    <p class="text-sm text-gray-600">
                                        ${bloqueo.hora_inicio ? bloqueo.hora_inicio.substring(0,5) + ' - ' + bloqueo.hora_fin.substring(0,5) : 'Todo el día'}
                                    </p>
                                    ${bloqueo.motivo ? `<p class="text-xs text-gray-500 mt-1">${bloqueo.motivo}</p>` : ''}
                                    <span class="inline-block mt-1 px-2 py-1 text-xs font-semibold rounded-full capitalize ${tipoColors[bloqueo.tipo_bloqueo]}">
                                        ${bloqueo.tipo_bloqueo}
                                    </span>
                                </div>
                            </div>
                            <button onclick="deleteBlock(${bloqueo.id_bloqueo})" class="text-red-600 hover:text-red-800 p-2">
                                <ion-icon name="trash"></ion-icon>
                            </button>
                        </div>
                    `;
                }).join('');

            } catch (error) {
                console.error('Error cargando bloqueos:', error);
                alert('Error al cargar bloqueos del doctor');
            }
        }

        // Guardar nuevo bloqueo
        document.getElementById('block-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);

            try {
                const response = await fetch('php_action/save_block.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('Bloqueo creado exitosamente');
                    loadDoctorBlocks(currentDoctorIdBlock);
                    e.target.reset();
                    document.getElementById('doctor-id-block').value = currentDoctorIdBlock;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al crear bloqueo');
            }
        });

        // Eliminar bloqueo
        async function deleteBlock(blockId) {
            if (!confirm('¿Eliminar este bloqueo?')) {
                return;
            }

            try {
                const response = await fetch('php_action/delete_block.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: blockId })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Bloqueo eliminado');
                    loadDoctorBlocks(currentDoctorIdBlock);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar bloqueo');
            }
        }
    </script>
</body>
</html>
