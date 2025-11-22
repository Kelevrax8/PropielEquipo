<?php
session_start();
require_once('../database_connection.php');

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 3) {
    header('Location: ../Landing/login.html');
    exit();
}

// Get appointment ID
if (!isset($_GET['cita']) || empty($_GET['cita'])) {
    header('Location: reservas.php');
    exit();
}

$id_cita = intval($_GET['cita']);
$id_usuario = $_SESSION['user_id'];

// Verify the appointment belongs to this user
$query = "SELECT c.*, u.nombre, u.apellido 
          FROM citas c 
          JOIN usuarios u ON c.id_usuario = u.user_id 
          WHERE c.id_cita = ? AND c.id_usuario = ?";
$stmt = $conex->prepare($query);
$stmt->bind_param("ii", $id_cita, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: reservas.php');
    exit();
}

$cita = $result->fetch_assoc();

// Get bank account information
$query_banco = "SELECT * FROM configuracion_pagos WHERE activo = 1 LIMIT 1";
$result_banco = $conex->query($query_banco);
$datos_bancarios = $result_banco->fetch_assoc();

// Check if payment proof already uploaded
$comprobante_existente = !empty($cita['comprobante_pago']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Comprobante de Pago - PropielEquipo</title>
    <link href="../output.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 to-blue-50">

    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <img class="h-10 w-10 rounded-lg" src="../Images/logopropieel.png" alt="PropielEquipo">
                    <span class="ml-3 text-xl font-bold text-gray-800">PropielEquipo</span>
                </div>
                <a href="reservas.php" class="text-emerald-600 hover:text-emerald-800 font-medium">
                    <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                    Volver a Mis Citas
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">
        
        <?php if ($comprobante_existente): ?>
            <!-- Alert: Payment proof already uploaded -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg">
                <div class="flex items-center">
                    <ion-icon name="information-circle" class="text-blue-500 text-2xl mr-3"></ion-icon>
                    <div>
                        <p class="font-semibold text-blue-800">Comprobante ya enviado</p>
                        <p class="text-sm text-blue-600">Tu comprobante está siendo revisado por el personal médico.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Instructions - Moved to top -->
        <div class="bg-gradient-to-r from-emerald-500 to-blue-500 rounded-2xl shadow-xl p-6 mb-6 text-white">
            <h3 class="font-bold text-xl mb-4 flex items-center">
                <ion-icon name="information-circle" class="text-white mr-2 text-2xl"></ion-icon>
                ¿Cómo realizar el pago?
            </h3>
            <ol class="space-y-3 text-white">
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-7 h-7 bg-white text-emerald-600 rounded-full flex items-center justify-center font-bold text-sm mr-3">1</span>
                    <span>Realiza una transferencia SPEI a la CLABE indicada abajo</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-7 h-7 bg-white text-emerald-600 rounded-full flex items-center justify-center font-bold text-sm mr-3">2</span>
                    <span>Incluye tu nombre completo en el concepto de la transferencia</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-7 h-7 bg-white text-emerald-600 rounded-full flex items-center justify-center font-bold text-sm mr-3">3</span>
                    <span>Toma una captura de pantalla o foto del comprobante</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-7 h-7 bg-white text-emerald-600 rounded-full flex items-center justify-center font-bold text-sm mr-3">4</span>
                    <span>Sube el comprobante usando el formulario de esta página</span>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-7 h-7 bg-white text-emerald-600 rounded-full flex items-center justify-center font-bold text-sm mr-3">5</span>
                    <span>Espera la confirmación del personal médico (24-48 horas)</span>
                </li>
            </ol>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Left Column: Appointment Details -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                        <ion-icon name="calendar" class="text-emerald-600 text-2xl"></ion-icon>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-bold text-gray-800">Detalles de tu Cita</h2>
                        <p class="text-sm text-gray-500">Información de la reserva</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="border-b pb-3">
                        <p class="text-sm text-gray-500">Paciente</p>
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($cita['nombre'] . ' ' . $cita['apellido']); ?></p>
                    </div>
                    
                    <div class="border-b pb-3">
                        <p class="text-sm text-gray-500">Servicio</p>
                        <p class="font-semibold text-gray-800 capitalize"><?php echo htmlspecialchars($cita['servicio']); ?></p>
                    </div>
                    
                    <div class="border-b pb-3">
                        <p class="text-sm text-gray-500">Fecha</p>
                        <p class="font-semibold text-gray-800"><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></p>
                    </div>
                    
                    <div class="border-b pb-3">
                        <p class="text-sm text-gray-500">Horario</p>
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($cita['horario']); ?></p>
                    </div>
                    
                    <div class="bg-emerald-50 rounded-lg p-4 mt-4">
                        <p class="text-sm text-gray-600">Monto a Pagar</p>
                        <p class="text-3xl font-bold text-emerald-600">$<?php echo number_format($cita['monto'], 2); ?> MXN</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Bank Information -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <ion-icon name="card" class="text-blue-600 text-2xl"></ion-icon>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-bold text-gray-800">Datos para Transferencia</h2>
                        <p class="text-sm text-gray-500">SPEI - Transferencia Bancaria</p>
                    </div>
                </div>

                <?php if ($datos_bancarios): ?>
                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Banco</p>
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($datos_bancarios['banco']); ?></p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Beneficiario</p>
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($datos_bancarios['titular']); ?></p>
                    </div>
                    
                    <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-200">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-xs text-blue-600 font-semibold">CLABE Interbancaria</p>
                            <button onclick="copiarClabe()" class="text-blue-600 hover:text-blue-800">
                                <ion-icon name="copy-outline"></ion-icon>
                            </button>
                        </div>
                        <p class="text-xl font-bold text-blue-800 tracking-wider" id="clabe"><?php echo htmlspecialchars($datos_bancarios['clabe']); ?></p>
                    </div>

                    <?php if (!empty($datos_bancarios['numero_cuenta'])): ?>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Número de Cuenta</p>
                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($datos_bancarios['numero_cuenta']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($datos_bancarios['referencia_info'])): ?>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                        <p class="text-xs text-yellow-700 font-semibold mb-1">⚠️ IMPORTANTE</p>
                        <p class="text-sm text-yellow-800"><?php echo nl2br(htmlspecialchars($datos_bancarios['referencia_info'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <p class="text-red-800">No se encontró información bancaria. Contacta al administrador.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mt-6">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <ion-icon name="cloud-upload" class="text-purple-600 text-2xl"></ion-icon>
                </div>
                <div class="ml-4">
                    <h2 class="text-xl font-bold text-gray-800">Subir Comprobante de Pago</h2>
                    <p class="text-sm text-gray-500">Sube una foto o captura de pantalla de tu transferencia</p>
                </div>
            </div>

            <form action="php_action/upload_comprobante.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="id_cita" value="<?php echo $id_cita; ?>">
                
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-emerald-400 transition duration-200">
                    <ion-icon name="image-outline" class="text-6xl text-gray-400 mb-4"></ion-icon>
                    <p class="text-gray-600 mb-2">Arrastra tu imagen aquí o haz clic para seleccionar</p>
                    <p class="text-sm text-gray-400 mb-4">PNG, JPG o PDF (máx. 5MB)</p>
                    <input 
                        type="file" 
                        name="comprobante" 
                        id="comprobante" 
                        accept="image/png,image/jpeg,image/jpg,application/pdf"
                        required
                        class="hidden"
                        onchange="mostrarNombreArchivo(this)"
                    >
                    <label for="comprobante" class="bg-emerald-600 text-white px-6 py-3 rounded-lg cursor-pointer hover:bg-emerald-700 inline-block">
                        Seleccionar Archivo
                    </label>
                    <p id="nombreArchivo" class="mt-4 text-sm text-gray-600"></p>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Notas adicionales (opcional)
                    </label>
                    <textarea 
                        name="notas" 
                        rows="3" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        placeholder="Ej: Transferencia realizada el día de hoy desde cuenta BBVA"
                    ></textarea>
                </div>

                <div class="mt-6 flex gap-4">
                    <button 
                        type="submit" 
                        class="flex-1 bg-gradient-to-r from-emerald-600 to-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:from-emerald-700 hover:to-blue-700 transition duration-200 flex items-center justify-center"
                    >
                        <ion-icon name="checkmark-circle" class="mr-2 text-xl"></ion-icon>
                        Enviar Comprobante
                    </button>
                    <a 
                        href="reservas.php" 
                        class="bg-gray-200 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-300 transition duration-200"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function copiarClabe() {
            const clabe = document.getElementById('clabe').textContent;
            navigator.clipboard.writeText(clabe).then(() => {
                alert('CLABE copiada al portapapeles');
            });
        }

        function mostrarNombreArchivo(input) {
            const nombreArchivo = document.getElementById('nombreArchivo');
            if (input.files && input.files[0]) {
                nombreArchivo.textContent = '📎 ' + input.files[0].name;
                nombreArchivo.classList.add('text-emerald-600', 'font-semibold');
            }
        }

        // Drag and drop functionality
        const dropZone = document.querySelector('.border-dashed');
        const fileInput = document.getElementById('comprobante');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('border-emerald-400', 'bg-emerald-50');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-emerald-400', 'bg-emerald-50');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            mostrarNombreArchivo(fileInput);
        }
    </script>
</body>
</html>
