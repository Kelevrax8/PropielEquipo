<?php
// Dashboard específico para Tamizaje
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
    
    // Obtener estadísticas específicas de tamizaje para este doctor
    $citas_tamiz_hoy = [];
    $imagenes_tamiz = [];
    
    try {
        // Filtrar citas de tamizaje de hoy asignadas a este doctor
        $todas_citas = $db_queries->getAppointmentsByDate(date('Y-m-d'), $uid);
        foreach ($todas_citas as $cita) {
            // Comparar con 'tamiz' (como está en la base de datos)
            if (strtolower($cita['servicio']) === 'tamiz') {
                $citas_tamiz_hoy[] = $cita;
            }
        }
        
        // Obtener imágenes médicas de tamizaje
        $todas_imagenes = $db_queries->getDoctorPatientImages($uid);
        foreach ($todas_imagenes as $imagen) {
            if ($imagen['tipo_imagen'] === 'tamizaje') {
                $imagenes_tamiz[] = $imagen;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error obteniendo datos de tamizaje: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Error en dashboard tamizaje: " . $e->getMessage());
    header('location: ../../../Landing/login.html');
    exit();
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tamizaje - PropielEquipo</title>
    <link href="../../../output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
</head>
<body class="bg-gradient-to-r from-purple-50 to-purple-100 flex flex-col min-h-screen">
    
    <?php include '../../shared/navbar_doctor.php'; ?>

    <!-- Dashboard Específico de Tamizaje -->
    <div class="container mx-auto px-4 py-8">
        
        <!-- Header Especialidad -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl p-8 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Dashboard de Tamizaje</h1>
                    <p class="text-purple-100">Dr. <?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-purple-200 text-sm">Especialista en evaluaciones y diagnóstico preventivo</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="search" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Citas Hoy</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo count($citas_tamiz_hoy); ?></p>
                    </div>
                    <ion-icon name="calendar" class="text-3xl text-purple-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Imágenes</p>
                        <p class="text-2xl font-bold text-indigo-600"><?php echo count($imagenes_tamiz); ?></p>
                    </div>
                    <ion-icon name="images" class="text-3xl text-indigo-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Especialidad</p>
                        <p class="text-lg font-semibold text-gray-800">Tamizaje</p>
                    </div>
                    <ion-icon name="shield-checkmark" class="text-3xl text-pink-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Estado</p>
                        <p class="text-lg font-semibold text-purple-600">Activo</p>
                    </div>
                    <ion-icon name="checkmark-circle" class="text-3xl text-purple-500"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <!-- Citas de Hoy -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-purple-600 text-white p-4">
                    <h3 class="text-lg font-bold flex items-center">
                        <ion-icon name="calendar-outline" class="mr-2"></ion-icon>
                        Citas de Tamizaje Hoy
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (count($citas_tamiz_hoy) > 0): ?>
                        <div class="space-y-3">
                            <?php foreach ($citas_tamiz_hoy as $cita): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium">Paciente ID: <?php echo htmlspecialchars($cita['user_id']); ?></p>
                                        <p class="text-sm text-gray-600">Hora: <?php echo htmlspecialchars($cita['horario']); ?></p>
                                    </div>
                                    <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm">
                                        Tamizaje
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4">
                            <a href="citas_tamizaje.php" class="text-purple-600 hover:text-purple-700 font-medium">
                                Ver todas las citas →
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <ion-icon name="calendar-outline" class="text-4xl text-gray-400 mb-2"></ion-icon>
                            <p class="text-gray-500">No hay citas de tamizaje programadas para hoy</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Imágenes Recientes -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-indigo-600 text-white p-4">
                    <h3 class="text-lg font-bold flex items-center">
                        <ion-icon name="images-outline" class="mr-2"></ion-icon>
                        Imágenes de Tamizaje Recientes
                    </h3>
                </div>
                <div class="p-6">
                    <?php if (count($imagenes_tamiz) > 0): ?>
                        <div class="grid grid-cols-3 gap-3">
                            <?php 
                            $imagenes_recientes = array_slice($imagenes_tamiz, 0, 6);
                            foreach ($imagenes_recientes as $imagen): 
                            ?>
                                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden relative">
                                    <img src="../../../Images/secure_image_viewer.php?image=<?php echo urlencode($imagen['nombre_archivo']); ?>" 
                                         alt="Imagen de tamizaje" 
                                         class="w-full h-full object-cover filter blur-sm hover:blur-none transition-all duration-300 cursor-pointer"
                                         onclick="verImagenDetalle('<?php echo htmlspecialchars($imagen['nombre_archivo']); ?>', <?php echo $imagen['id_imagen'] ?? 0; ?>)">
                                    <!-- Overlay con gradiente y efecto hover -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent flex items-center justify-center opacity-100 hover:opacity-0 transition-opacity duration-300 pointer-events-none">
                                        <div class="text-center">
                                            <ion-icon name="eye" class="text-white text-xl mb-1 drop-shadow-lg"></ion-icon>
                                            <p class="text-white text-xs drop-shadow-lg">Click para detalle</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-4">
                            <a href="imagenes_tamizaje.php" class="text-green-600 hover:text-green-700 font-medium">
                                Ver todas las imágenes →
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <ion-icon name="images-outline" class="text-4xl text-gray-400 mb-2"></ion-icon>
                            <p class="text-gray-500">No hay imágenes de tamizaje disponibles</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-bold mb-6 text-gray-800">Acciones Rápidas</h3>
            <div class="grid md:grid-cols-4 gap-4">
                <a href="citas_tamizaje.php" class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-200">
                    <ion-icon name="calendar" class="text-3xl text-purple-600 mb-2"></ion-icon>
                    <span class="text-sm font-medium text-gray-700">Gestionar Citas</span>
                </a>
                
                <a href="pacientes_tamizaje.php" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition duration-200">
                    <ion-icon name="people" class="text-3xl text-green-600 mb-2"></ion-icon>
                    <span class="text-sm font-medium text-gray-700">Mis Pacientes</span>
                </a>
                
                <a href="imagenes_tamizaje.php" class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition duration-200">
                    <ion-icon name="images" class="text-3xl text-orange-600 mb-2"></ion-icon>
                    <span class="text-sm font-medium text-gray-700">Imágenes Médicas</span>
                </a>
                
                <a href="../../dashboarddoc.php" class="flex flex-col items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition duration-200">
                    <ion-icon name="home" class="text-3xl text-gray-600 mb-2"></ion-icon>
                    <span class="text-sm font-medium text-gray-700">Dashboard General</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal para ver imagen en detalle -->
    <div id="modalImagen" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50 hidden">
        <div class="max-w-4xl max-h-full p-4 relative">
            <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
                <div class="flex items-center justify-between p-4 bg-purple-600 text-white">
                    <h3 class="text-lg font-bold" id="modalTitulo">Imagen Médica - Tamizaje</h3>
                    <button onclick="cerrarModal()" class="text-white hover:text-gray-200">
                        <ion-icon name="close" class="text-2xl"></ion-icon>
                    </button>
                </div>
                <div class="p-4">
                    <div class="text-center">
                        <img id="modalImagenSrc" src="" alt="Imagen médica" class="max-w-full max-h-96 object-contain mx-auto rounded-lg">
                    </div>
                    <div class="mt-4 flex justify-center gap-4">
                        <button onclick="descargarImagenModal()" 
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <ion-icon name="download" class="mr-2"></ion-icon>
                            Descargar
                        </button>
                        <button onclick="cerrarModal()" 
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let imagenActual = '';
        
        function verImagenDetalle(nombreArchivo, idImagen) {
            imagenActual = nombreArchivo;
            const modal = document.getElementById('modalImagen');
            const modalImg = document.getElementById('modalImagenSrc');
            const modalTitulo = document.getElementById('modalTitulo');
            
            modalImg.src = '../../../Images/secure_image_viewer.php?image=' + encodeURIComponent(nombreArchivo);
            modalTitulo.textContent = 'Imagen Médica #' + idImagen + ' - Tamizaje';
            
            // Mostrar modal con flex
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Prevenir scroll del body cuando el modal está abierto
            document.body.style.overflow = 'hidden';
        }
        
        function cerrarModal() {
            const modal = document.getElementById('modalImagen');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
        
        function descargarImagenModal() {
            if (imagenActual) {
                descargarImagen(imagenActual);
            }
        }
        
        function descargarImagen(nombreArchivo) {
            // Crear enlace temporal para descargar
            const link = document.createElement('a');
            link.href = '../../../Images/secure_image_viewer.php?image=' + encodeURIComponent(nombreArchivo) + '&action=download';
            link.download = nombreArchivo;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarModal();
            }
        });
        
        // Cerrar modal al hacer clic fuera de la imagen
        document.getElementById('modalImagen').addEventListener('click', function(event) {
            if (event.target === this) {
                cerrarModal();
            }
        });
    </script>

</body>
</html>
