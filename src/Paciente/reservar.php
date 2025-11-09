<?php
//Aqui puedes colocar el código para la página de citas de paciente

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
        
        if (!$user || empty($user)) {
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
        
        // Verificar si el usuario ya ha firmado un consentimiento informado
        $hasConsent = $db_queries->hasUserSignedConsent($uid);
        
    } catch (Exception $e) {
        error_log("Error en citas paciente: " . $e->getMessage());
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
    <title>Reservar Cita - PropielEquipo</title>
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
                    <a href="reservar.php" class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-lg font-medium">
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
                    <a href="dashboardpaciente.php" class="block text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="person" class="mr-2"></ion-icon>Perfil
                    </a>
                    <a href="reservar.php" class="block bg-emerald-100 text-emerald-700 px-4 py-2 rounded-lg font-medium">
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

    <!-- Header de Reservar Cita -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-2xl p-8 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Reservar Nueva Cita</h1>
                    <p class="text-emerald-100"><?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-emerald-200 text-sm">Agenda tu próxima consulta médica</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="calendar" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Formulario de Reserva -->
        <div class="max-w-2xl mx-auto">
            <?php 
            // Mostrar mensajes de consentimiento si vienen en URL
            if (isset($_GET['consent'])) {
                if ($_GET['consent'] === 'signed') {
                    echo '<div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-8">
                        <div class="flex items-center">
                            <ion-icon name="checkmark-circle" class="text-3xl text-green-600 mr-3"></ion-icon>
                            <div>
                                <h3 class="text-lg font-semibold text-green-800">¡Consentimiento Firmado Exitosamente!</h3>
                                <p class="text-green-700">Ya puedes proceder a reservar tu cita médica.</p>';
                    if (isset($_GET['file'])) {
                        echo '<p class="text-sm text-green-600 mt-1">Archivo generado: ' . htmlspecialchars($_GET['file']) . '</p>';
                    }
                    echo '</div></div></div>';
                } elseif ($_GET['consent'] === 'already_signed') {
                    echo '<div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                        <div class="flex items-center">
                            <ion-icon name="information-circle" class="text-3xl text-blue-600 mr-3"></ion-icon>
                            <div>
                                <h3 class="text-lg font-semibold text-blue-800">Consentimiento ya firmado</h3>
                                <p class="text-blue-700">Ya tienes un consentimiento informado vigente. Puedes proceder con tu reserva.</p>
                            </div>
                        </div>
                    </div>';
                }
            }
            ?>
            
            <?php if (!$hasConsent): ?>
            <!-- Notificación de Consentimiento Requerido -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-8">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <ion-icon name="warning" class="text-3xl text-amber-600"></ion-icon>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-semibold text-amber-800 mb-2">
                            Consentimiento Informado Requerido
                        </h3>
                        <p class="text-amber-700 mb-4">
                            Por regulaciones médicas y de seguridad, <strong>debes firmar un consentimiento informado</strong> 
                            antes de poder reservar citas médicas. Este documento es obligatorio y protege tanto al paciente 
                            como al profesional médico.
                        </p>
                        <div class="bg-amber-100 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-amber-800 mb-2">¿Por qué es necesario?</h4>
                            <ul class="text-sm text-amber-700 space-y-1">
                                <li>• <strong>Seguridad del paciente:</strong> Garantiza que comprendes los procedimientos</li>
                                <li>• <strong>Marco legal:</strong> Cumple con la normativa médica mexicana (NOM-001 y NOM-234)</li>
                                <li>• <strong>Transparencia:</strong> Establece claramente los términos del tratamiento</li>
                                <li>• <strong>Protección mutua:</strong> Protege tanto al paciente como al médico</li>
                            </ul>
                        </div>
                        <p class="text-sm text-amber-600">
                            <ion-icon name="information-circle" class="mr-1"></ion-icon>
                            Una vez firmado, podrás reservar todas las citas que necesites sin volver a firmar.
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-xl shadow-lg p-8 <?php echo !$hasConsent ? 'opacity-60' : ''; ?>">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        <?php echo $hasConsent ? 'Información de la Cita' : 'Formulario Bloqueado'; ?>
                    </h2>
                    <p class="text-gray-600">
                        <?php echo $hasConsent ? 'Completa los siguientes datos para reservar tu cita médica' : 'Firma el consentimiento informado para continuar'; ?>
                    </p>
                </div>

                <form id="reservation-form" action="php_action/reservar.php" method="POST" class="space-y-6 <?php echo !$hasConsent ? 'pointer-events-none' : ''; ?>">
                    <input type="hidden" id="user_id" name="user_id" value="<?php echo htmlspecialchars($uid); ?>">
                    
                    <!-- Seleccionar Servicio -->
                    <div>
                        <label for="service" class="block text-sm font-semibold text-gray-700 mb-2">
                            <ion-icon name="medical" class="mr-2 text-emerald-600"></ion-icon>
                            Seleccionar Servicio
                        </label>
                        <select id="service" name="service" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200">
                            <option value="">Selecciona un servicio</option>
                            <option value="dermatología">🔹 Dermatología - Cuidado de la piel</option>
                            <option value="podología">🔹 Podología - Cuidado de los pies</option>
                            <option value="tamiz">🔹 Tamizaje - Exámenes preventivos</option>
                        </select>
                    </div>

                    <!-- Seleccionar Médico -->
                    <div id="doctor-selection" class="hidden">
                        <label for="doctor" class="block text-sm font-semibold text-gray-700 mb-2">
                            <ion-icon name="person" class="mr-2 text-emerald-600"></ion-icon>
                            Seleccionar Médico
                        </label>
                        <select id="doctor" name="doctor" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200">
                            <option value="">Cargando médicos...</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Puedes elegir cualquier médico disponible o dejar que el sistema asigne automáticamente</p>
                    </div>
                    
                    <!-- Seleccionar Día -->
                    <div>
                        <label for="day" class="block text-sm font-semibold text-gray-700 mb-2">
                            <ion-icon name="calendar-outline" class="mr-2 text-emerald-600"></ion-icon>
                            Seleccionar Fecha
                        </label>
                        <input type="date" id="day" name="day" required
                               min="<?php echo date('Y-m-d'); ?>" 
                               max="<?php echo date('Y-m-d', strtotime('+1 month')); ?>"
                               onchange="validateDay(this)"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200">
                        <p class="text-sm text-gray-500 mt-1">Disponible de lunes a viernes • Máximo 1 cita por día</p>
                        <div id="date-status" class="mt-2 text-sm hidden"></div>
                    </div>
                    
                    <!-- Seleccionar Hora -->
                    <div>
                        <label for="time" class="block text-sm font-semibold text-gray-700 mb-2">
                            <ion-icon name="time-outline" class="mr-2 text-emerald-600"></ion-icon>
                            Seleccionar Hora
                        </label>
                        <select id="time" name="time" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200">
                            <option value="">Primero selecciona una fecha</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Horario de atención: 9:00 AM - 6:00 PM (solo horarios disponibles)</p>
                        <div id="availability-status" class="mt-2 text-sm hidden"></div>
                    </div>
                    
                    <!-- Información Adicional -->
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-emerald-800 mb-2">
                            <ion-icon name="information-circle" class="mr-2"></ion-icon>
                            Información Importante
                        </h3>
                        <ul class="text-sm text-emerald-700 space-y-1">
                            <li>• <strong>Límite:</strong> Solo 1 cita por día por paciente</li>
                            <li>• Llega 15 minutos antes de tu cita</li>
                            <li>• Trae tu documento de identidad</li>
                            <li>• Puedes cancelar hasta 24 horas antes</li>
                            <li>• Confirmaremos tu cita por teléfono</li>
                        </ul>
                    </div>
                    
                    <!-- Botones -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <?php if ($hasConsent): ?>
                            <button type="button" id="show-consent-btn"
                                    class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                                <ion-icon name="checkmark-circle" class="mr-2"></ion-icon>
                                Confirmar Reserva
                            </button>
                        <?php else: ?>
                            <div class="flex-1 text-center">
                                <a href="consentimiento.php" 
                                   class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200 mb-3"
                                   style="pointer-events: auto !important;">
                                    <ion-icon name="document-text" class="mr-2"></ion-icon>
                                    Firmar Consentimiento Informado
                                </a>
                                <p class="text-sm text-gray-500">
                                    Después de firmar, regresa aquí para continuar
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <a href="dashboardpaciente.php" 
                           class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                            <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Consentimiento Informado -->
    <div id="consent-modal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50 hidden p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <!-- Header del Modal -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 text-white p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <img class="h-12 w-12 rounded-full bg-white p-2" src="../Images/logopropieel.png" alt="PRO-PIEL">
                        <div class="ml-4">
                            <h2 class="text-2xl font-bold">PRO-PIEL</h2>
                            <p class="text-emerald-100 text-sm">CONSENTIMIENTO INFORMADO</p>
                        </div>
                    </div>
                    <button onclick="closeConsentModal()" class="text-white hover:text-gray-200 text-2xl">
                        <ion-icon name="close"></ion-icon>
                    </button>
                </div>
            </div>

            <!-- Contenido del Modal -->
            <div class="flex flex-col h-full max-h-[calc(90vh-120px)]">
                <!-- Área de scroll para el contenido -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="text-center mb-6">
                        <h3 id="consent-title" class="text-xl font-bold text-gray-800">DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA</h3>
                    </div>

                    <div class="space-y-4 text-sm text-gray-700 leading-relaxed">
                        <p>
                            Yo <strong id="patient-name-consent"><?php echo htmlspecialchars($nombre . ' ' . $apellido); ?></strong> autorizo al 
                            <strong id="doctor-name">Dr. Juan López</strong> especialista en <strong id="specialty-name">Medicina General</strong> con cédula <strong id="doctor-license">MED12345678</strong>, 
                            como mi médico tratante. Con mi número de teléfono <strong id="patient-phone-consent"><?php echo htmlspecialchars($telefono); ?></strong> 
                            y a la edad de <strong id="patient-age-consent"><?php echo htmlspecialchars($edad); ?> años</strong>, 
                            de sexo <strong id="patient-gender-consent"><?php echo htmlspecialchars($genero); ?></strong>; 
                            acudo a consulta externa de primera vez. Lo cual manifiesto consciente, sin presión y por voluntad propia.
                        </p>

                        <p>
                            Para lo cual <em>me interrogará sobre mi enfermedad y comorbilidades, me explorará el área afectada incluyendo el área genital si fuera necesario, lo cual lo hará siempre con la presencia de la enfermera. Asimismo, me solicitará estudios de laboratorio y hasta una biopsia de piel según mi enfermedad. Me prescribirá una receta médica en la que se indicarán los nombres de los medicamentos, forma de uso y tiempo que debo tomarlos; asimismo, si fuera necesario, mandará una cita subsecuente para valorar la evolución de mi enfermedad.</em>
                        </p>

                        <p>
                            Todo lo anterior apegado a la ética, profesionalismo y responsabilidad y con base en el principio de libertad prescriptiva, de acuerdo a lo establecido en las <strong>Normas Oficiales Mexicanas aplicables (NOM-001 y NOM-234)</strong>.
                        </p>
                    </div>

                    <!-- Información de confirmación -->
                    <div class="mt-8 border-t pt-6">
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                            <div class="flex items-center justify-center mb-3">
                                <ion-icon name="checkmark-circle" class="text-2xl text-emerald-600 mr-3"></ion-icon>
                                <h4 class="text-lg font-semibold text-emerald-800">Consentimiento Informado Previamente Firmado</h4>
                            </div>
                            <p class="text-sm text-emerald-700 text-center">
                                Ya has firmado el consentimiento informado requerido. 
                                Puedes proceder directamente con la confirmación de tu cita médica.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="border-t bg-gray-50 px-6 py-4">
                    <div class="flex flex-col sm:flex-row gap-3 justify-end">
                        <button type="button" 
                                onclick="closeConsentModal()" 
                                class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition duration-200">
                            Cancelar
                        </button>
                        
                        <button type="button" 
                                onclick="confirmReservation()" 
                                class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition duration-200">
                            Confirmar Cita
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let doctorsData = {}; // Cache para médicos por especialidad
        
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

        function validateDay(input) {
            const day = new Date(input.value).getDay();
            if (day === 5 || day === 6) { // Friday = 5, Saturday = 6
                alert("Por favor selecciona un día entre lunes y viernes.");
                input.value = "";
                return;
            }
            
            // Verificar que se haya seleccionado un servicio antes de cargar horarios
            const serviceSelect = document.getElementById('service');
            if (!serviceSelect.value) {
                alert("Por favor selecciona un servicio antes de elegir la fecha.");
                input.value = "";
                return;
            }
            
            // Verificar si el usuario ya tiene una cita en esta fecha
            if (input.value) {
                checkUserAppointmentOnDate(input.value).then(hasAppointment => {
                    if (hasAppointment) {
                        alert("⚠️ Ya tienes una cita reservada para esta fecha.\\n\\nPor política médica, solo se permite una cita por día por paciente.\\n\\nPor favor selecciona otra fecha o cancela tu cita existente desde 'Mis Citas'.");
                        input.value = "";
                        return;
                    }
                    
                    // Si no hay conflicto, cargar horarios disponibles
                    loadAvailableTimeSlots(input.value);
                });
            }
        }

        // Función para cargar horarios disponibles para una fecha específica
        async function loadAvailableTimeSlots(fecha) {
            const timeSelect = document.getElementById('time');
            const statusDiv = document.getElementById('availability-status');
            const serviceSelect = document.getElementById('service');
            const selectedService = serviceSelect.value;
            
            // Verificar que se haya seleccionado un servicio
            if (!selectedService) {
                timeSelect.innerHTML = '<option value="">Primero selecciona un servicio</option>';
                timeSelect.disabled = true;
                statusDiv.className = 'mt-2 text-sm text-amber-600';
                statusDiv.textContent = '⚠️ Selecciona un servicio para ver horarios disponibles';
                statusDiv.classList.remove('hidden');
                return;
            }
            
            // Mostrar estado de carga
            timeSelect.innerHTML = '<option value="">Verificando disponibilidad...</option>';
            timeSelect.disabled = true;
            statusDiv.className = 'mt-2 text-sm text-blue-600';
            statusDiv.textContent = `🔍 Verificando horarios disponibles para ${selectedService}...`;
            statusDiv.classList.remove('hidden');
            
            console.log('Verificando disponibilidad para fecha:', fecha, 'y servicio:', selectedService);
            
            try {
                // Limpiar el select
                timeSelect.innerHTML = '';
                timeSelect.disabled = false;
                
                // Agregar opción por defecto
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Selecciona una hora disponible';
                timeSelect.appendChild(defaultOption);
                
                let availableCount = 0;
                let occupiedCount = 0;
                
                // Verificar cada horario secuencialmente para la especialidad específica
                for (let hour = 9; hour <= 17; hour++) {
                    const horario = hour + ':00';
                    
                    try {
                        const response = await fetch(`php_action/check_availability.php?fecha=${encodeURIComponent(fecha)}&horario=${encodeURIComponent(horario)}&servicio=${encodeURIComponent(selectedService)}`);
                        const data = await response.json();
                        
                        console.log(`${horario} (${selectedService}):`, data);
                        
                        const option = document.createElement('option');
                        option.value = horario;
                        
                        const timeRange = `${horario} - ${(hour + 1)}:00`;
                        
                        if (data.success && data.available) {
                            option.textContent = `✅ ${timeRange} - Disponible`;
                            option.className = 'text-green-700';
                            availableCount++;
                        } else {
                            option.textContent = `❌ ${timeRange} - No disponible`;
                            option.className = 'text-red-500';
                            option.disabled = true;
                            occupiedCount++;
                        }
                        
                        timeSelect.appendChild(option);
                        
                    } catch (error) {
                        console.error(`Error verificando ${horario}:`, error);
                        
                        const option = document.createElement('option');
                        option.value = horario;
                        option.textContent = `⚠️ ${horario} - ${(hour + 1)}:00 - Error`;
                        option.className = 'text-yellow-600';
                        option.disabled = true;
                        timeSelect.appendChild(option);
                        occupiedCount++;
                    }
                }
                
                // Mostrar resumen de disponibilidad
                if (availableCount === 0) {
                    statusDiv.className = 'mt-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded p-2';
                    statusDiv.innerHTML = `
                        <div class="flex items-center">
                            <ion-icon name="close-circle" class="mr-2"></ion-icon>
                            <span><strong>Sin horarios disponibles</strong> para ${selectedService} el ${formatDate(fecha)}</span>
                        </div>
                        <p class="text-xs mt-1">Todos los horarios de ${selectedService} están ocupados. Intenta con otra fecha.</p>
                    `;
                } else {
                    statusDiv.className = 'mt-2 text-sm text-green-600 bg-green-50 border border-green-200 rounded p-2';
                    statusDiv.innerHTML = `
                        <div class="flex items-center">
                            <ion-icon name="checkmark-circle" class="mr-2"></ion-icon>
                            <span><strong>${availableCount} horario${availableCount > 1 ? 's' : ''} disponible${availableCount > 1 ? 's' : ''}</strong> para ${selectedService} el ${formatDate(fecha)}</span>
                        </div>
                        ${occupiedCount > 0 ? `<p class="text-xs mt-1">${occupiedCount} horario${occupiedCount > 1 ? 's' : ''} ya reservado${occupiedCount > 1 ? 's' : ''} para esta especialidad.</p>` : ''}
                    `;
                }
                
            } catch (error) {
                console.error('Error general al cargar horarios:', error);
                
                // Fallback: cargar horarios estáticos
                timeSelect.innerHTML = '<option value="">Selecciona una hora</option>';
                for (let hour = 9; hour <= 17; hour++) {
                    const option = document.createElement('option');
                    option.value = hour + ':00';
                    option.textContent = `${hour}:00 - ${(hour + 1)}:00`;
                    timeSelect.appendChild(option);
                }
                
                timeSelect.disabled = false;
                statusDiv.className = 'mt-2 text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded p-2';
                statusDiv.innerHTML = `
                    <div class="flex items-center">
                        <ion-icon name="warning" class="mr-2"></ion-icon>
                        <span>No se pudo verificar disponibilidad para ${selectedService}. Mostrando todos los horarios.</span>
                    </div>
                `;
            }
        }
        
        // Función auxiliar para formatear fechas
        function formatDate(dateString) {
            const date = new Date(dateString + 'T00:00:00');
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            return date.toLocaleDateString('es-ES', options);
        }
        
        // Función para verificar si el usuario ya tiene una cita en una fecha específica
        async function checkUserAppointmentOnDate(fecha) {
            try {
                const response = await fetch(`php_action/check_user_appointment.php?fecha=${encodeURIComponent(fecha)}`);
                const data = await response.json();
                
                const dateStatusDiv = document.getElementById('date-status');
                
                if (data.success) {
                    if (data.hasAppointment && data.appointmentDetails) {
                        // Mostrar información de la cita existente
                        const details = data.appointmentDetails;
                        dateStatusDiv.className = 'mt-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded p-2';
                        dateStatusDiv.innerHTML = `
                            <div class="flex items-center">
                                <ion-icon name="warning" class="mr-2"></ion-icon>
                                <span><strong>Ya tienes una cita este día</strong></span>
                            </div>
                            <p class="text-xs mt-1">
                                ${details.servicio} a las ${details.horario} • Estado: ${details.estado}
                            </p>
                            <p class="text-xs mt-1">
                                <a href="reservas.php" class="text-red-700 underline hover:text-red-800">
                                    Ver en "Mis Citas" →
                                </a>
                            </p>
                        `;
                        dateStatusDiv.classList.remove('hidden');
                        return true;
                    } else {
                        // Fecha disponible
                        dateStatusDiv.className = 'mt-2 text-sm text-green-600 bg-green-50 border border-green-200 rounded p-2';
                        dateStatusDiv.innerHTML = `
                            <div class="flex items-center">
                                <ion-icon name="checkmark-circle" class="mr-2"></ion-icon>
                                <span>Fecha disponible para nueva cita</span>
                            </div>
                        `;
                        dateStatusDiv.classList.remove('hidden');
                        return false;
                    }
                } else {
                    dateStatusDiv.classList.add('hidden');
                    return false;
                }
            } catch (error) {
                console.error('Error al verificar cita existente:', error);
                const dateStatusDiv = document.getElementById('date-status');
                dateStatusDiv.classList.add('hidden');
                return false; // En caso de error, permitir continuar
            }
        }

        // Función para cargar médicos cuando se selecciona un servicio
        async function loadDoctors(service) {
            const doctorSelection = document.getElementById('doctor-selection');
            const doctorSelect = document.getElementById('doctor');
            
            if (!service) {
                doctorSelection.classList.add('hidden');
                return;
            }
            
            // Mostrar el campo de selección de médico
            doctorSelection.classList.remove('hidden');
            doctorSelect.innerHTML = '<option value="">Cargando médicos...</option>';
            
            try {
                // Verificar cache primero
                if (doctorsData[service]) {
                    populateDoctorSelect(doctorsData[service], service);
                    return;
                }
                
                // Hacer fetch a la API
                const response = await fetch(`php_action/get_doctors.php?specialty=${encodeURIComponent(service)}`);
                const data = await response.json();
                
                if (data.success && data.doctors) {
                    // Guardar en cache
                    doctorsData[service] = data.doctors;
                    populateDoctorSelect(data.doctors, service);
                } else {
                    // Usar datos por defecto
                    const defaultDoctors = getDefaultDoctorsForService(service);
                    populateDoctorSelect(defaultDoctors, service);
                }
            } catch (error) {
                console.error('Error al cargar médicos:', error);
                const defaultDoctors = getDefaultDoctorsForService(service);
                populateDoctorSelect(defaultDoctors, service);
            }
        }
        
        // Función para poblar el select de médicos
        function populateDoctorSelect(doctors, service) {
            const doctorSelect = document.getElementById('doctor');
            
            // Limpiar opciones existentes
            doctorSelect.innerHTML = '';
            
            // Agregar opción "Cualquiera" (recomendada)
            const anyOption = document.createElement('option');
            anyOption.value = 'cualquiera';
            anyOption.textContent = '✨ Cualquier médico disponible (Recomendado)';
            anyOption.selected = true;
            doctorSelect.appendChild(anyOption);
            
            // Agregar separador visual
            const separator = document.createElement('option');
            separator.disabled = true;
            separator.textContent = '────────────────────────';
            doctorSelect.appendChild(separator);
            
            // Agregar médicos específicos
            doctors.forEach(doctor => {
                const option = document.createElement('option');
                option.value = doctor.id;
                option.textContent = `${doctor.titulo} ${doctor.nombre} ${doctor.apellido} - ${doctor.especialidad}`;
                option.dataset.doctorData = JSON.stringify(doctor);
                doctorSelect.appendChild(option);
            });
        }
        
        // Función para obtener médicos por defecto por servicio
        function getDefaultDoctorsForService(service) {
            const defaults = {
                'dermatología': [{
                    id: 0,
                    titulo: 'Dra.',
                    nombre: 'María',
                    apellido: 'García',
                    nombre_completo: 'Dra. María García',
                    especialidad: 'Dermatología',
                    cedula: 'DER12345678',
                    cedula_real: false
                }],
                'podología': [{
                    id: 0,
                    titulo: 'Dr.',
                    nombre: 'Juan',
                    apellido: 'López',
                    nombre_completo: 'Dr. Juan López',
                    especialidad: 'Podología',
                    cedula: 'POD12345678',
                    cedula_real: false
                }],
                'tamiz': [{
                    id: 0,
                    titulo: 'Dr.',
                    nombre: 'Carlos',
                    apellido: 'Mendoza',
                    nombre_completo: 'Dr. Carlos Mendoza',
                    especialidad: 'Medicina General',
                    cedula: 'TAM12345678',
                    cedula_real: false
                }]
            };
            
            return defaults[service] || [];
        }

        // Función para mostrar el modal de consentimiento con validación adicional
        function showConsentModal() {
            const service = document.getElementById('service').value;
            const doctor = document.getElementById('doctor').value;
            const day = document.getElementById('day').value;
            const time = document.getElementById('time').value;
            
            // Validar que todos los campos estén llenos
            if (!service || !doctor || !day || !time) {
                alert('Por favor completa todos los campos antes de continuar.');
                return;
            }
            
            // Verificar que el horario seleccionado esté disponible
            const timeOption = document.querySelector(`#time option[value="${time}"]`);
            if (timeOption && timeOption.disabled) {
                alert('El horario seleccionado ya no está disponible. Por favor selecciona otro horario.');
                return;
            }
            
            // Verificar disponibilidad una vez más antes de mostrar el modal
            verifyFinalAvailability(day, time).then(available => {
                if (available) {
                    // Actualizar información dinámica del modal según la especialidad y médico
                    updateModalContent(service, doctor);
                    
                    const modal = document.getElementById('consent-modal');
                    modal.classList.remove('hidden');
                    modal.style.display = 'flex';
                    
                    // Prevenir scroll del body
                    document.body.style.overflow = 'hidden';
                } else {
                    alert('Lo sentimos, el horario seleccionado acaba de ser reservado por otro paciente. Por favor selecciona otro horario.');
                    // Recargar horarios para la fecha
                    loadAvailableTimeSlots(day);
                }
            });
        }
        
        // Función para verificar disponibilidad final antes de confirmar
        async function verifyFinalAvailability(fecha, horario) {
            const serviceSelect = document.getElementById('service');
            const selectedService = serviceSelect.value;
            
            if (!selectedService) {
                return false;
            }
            
            try {
                const response = await fetch(`php_action/check_availability.php?fecha=${encodeURIComponent(fecha)}&horario=${encodeURIComponent(horario)}&servicio=${encodeURIComponent(selectedService)}`);
                const data = await response.json();
                return data.success && data.available;
            } catch (error) {
                console.error('Error al verificar disponibilidad final:', error);
                return true; // En caso de error, permitir continuar
            }
        }

        // Función para actualizar el contenido del modal según la especialidad y médico seleccionado
        async function updateModalContent(service, selectedDoctorId) {
            const consentTitle = document.getElementById('consent-title');
            const doctorName = document.getElementById('doctor-name');
            const specialtyName = document.getElementById('specialty-name');
            const doctorLicense = document.getElementById('doctor-license');
            
            // Títulos según especialidad
            const titles = {
                'dermatología': 'DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA DERMATOLÓGICA',
                'podología': 'DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA PODOLÓGICA',
                'tamiz': 'DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA DE TAMIZAJE'
            };
            
            consentTitle.textContent = titles[service] || 'DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA';
            
            // Si seleccionó "cualquiera", usar el primer médico disponible o datos por defecto
            if (selectedDoctorId === 'cualquiera') {
                try {
                    // Obtener médicos de la especialidad
                    let doctors = doctorsData[service];
                    
                    if (!doctors) {
                        const response = await fetch(`php_action/get_doctors.php?specialty=${encodeURIComponent(service)}`);
                        const data = await response.json();
                        doctors = data.success ? data.doctors : [];
                    }
                    
                    if (doctors && doctors.length > 0) {
                        const doctor = doctors[0];
                        doctorName.textContent = doctor.nombre_completo;
                        specialtyName.textContent = doctor.especialidad;
                        doctorLicense.textContent = doctor.cedula;
                        
                        const cedulaType = doctor.cedula_real ? 'real' : 'generada automáticamente';
                        console.log('Médico asignado automáticamente:', doctor, `(Cédula ${cedulaType})`);
                    } else {
                        updateModalContentFallback(service);
                    }
                } catch (error) {
                    console.error('Error al obtener médico automático:', error);
                    updateModalContentFallback(service);
                }
            } else {
                // Usar el médico específico seleccionado
                const doctorSelect = document.getElementById('doctor');
                const selectedOption = doctorSelect.querySelector(`option[value="${selectedDoctorId}"]`);
                
                if (selectedOption && selectedOption.dataset.doctorData) {
                    const doctor = JSON.parse(selectedOption.dataset.doctorData);
                    doctorName.textContent = doctor.nombre_completo;
                    specialtyName.textContent = doctor.especialidad;
                    doctorLicense.textContent = doctor.cedula;
                    
                    const cedulaType = doctor.cedula_real ? 'real' : 'generada automáticamente';
                    console.log('Médico seleccionado específicamente:', doctor, `(Cédula ${cedulaType})`);
                } else {
                    updateModalContentFallback(service);
                }
            }
        }
        
        // Función de fallback con datos estáticos
        function updateModalContentFallback(service) {
            const consentTitle = document.getElementById('consent-title');
            const doctorName = document.getElementById('doctor-name');
            const specialtyName = document.getElementById('specialty-name');
            const doctorLicense = document.getElementById('doctor-license');
            
            // Configurar información según el servicio seleccionado (datos por defecto)
            switch(service) {
                case 'dermatología':
                    consentTitle.textContent = 'DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA DERMATOLÓGICA';
                    doctorName.textContent = 'Dra. María García';
                    specialtyName.textContent = 'Dermatología';
                    doctorLicense.textContent = 'DER12345678';
                    break;
                    
                case 'podología':
                    consentTitle.textContent = 'DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA PODOLÓGICA';
                    doctorName.textContent = 'Dr. Juan López';
                    specialtyName.textContent = 'Podología';
                    doctorLicense.textContent = 'POD12345678';
                    break;
                    
                case 'tamiz':
                    consentTitle.textContent = 'DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA DE TAMIZAJE';
                    doctorName.textContent = 'Dr. Carlos Mendoza';
                    specialtyName.textContent = 'Medicina General';
                    doctorLicense.textContent = 'TAM12345678';
                    break;
                    
                default:
                    consentTitle.textContent = 'DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA';
                    doctorName.textContent = 'Dr. Juan López';
                    specialtyName.textContent = 'Medicina General';
                    doctorLicense.textContent = 'MED12345678';
                    break;
            }
            
            console.log('Usando datos por defecto para:', service, '(Cédula generada automáticamente)');
        }

        // Función para cerrar el modal
        function closeConsentModal() {
            const modal = document.getElementById('consent-modal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            
            // Restaurar scroll del body
            document.body.style.overflow = 'auto';
        }

        // Función para confirmar la reserva directamente
        function confirmReservation() {
            // Enviar el formulario directamente sin generar PDF
            const form = document.getElementById('reservation-form');
            form.submit();
        }

        // Validación en tiempo real del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('#reservation-form');
            const submitButton = document.getElementById('show-consent-btn');
            const serviceSelect = document.getElementById('service');
            const dayInput = document.getElementById('day');
            
            // Validación del formulario principal
            function validateForm() {
                const service = document.getElementById('service').value;
                const doctor = document.getElementById('doctor').value;
                const day = document.getElementById('day').value;
                const time = document.getElementById('time').value;
                
                // Verificar si el campo de médico está visible y si tiene valor
                const doctorSelection = document.getElementById('doctor-selection');
                const doctorRequired = !doctorSelection.classList.contains('hidden');
                
                // Verificar que el horario seleccionado no esté deshabilitado
                let timeAvailable = true;
                if (time) {
                    const timeOption = document.querySelector(`#time option[value="${time}"]`);
                    timeAvailable = timeOption && !timeOption.disabled;
                }
                
                if (service && day && time && timeAvailable && (!doctorRequired || doctor)) {
                    submitButton.classList.remove('opacity-50');
                    submitButton.disabled = false;
                } else {
                    submitButton.classList.add('opacity-50');
                    submitButton.disabled = true;
                }
            }
            
            // Event listeners para validación
            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);
            
            // Event listener para cargar médicos cuando cambia el servicio
            serviceSelect.addEventListener('change', function() {
                const service = this.value;
                const dayInput = document.getElementById('day');
                const timeSelect = document.getElementById('time');
                
                loadDoctors(service);
                
                // Si ya hay una fecha seleccionada, recargar horarios para el nuevo servicio
                if (dayInput.value && service) {
                    loadAvailableTimeSlots(dayInput.value);
                } else if (!service) {
                    // Si no hay servicio seleccionado, limpiar horarios
                    timeSelect.innerHTML = '<option value="">Primero selecciona un servicio</option>';
                    timeSelect.disabled = true;
                    const statusDiv = document.getElementById('availability-status');
                    statusDiv.classList.add('hidden');
                }
                
                validateForm();
            });
            
            // Event listener para cargar horarios cuando cambia la fecha
            dayInput.addEventListener('change', function() {
                validateForm();
            });
            
            // Event listener para el botón de mostrar consentimiento
            if (submitButton) {
                submitButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    <?php if ($hasConsent): ?>
                    showConsentModal();
                    <?php else: ?>
                    // Redirigir a la página de consentimiento
                    window.location.href = '../consentimientos/';
                    <?php endif; ?>
                });
            }
            
            // Estado inicial
            if (submitButton) {
                submitButton.classList.add('opacity-50');
                submitButton.disabled = true;
            }
            
            // Cerrar modal con tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeConsentModal();
                }
            });
        });
    </script>

</body>
</html>