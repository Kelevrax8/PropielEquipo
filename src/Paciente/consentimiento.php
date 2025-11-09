<?php
// Página para firmar consentimiento informado
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['telefono']) || !isset($_SESSION['user_id'])) {
    header('location: ../Landing/login.html');
    exit();
}

// Obtener la información del usuario desde la base de datos
require_once '../database_connection.php';
require_once '../database_queries.php';

try {
    $db_queries = new PropielEquipoQueries();
    $user = $db_queries->getUserById($_SESSION['user_id']);
    
    if (!$user || empty($user)) {
        session_destroy();
        header('location: ../Landing/login.html');
        exit();
    }
    
    // Asignar variables para usar en el HTML
    $uid = $user['user_id'];
    $nombre = $user["nombre"];
    $apellido = $user["apellido"];
    $edad = $user["edad"];
    $telefono = $user["telefono"];
    $genero = $user['genero_nombre'];
    
    // Verificar si ya tiene consentimiento firmado
    $hasConsent = $db_queries->hasUserSignedConsent($uid);
    
} catch (Exception $e) {
    error_log("Error en consentimientos: " . $e->getMessage());
    header('location: ../Landing/login.html');
    exit();
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consentimiento Informado - PropielEquipo</title>
    <link href="../output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
    <!-- Librerías para generar PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
                        <ion-icon name="home" class="mr-1"></ion-icon>
                        Dashboard
                    </a>
                    <a href="reservar.php" class="text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium transition duration-200">
                        <ion-icon name="calendar" class="mr-1"></ion-icon>
                        Reservar Cita
                    </a>
                </div>
                
                <!-- Botón Cerrar Sesión -->
                <div class="flex items-center">
                    <form action="php_action/logout.php" method="post" class="hidden md:block">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition duration-200">
                            <ion-icon name="log-out" class="mr-1"></ion-icon>
                            Cerrar Sesión
                        </button>
                    </form>
                    
                    <!-- Menú Mobile -->
                    <button class="md:hidden text-gray-600 hover:text-gray-800" onclick="toggleMobileMenu()">
                        <ion-icon id="mobile-menu-icon" name="menu" class="text-2xl"></ion-icon>
                    </button>
                </div>
            </div>
            
            <!-- Navegación Mobile -->
            <div id="mobile-menu" class="md:hidden hidden border-t border-gray-200 py-4">
                <div class="space-y-2">
                    <a href="dashboardpaciente.php" class="block text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="home" class="mr-1"></ion-icon>
                        Dashboard
                    </a>
                    <a href="reservar.php" class="block text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="calendar" class="mr-1"></ion-icon>
                        Reservar Cita
                    </a>
                    <form action="php_action/logout.php" method="post" class="mt-4">
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium">
                            <ion-icon name="log-out" class="mr-1"></ion-icon>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container mx-auto px-4 py-8 flex-1">
        <div class="max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-2xl p-8 text-white mb-8">
                <div class="text-center">
                    <h1 class="text-3xl font-bold mb-2">Consentimiento Informado</h1>
                    <p class="text-emerald-100 mb-1"><?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-emerald-200 text-sm">Documento legal requerido para atención médica</p>
                </div>
            </div>

            <?php if ($hasConsent): ?>
            <!-- Usuario ya firmó el consentimiento -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <div class="text-center">
                    <div class="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <ion-icon name="checkmark-circle" class="text-4xl text-green-600"></ion-icon>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">
                        Consentimiento Ya Firmado
                    </h2>
                    
                    <p class="text-gray-600 mb-8">
                        Ya has firmado exitosamente tu consentimiento informado. 
                        Ahora puedes proceder a reservar citas médicas.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="reservar.php" 
                           class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200 flex items-center justify-center">
                            <ion-icon name="calendar" class="mr-2"></ion-icon>
                            Reservar Nueva Cita
                        </a>
                        
                        <a href="dashboardpaciente.php" 
                           class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded-lg transition duration-200 flex items-center justify-center">
                            <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                            Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Formulario de consentimiento -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Contenido del documento -->
                <div class="p-8" id="consent-document">
                    <!-- Header del documento -->
                    <div class="text-center mb-8">
                        <img src="../Images/logopropieel.png" alt="PRO-PIEL" class="mx-auto w-16 h-16 mb-4">
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">PRO-PIEL</h2>
                        <h3 class="text-xl font-semibold text-emerald-700 mb-2">CONSENTIMIENTO INFORMADO</h3>
                        <h4 class="text-lg text-gray-600">DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA</h4>
                    </div>

                    <!-- Información del paciente -->
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-6 mb-8">
                        <h4 class="font-bold text-emerald-800 mb-3">INFORMACIÓN DEL PACIENTE:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-emerald-700">
                            <div><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre . ' ' . $apellido); ?></div>
                            <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($telefono); ?></div>
                            <div><strong>Edad:</strong> <?php echo htmlspecialchars($edad); ?> años</div>
                            <div><strong>Sexo:</strong> <?php echo htmlspecialchars($genero); ?></div>
                        </div>
                    </div>

                    <!-- Contenido del consentimiento -->
                    <div class="space-y-6 text-gray-700 text-sm leading-relaxed">
                        <p class="text-justify">
                            Yo <strong><?php echo htmlspecialchars($nombre . ' ' . $apellido); ?></strong> autorizo al 
                            médico especialista designado por <strong>PRO-PIEL</strong> como mi médico tratante. 
                            Con mi número de teléfono <strong><?php echo htmlspecialchars($telefono); ?></strong> 
                            y a la edad de <strong><?php echo htmlspecialchars($edad); ?> años</strong>, 
                            de sexo <strong><?php echo htmlspecialchars($genero); ?></strong>; 
                            acudo a consulta externa de primera vez. Lo cual manifiesto consciente, sin presión y por voluntad propia.
                        </p>

                        <p class="text-justify font-medium">
                            Para lo cual <em>me interrogará sobre mi enfermedad y comorbilidades, me explorará el área afectada incluyendo el área genital si fuera necesario, lo cual lo hará siempre con la presencia de la enfermera. Asimismo, me solicitará estudios de laboratorio y hasta una biopsia de piel según mi enfermedad. Me prescribirá una receta médica en la que se indicarán los nombres de los medicamentos, forma de uso y tiempo que debo tomarlos; asimismo, si fuera necesario, mandará una cita subsecuente para valorar la evolución de mi enfermedad.</em>
                        </p>

                        <p class="text-justify">
                            Todo lo anterior apegado a la ética, profesionalismo y responsabilidad y con base en el principio de libertad prescriptiva, de acuerdo a lo establecido en las <strong>Normas Oficiales Mexicanas aplicables (NOM-001 y NOM-234)</strong>.
                        </p>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mt-8">
                        <h4 class="font-bold text-green-800 mb-3">DERECHOS DEL PACIENTE:</h4>
                        <ul class="text-green-700 space-y-2">
                            <li>• <strong>Información clara:</strong> Recibirás explicaciones comprensibles sobre tu tratamiento</li>
                            <li>• <strong>Confidencialidad:</strong> Tu información médica será protegida según la ley</li>
                            <li>• <strong>Decisión libre:</strong> Puedes aceptar o rechazar el tratamiento propuesto</li>
                            <li>• <strong>Segunda opinión:</strong> Derecho a consultar con otro profesional</li>
                            <li>• <strong>Acceso a expediente:</strong> Puedes solicitar copia de tu historial médico</li>
                        </ul>
                    </div>
                </div>

                <!-- Área de Firma -->
                <div class="border-t bg-gray-50 p-8">
                    <h4 class="text-xl font-semibold text-gray-800 mb-6 text-center">
                        Firma Digital del Paciente
                    </h4>
                    
                    <!-- Canvas para la firma -->
                    <div class="flex justify-center mb-6">
                        <div class="border-2 border-gray-300 rounded-lg bg-white w-full max-w-lg">
                            <canvas id="signature-canvas" 
                                    width="500" 
                                    height="200" 
                                    class="w-full h-40 cursor-crosshair touch-none"
                                    style="touch-action: none;">
                            </canvas>
                        </div>
                    </div>

                    <!-- Instrucciones para firma -->
                    <div class="text-center text-sm text-gray-600 mb-4">
                        <p class="mb-2">
                            <ion-icon name="information-circle" class="mr-1"></ion-icon>
                            Firma en el recuadro anterior usando tu dedo (móvil) o mouse (escritorio)
                        </p>
                        <p>La firma debe ser clara y legible para proceder</p>
                    </div>

                    <!-- Controles de la firma -->
                    <div class="flex justify-center gap-4 mb-8">
                        <button type="button" 
                                onclick="clearSignature()" 
                                class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                            <ion-icon name="refresh" class="mr-2"></ion-icon>
                            Limpiar Firma
                        </button>
                        
                        <button type="button" 
                                onclick="previewConsent()" 
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                            <ion-icon name="eye" class="mr-2"></ion-icon>
                            Vista Previa PDF
                        </button>
                    </div>

                    <!-- Checkbox de aceptación -->
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-6 mb-8">
                        <label class="flex items-start space-x-3">
                            <input type="checkbox" 
                                   id="consent-checkbox"
                                   class="mt-1 h-5 w-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="text-emerald-800 font-medium">
                                <strong>Acepto y entiendo</strong> que he leído, comprendido y acepto todos los términos del presente 
                                consentimiento informado. Confirmo que mi decisión es libre, consciente y voluntaria, y que todas 
                                mis dudas han sido resueltas satisfactoriamente.
                            </span>
                        </label>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button type="button" 
                                id="confirm-consent-btn"
                                onclick="submitConsent()" 
                                disabled
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-8 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                            <ion-icon name="checkmark-circle" class="mr-2"></ion-icon>
                            Firmar y Guardar Consentimiento
                        </button>
                        
                        <a href="dashboardpaciente.php" 
                           class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-4 px-8 rounded-lg transition duration-200 flex items-center justify-center">
                            <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                            Volver Después
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Variables globales para el canvas de firma
        let canvas, ctx, isDrawing = false;
        
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

        // Inicializar cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!$hasConsent): ?>
            initSignatureCanvas();
            
            // Event listener para el checkbox
            const checkbox = document.getElementById('consent-checkbox');
            if (checkbox) {
                checkbox.addEventListener('change', updateConsentButton);
            }
            <?php endif; ?>
        });

        // Inicializar canvas de firma
        function initSignatureCanvas() {
            canvas = document.getElementById('signature-canvas');
            if (!canvas) return;
            
            ctx = canvas.getContext('2d');
            
            // Configurar el canvas para alta resolución
            const rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            canvas.style.width = rect.width + 'px';
            canvas.style.height = rect.height + 'px';
            
            ctx.scale(dpr, dpr);
            
            // Configurar el estilo de dibujo
            ctx.strokeStyle = '#000000';
            ctx.lineWidth = isMobileDevice() ? 3 : 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            
            // Event listeners para mouse
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);
            
            // Event listeners para touch (móviles)
            canvas.addEventListener('touchstart', handleTouchStart, { passive: false });
            canvas.addEventListener('touchmove', handleTouchMove, { passive: false });
            canvas.addEventListener('touchend', handleTouchEnd, { passive: false });
            canvas.addEventListener('touchcancel', handleTouchEnd, { passive: false });
            
            // Prevenir comportamientos por defecto
            canvas.addEventListener('selectstart', function(e) { e.preventDefault(); });
            canvas.addEventListener('dragstart', function(e) { e.preventDefault(); });
        }

        // Funciones de dibujo para mouse
        function startDrawing(e) {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        function draw(e) {
            if (!isDrawing) return;
            
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            ctx.lineTo(x, y);
            ctx.stroke();
        }

        function stopDrawing() {
            isDrawing = false;
            ctx.beginPath();
        }

        // Funciones para eventos touch
        function handleTouchStart(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (e.touches.length === 1) {
                const touch = e.touches[0];
                const rect = canvas.getBoundingClientRect();
                const x = touch.clientX - rect.left;
                const y = touch.clientY - rect.top;
                
                isDrawing = true;
                ctx.beginPath();
                ctx.moveTo(x, y);
            }
        }

        function handleTouchMove(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (!isDrawing || e.touches.length !== 1) return;
            
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            const x = touch.clientX - rect.left;
            const y = touch.clientY - rect.top;
            
            ctx.lineTo(x, y);
            ctx.stroke();
        }

        function handleTouchEnd(e) {
            e.preventDefault();
            e.stopPropagation();
            
            isDrawing = false;
            ctx.beginPath();
        }

        // Función para detectar dispositivos móviles
        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        // Limpiar canvas
        function clearSignature() {
            if (!canvas) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            updateConsentButton();
        }

        // Verificar si el canvas está vacío
        function isCanvasEmpty() {
            if (!canvas) return true;
            
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const pixelData = imageData.data;
            
            for (let i = 3; i < pixelData.length; i += 4) {
                if (pixelData[i] !== 0) {
                    return false;
                }
            }
            return true;
        }

        // Actualizar estado del botón de consentimiento
        function updateConsentButton() {
            const checkbox = document.getElementById('consent-checkbox');
            const button = document.getElementById('confirm-consent-btn');
            
            if (checkbox && button) {
                const hasSignature = !isCanvasEmpty();
                button.disabled = !(checkbox.checked && hasSignature);
            }
        }

        // Vista previa del PDF
        async function previewConsent() {
            if (isCanvasEmpty()) {
                alert('Por favor firma el documento antes de generar la vista previa.');
                return;
            }
            
            try {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                
                // Configuración del PDF
                const pageWidth = 210;
                const margin = 20;
                let yPosition = margin;

                // Agregar logo centrado en la parte superior
                try {
                    // Crear un canvas temporal para cargar el logo
                    const logoCanvas = document.createElement('canvas');
                    const logoCtx = logoCanvas.getContext('2d');
                    const logoImg = new Image();
                    
                    // Promesa para cargar la imagen
                    await new Promise((resolve, reject) => {
                        logoImg.onload = function() {
                            // Configurar canvas del tamaño de la imagen
                            logoCanvas.width = this.width;
                            logoCanvas.height = this.height;
                            
                            // Dibujar la imagen en el canvas
                            logoCtx.drawImage(this, 0, 0);
                            
                            // Convertir a data URL y agregar al PDF
                            const logoDataURL = logoCanvas.toDataURL('image/png');
                            
                            // Calcular dimensiones para el PDF (35mm de ancho para mejor visibilidad)
                            const logoMaxWidth = 35;
                            const logoAspectRatio = this.height / this.width;
                            const logoWidth = logoMaxWidth;
                            const logoHeight = logoMaxWidth * logoAspectRatio;
                            const logoX = (pageWidth - logoWidth) / 2; // Centrar horizontalmente
                            
                            // Agregar logo al PDF en la parte superior
                            pdf.addImage(logoDataURL, 'PNG', logoX, yPosition, logoWidth, logoHeight);
                            yPosition += logoHeight + 15; // Más espacio después del logo
                            
                            console.log('Logo agregado al PDF exitosamente');
                            resolve();
                        };
                        
                        logoImg.onerror = function() {
                            console.warn('No se pudo cargar el logo desde:', this.src);
                            yPosition += 10; // Mantener espacio aunque no haya logo
                            resolve(); // Continuar sin logo si hay error
                        };
                        
                        // Cargar la imagen del logo con crossOrigin para evitar problemas CORS
                        logoImg.crossOrigin = 'anonymous';
                        logoImg.src = '../Images/logopropieel.png?' + new Date().getTime(); // Cache busting
                    });
                } catch (logoError) {
                    console.warn('Error al procesar logo:', logoError);
                    yPosition += 10; // Mantener espacio aunque no haya logo
                }

                // Header del PDF
                pdf.setFontSize(16);
                pdf.setFont(undefined, 'bold');
                pdf.text('PRO-PIEL', pageWidth/2, yPosition, { align: 'center' });
                yPosition += 8;
                
                pdf.setFontSize(14);
                pdf.text('CONSENTIMIENTO INFORMADO', pageWidth/2, yPosition, { align: 'center' });
                yPosition += 8;
                
                pdf.setFontSize(12);
                pdf.text('DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA', pageWidth/2, yPosition, { align: 'center' });
                yPosition += 15;

                // Contenido del consentimiento
                pdf.setFontSize(10);
                pdf.setFont(undefined, 'normal');
                
                const consentText = `Yo <?php echo addslashes($nombre . ' ' . $apellido); ?> autorizo al médico especialista designado por PRO-PIEL como mi médico tratante. Con mi número de teléfono <?php echo addslashes($telefono); ?> y a la edad de <?php echo addslashes($edad); ?> años, de sexo <?php echo addslashes($genero); ?>; acudo a consulta externa. Lo cual manifiesto consciente, sin presión y por voluntad propia.

Para lo cual me interrogará sobre mi enfermedad y comorbilidades, me explorará el área afectada incluyendo el área genital si fuera necesario, lo cual lo hará siempre con la presencia de la enfermera. Asimismo, me solicitará estudios de laboratorio y hasta una biopsia de piel según mi enfermedad. Me prescribirá una receta médica en la que se indicarán los nombres de los medicamentos, forma de uso y tiempo que debo tomarlos; asimismo, si fuera necesario, mandará una cita subsecuente para valorar la evolución de mi enfermedad.

Todo lo anterior apegado a la ética, profesionalismo y responsabilidad y con base en el principio de libertad prescriptiva, de acuerdo a lo establecido en las Normas Oficiales Mexicanas aplicables (NOM-001 y NOM-234).`;

                // Dividir texto en líneas
                const splitText = pdf.splitTextToSize(consentText, pageWidth - (margin * 2));
                pdf.text(splitText, margin, yPosition);
                yPosition += splitText.length * 5 + 20;

                // Agregar firma
                const signatureDataURL = canvas.toDataURL('image/png');
                pdf.setFontSize(12);
                pdf.setFont(undefined, 'bold');
                pdf.text('Firma del Paciente:', pageWidth/2, yPosition, { align: 'center' });
                yPosition += 10;
                
                const signatureWidth = 80;
                const signatureHeight = 40;
                const signatureX = (pageWidth - signatureWidth) / 2;
                pdf.addImage(signatureDataURL, 'PNG', signatureX, yPosition, signatureWidth, signatureHeight);
                yPosition += signatureHeight + 10;

                // Información adicional
                yPosition += 10;
                pdf.setFontSize(10);
                pdf.setFont(undefined, 'normal');
                const currentDate = new Date().toLocaleDateString('es-MX');
                const currentTime = new Date().toLocaleTimeString('es-MX', { hour12: false });
                pdf.text(`Fecha: ${currentDate}`, margin, yPosition);
                yPosition += 8;
                pdf.text(`Sistema: PropielEquipo v1.0`, margin, yPosition);

                // Crear blob y abrir en nueva pestaña para vista previa
                const pdfBlob = pdf.output('blob');
                const pdfUrl = URL.createObjectURL(pdfBlob);
                window.open(pdfUrl, '_blank');
                
                setTimeout(() => {
                    URL.revokeObjectURL(pdfUrl);
                }, 30000);
                
            } catch (error) {
                console.error('Error al generar PDF:', error);
                alert('Error al generar la vista previa. Por favor intenta nuevamente.');
            }
        }

        // Enviar consentimiento
        async function submitConsent() {
            const checkbox = document.getElementById('consent-checkbox');
            
            if (!checkbox.checked) {
                alert('Debes aceptar el consentimiento informado para continuar.');
                return;
            }
            
            if (isCanvasEmpty()) {
                alert('Por favor firma el documento antes de continuar.');
                return;
            }
            
            const confirmBtn = document.getElementById('confirm-consent-btn');
            const originalText = confirmBtn.innerHTML;
            
            try {
                // Mostrar indicador de carga
                confirmBtn.innerHTML = '<ion-icon name="hourglass" class="mr-2"></ion-icon>Guardando...';
                confirmBtn.disabled = true;
                
                // Generar PDF
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                
                // Configurar PDF (mismo código que previewConsent pero sin mostrar)
                const pageWidth = 210;
                const margin = 20;
                let yPosition = margin;

                // Agregar logo centrado en la parte superior
                try {
                    // Crear un canvas temporal para cargar el logo
                    const logoCanvas = document.createElement('canvas');
                    const logoCtx = logoCanvas.getContext('2d');
                    const logoImg = new Image();
                    
                    // Promesa para cargar la imagen
                    await new Promise((resolve, reject) => {
                        logoImg.onload = function() {
                            // Configurar canvas del tamaño de la imagen
                            logoCanvas.width = this.width;
                            logoCanvas.height = this.height;
                            
                            // Dibujar la imagen en el canvas
                            logoCtx.drawImage(this, 0, 0);
                            
                            // Convertir a data URL y agregar al PDF
                            const logoDataURL = logoCanvas.toDataURL('image/png');
                            
                            // Calcular dimensiones para el PDF (35mm de ancho para mejor visibilidad)
                            const logoMaxWidth = 35;
                            const logoAspectRatio = this.height / this.width;
                            const logoWidth = logoMaxWidth;
                            const logoHeight = logoMaxWidth * logoAspectRatio;
                            const logoX = (pageWidth - logoWidth) / 2; // Centrar horizontalmente
                            
                            // Agregar logo al PDF en la parte superior
                            pdf.addImage(logoDataURL, 'PNG', logoX, yPosition, logoWidth, logoHeight);
                            yPosition += logoHeight + 15; // Más espacio después del logo
                            
                            console.log('Logo agregado al PDF exitosamente');
                            resolve();
                        };
                        
                        logoImg.onerror = function() {
                            console.warn('No se pudo cargar el logo desde:', this.src);
                            yPosition += 10; // Mantener espacio aunque no haya logo
                            resolve(); // Continuar sin logo si hay error
                        };
                        
                        // Cargar la imagen del logo con crossOrigin para evitar problemas CORS
                        logoImg.crossOrigin = 'anonymous';
                        logoImg.src = '../Images/logopropieel.png?' + new Date().getTime(); // Cache busting
                    });
                } catch (logoError) {
                    console.warn('Error al procesar logo:', logoError);
                    yPosition += 10; // Mantener espacio aunque no haya logo
                }

                pdf.setFontSize(16);
                pdf.setFont(undefined, 'bold');
                pdf.text('PRO-PIEL', pageWidth/2, yPosition, { align: 'center' });
                yPosition += 8;
                
                pdf.setFontSize(14);
                pdf.text('CONSENTIMIENTO INFORMADO', pageWidth/2, yPosition, { align: 'center' });
                yPosition += 8;
                
                pdf.setFontSize(12);
                pdf.text('DE ATENCIÓN Y PRESCRIPCIÓN MÉDICA', pageWidth/2, yPosition, { align: 'center' });
                yPosition += 15;

                pdf.setFontSize(10);
                pdf.setFont(undefined, 'normal');
                
                const consentText = `Yo <?php echo addslashes($nombre . ' ' . $apellido); ?> autorizo al médico especialista designado por PRO-PIEL como mi médico tratante. Con mi número de teléfono <?php echo addslashes($telefono); ?> y a la edad de <?php echo addslashes($edad); ?> años, de sexo <?php echo addslashes($genero); ?>; acudo a consulta externa. Lo cual manifiesto consciente, sin presión y por voluntad propia.

Para lo cual me interrogará sobre mi enfermedad y comorbilidades, me explorará el área afectada incluyendo el área genital si fuera necesario, lo cual lo hará siempre con la presencia de la enfermera. Asimismo, me solicitará estudios de laboratorio y hasta una biopsia de piel según mi enfermedad. Me prescribirá una receta médica en la que se indicarán los nombres de los medicamentos, forma de uso y tiempo que debo tomarlos; asimismo, si fuera necesario, mandará una cita subsecuente para valorar la evolución de mi enfermedad.

Todo lo anterior apegado a la ética, profesionalismo y responsabilidad y con base en el principio de libertad prescriptiva, de acuerdo a lo establecido en las Normas Oficiales Mexicanas aplicables (NOM-001 y NOM-234).`;

                const splitText = pdf.splitTextToSize(consentText, pageWidth - (margin * 2));
                pdf.text(splitText, margin, yPosition);
                yPosition += splitText.length * 5 + 20;

                const signatureDataURL = canvas.toDataURL('image/png');
                pdf.setFontSize(12);
                pdf.setFont(undefined, 'bold');
                pdf.text('Firma del Paciente:', pageWidth/2, yPosition, { align: 'center' });
                yPosition += 10;
                
                const signatureWidth = 80;
                const signatureHeight = 40;
                const signatureX = (pageWidth - signatureWidth) / 2;
                pdf.addImage(signatureDataURL, 'PNG', signatureX, yPosition, signatureWidth, signatureHeight);
                yPosition += signatureHeight + 10;

                yPosition += 10;
                pdf.setFontSize(10);
                pdf.setFont(undefined, 'normal');
                const currentDate = new Date().toLocaleDateString('es-MX');
                const currentTime = new Date().toLocaleTimeString('es-MX', { hour12: false });
                pdf.text(`Fecha: ${currentDate}`, margin, yPosition);
                yPosition += 8;
                pdf.text(`Sistema: PropielEquipo v1.0`, margin, yPosition);

                // Obtener PDF como base64
                const pdfBase64 = pdf.output('datauristring').split(',')[1];
                
                // Generar nombre del archivo
                const cleanPatientName = '<?php echo preg_replace("/[^a-zA-Z0-9]/", "_", $nombre . "_" . $apellido); ?>';
                const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
                const filename = `consentimiento_${cleanPatientName}_${timestamp}.pdf`;

                // Enviar al servidor
                const formData = new FormData();
                formData.append('consent_pdf', pdfBase64);
                formData.append('consent_filename', filename);
                formData.append('user_id', '<?php echo $uid; ?>');
                
                const response = await fetch('php_action/save_consent.php', {
                    method: 'POST',
                    body: formData
                });

                // Verificar si la respuesta es válida
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }

                // Obtener el texto de la respuesta para debuggear
                const responseText = await response.text();
                console.log('Respuesta del servidor:', responseText);

                // Intentar parsear como JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Error al parsear JSON:', parseError);
                    console.error('Respuesta cruda:', responseText);
                    throw new Error('Error en la respuesta del servidor. Respuesta: ' + responseText.substring(0, 100));
                }

                if (result.success) {
                    alert('¡Consentimiento firmado y guardado exitosamente! Ahora puedes reservar citas médicas.');
                    window.location.href = 'reservar.php?consent=signed&file=' + encodeURIComponent(result.filename);
                } else {
                    throw new Error(result.message || 'Error al guardar el consentimiento');
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Error al guardar el consentimiento: ' + error.message);
                
                // Restaurar botón
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
            }
        }
    </script>

</body>
</html>
