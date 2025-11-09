<?php
// Imágenes médicas específicas para Tamizaje
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
    
    // Obtener imágenes médicas filtradas por tamizaje
    $imagenes_tamizaje = [];
    $pacientes_con_imagenes = [];
    
    try {
        $todas_imagenes = $db_queries->getAllMedicalImages();
        
        foreach ($todas_imagenes as $imagen) {
            
            // Filtrar imágenes relacionadas con tamizaje usando el campo tipo_imagen
            // Hacer el filtro más flexible para capturar variaciones
            $tipo_lower = strtolower(trim($imagen['tipo_imagen']));
            if ($tipo_lower === 'tamizaje' || 
                strpos($tipo_lower, 'tamiz') !== false ||
                strpos($tipo_lower, 'screening') !== false ||
                strpos($tipo_lower, 'deteccion') !== false) {
                
                $imagenes_tamizaje[] = $imagen;
                
                // Crear lista de pacientes únicos con sus datos
                $paciente_id = $imagen['id_paciente'];
                if (!isset($pacientes_con_imagenes[$paciente_id])) {
                    $pacientes_con_imagenes[$paciente_id] = [
                        'id' => $paciente_id,
                        'nombre' => $imagen['nombre'] . ' ' . $imagen['apellido'],
                        'telefono' => $imagen['telefono'],
                        'count' => 0
                    ];
                }
                $pacientes_con_imagenes[$paciente_id]['count']++;
            }
        }
        
        // Ordenar por fecha más reciente
        usort($imagenes_tamizaje, function($a, $b) {
            return strtotime($b['fecha_subida']) - strtotime($a['fecha_subida']);
        });
        
        // Convertir a array indexado y ordenar pacientes por nombre
        $pacientes_con_imagenes = array_values($pacientes_con_imagenes);
        usort($pacientes_con_imagenes, function($a, $b) {
            return strcmp($a['nombre'], $b['nombre']);
        });
        
    } catch (Exception $e) {
        error_log("Error obteniendo imágenes de tamizaje: " . $e->getMessage());
    }
    
    // Filtrar por paciente seleccionado si se especifica
    $paciente_seleccionado = $_GET['paciente'] ?? '';
    $imagenes_filtradas = $imagenes_tamizaje;
    
    if (!empty($paciente_seleccionado) && $paciente_seleccionado !== 'todos') {
        $imagenes_filtradas = array_filter($imagenes_tamizaje, function($imagen) use ($paciente_seleccionado) {
            return $imagen['id_paciente'] == $paciente_seleccionado;
        });
    }
    
} catch (Exception $e) {
    error_log("Error en imágenes tamizaje: " . $e->getMessage());
    header('location: ../../../Landing/login.html');
    exit();
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imágenes Tamizaje - PropielEquipo</title>
    <link href="../../../output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
</head>
<body class="bg-gradient-to-r from-purple-50 to-purple-100 flex flex-col min-h-screen">
    
    <?php include '../../shared/navbar_doctor.php'; ?>

    <!-- Header de Imágenes de Tamizaje -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl p-6 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Imágenes Médicas - Tamizaje</h1>
                    <p class="text-purple-100">Dr. <?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-purple-200 text-sm">Gestión de imágenes de detección temprana</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="search" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Filtro de Pacientes -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Filtrar por Paciente</h2>
                    <p class="text-sm text-gray-600">Selecciona un paciente para ver solo sus imágenes</p>
                </div>
                <div class="flex items-center gap-4">
                    <form method="GET" class="flex items-center gap-3">
                        <select name="paciente" onchange="this.form.submit()" 
                                class="bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="todos" <?php echo ($paciente_seleccionado === '' || $paciente_seleccionado === 'todos') ? 'selected' : ''; ?>>
                                Todos los pacientes (<?php echo count($imagenes_tamizaje); ?> imágenes)
                            </option>
                            <?php foreach ($pacientes_con_imagenes as $paciente): ?>
                                <option value="<?php echo $paciente['id']; ?>" 
                                        <?php echo ($paciente_seleccionado == $paciente['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($paciente['nombre']); ?> 
                                    (<?php echo $paciente['count']; ?> imágenes)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($paciente_seleccionado) && $paciente_seleccionado !== 'todos'): ?>
                            <a href="?paciente=todos" 
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition duration-200">
                                Limpiar filtro
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Imágenes -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">
                            <?php echo (!empty($paciente_seleccionado) && $paciente_seleccionado !== 'todos') ? 'Imágenes del Paciente' : 'Total Imágenes'; ?>
                        </p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo count($imagenes_filtradas); ?></p>
                    </div>
                    <ion-icon name="images-outline" class="text-3xl text-purple-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Esta Semana</p>
                        <p class="text-2xl font-bold text-green-600">
                            <?php 
                            $imagenes_semana = array_filter($imagenes_filtradas, function($imagen) {
                                $fecha_imagen = strtotime($imagen['fecha_subida']);
                                $hace_semana = strtotime('-1 week');
                                return $fecha_imagen >= $hace_semana;
                            });
                            echo count($imagenes_semana);
                            ?>
                        </p>
                    </div>
                    <ion-icon name="trending-up" class="text-3xl text-green-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Hoy</p>
                        <p class="text-2xl font-bold text-orange-600">
                            <?php 
                            $imagenes_hoy = array_filter($imagenes_filtradas, function($imagen) {
                                return date('Y-m-d', strtotime($imagen['fecha_subida'])) === date('Y-m-d');
                            });
                            echo count($imagenes_hoy);
                            ?>
                        </p>
                    </div>
                    <ion-icon name="today" class="text-3xl text-orange-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">
                            <?php echo (!empty($paciente_seleccionado) && $paciente_seleccionado !== 'todos') ? 'Paciente Actual' : 'Pacientes Únicos'; ?>
                        </p>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php 
                            if (!empty($paciente_seleccionado) && $paciente_seleccionado !== 'todos') {
                                echo '1';
                            } else {
                                $pacientes_unicos = array_unique(array_column($imagenes_filtradas, 'id_paciente'));
                                echo count($pacientes_unicos);
                            }
                            ?>
                        </p>
                    </div>
                    <ion-icon name="people" class="text-3xl text-blue-500"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Galería de Imágenes -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-purple-600 text-white p-6">
                <h2 class="text-xl font-bold flex items-center">
                    <ion-icon name="grid" class="mr-2"></ion-icon>
                    <?php if (!empty($paciente_seleccionado) && $paciente_seleccionado !== 'todos'): ?>
                        <?php 
                        $paciente_info = null;
                        foreach ($pacientes_con_imagenes as $p) {
                            if ($p['id'] == $paciente_seleccionado) {
                                $paciente_info = $p;
                                break;
                            }
                        }
                        ?>
                        Imágenes de <?php echo htmlspecialchars($paciente_info['nombre']); ?>
                        <span class="ml-2 text-sm bg-purple-500 px-2 py-1 rounded">
                            <?php echo count($imagenes_filtradas); ?> imágenes
                        </span>
                    <?php else: ?>
                        Galería de Imágenes de Tamizaje
                        <span class="ml-2 text-sm bg-purple-500 px-2 py-1 rounded">
                            <?php echo count($imagenes_filtradas); ?> imágenes total
                        </span>
                    <?php endif; ?>
                </h2>
            </div>
            
            <?php if (count($imagenes_filtradas) > 0): ?>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($imagenes_filtradas as $imagen): ?>
                            <div class="bg-gray-50 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300">
                                <!-- Imagen con efecto blur por privacidad -->
<div class="relative h-48 bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center">
    <?php if (!empty($imagen['nombre_archivo']) && file_exists("../../../Images/ImgMedicas/" . $imagen['nombre_archivo'])): ?>
        <img src="../../../Images/secure_image_viewer.php?image=<?php echo urlencode($imagen['nombre_archivo']); ?>" 
             alt="Imagen médica" 
             class="w-full h-full object-cover filter blur-sm hover:blur-none transition-all duration-300 cursor-pointer"
             onclick="verImagenDetalle('<?php echo htmlspecialchars($imagen['nombre_archivo']); ?>', <?php echo $imagen['id_imagen']; ?>)">
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent flex items-center justify-center opacity-100 hover:opacity-0 transition-opacity duration-300 pointer-events-none">
            <div class="text-center">
                <ion-icon name="eye" class="text-white text-2xl mb-1 drop-shadow-lg"></ion-icon>
                <p class="text-white text-xs drop-shadow-lg">Hover para preview / Click para detalle</p>
            </div>
        </div>
    <?php else: ?>
        <div class="text-gray-400 text-center">
            <ion-icon name="image-outline" class="text-4xl mb-2"></ion-icon>
            <p class="text-sm">Imagen no disponible</p>
        </div>
    <?php endif; ?>
</div>
                                
                                <!-- Información de la imagen -->
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-semibold text-gray-900 text-sm">
                                            Imagen #<?php echo htmlspecialchars($imagen['id_imagen']); ?>
                                        </h3>
                                        <span class="bg-purple-100 text-purple-800 px-2 py-1 text-xs font-semibold rounded">
                                            <?php echo htmlspecialchars(ucfirst($imagen['tipo_imagen'])); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (empty($paciente_seleccionado) || $paciente_seleccionado === 'todos'): ?>
                                        <!-- Mostrar información del paciente solo cuando no hay filtro específico -->
                                        <p class="text-gray-600 text-sm mb-2">
                                            <ion-icon name="person" class="text-xs mr-1"></ion-icon>
                                            Paciente: <?php echo htmlspecialchars($imagen['nombre'] . ' ' . $imagen['apellido']); ?>
                                        </p>
                                        
                                        <p class="text-gray-600 text-sm mb-2">
                                            <ion-icon name="call" class="text-xs mr-1"></ion-icon>
                                            Tel: <?php echo htmlspecialchars($imagen['telefono']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <p class="text-gray-600 text-sm mb-3">
                                        <ion-icon name="calendar" class="text-xs mr-1"></ion-icon>
                                        <?php echo date('d/m/Y H:i', strtotime($imagen['fecha_subida'])); ?>
                                    </p>
                                    
                                    <?php if (!empty($imagen['descripcion'])): ?>
                                        <p class="text-gray-700 text-xs mb-3 line-clamp-2">
                                            <?php echo htmlspecialchars($imagen['descripcion']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="flex justify-between items-center">
                                        <button class="text-purple-600 hover:text-purple-800 text-sm font-medium" 
                                                onclick="verImagenDetalle('<?php echo htmlspecialchars($imagen['nombre_archivo']); ?>', <?php echo $imagen['id_imagen']; ?>)">
                                            Ver imagen
                                        </button>
                                        <button class="text-gray-500 hover:text-gray-700" 
                                                onclick="descargarImagen('<?php echo htmlspecialchars($imagen['nombre_archivo']); ?>')">
                                            <ion-icon name="download"></ion-icon>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <div class="text-gray-500">
                        <ion-icon name="search" class="text-6xl text-gray-300 mb-4"></ion-icon>
                        <?php if (!empty($paciente_seleccionado) && $paciente_seleccionado !== 'todos'): ?>
                            <p class="text-lg">No hay imágenes para este paciente</p>
                            <p class="text-sm">Este paciente no tiene imágenes de tamizaje registradas</p>
                            <div class="mt-4">
                                <a href="?paciente=todos" 
                                   class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                    Ver todas las imágenes
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-lg">No hay imágenes de tamizaje registradas</p>
                            <p class="text-sm">Las imágenes médicas aparecerán aquí cuando los pacientes las suban</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Acciones Rápidas -->
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="dashboard_tamizaje.php" 
               class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                Volver al Dashboard
            </a>
            
            <a href="citas_tamizaje.php" 
               class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="calendar" class="mr-2"></ion-icon>
                Ver Citas
            </a>
            
            <a href="pacientes_tamizaje.php" 
               class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="people" class="mr-2"></ion-icon>
                Ver Pacientes
            </a>
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
        
        function toggleBlur(img) {
            img.classList.toggle('blur-sm');
        }
        
        function verDetalles(idImagen) {
            // Funcionalidad legacy mantenida por compatibilidad
            alert('Ver detalles de imagen #' + idImagen + '\n(Use "Ver imagen" para visualización completa)');
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
        
        // Efecto de carga suave para el dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.querySelector('select[name="paciente"]');
            if (select) {
                select.addEventListener('change', function() {
                    // Mostrar indicador de carga
                    const form = this.closest('form');
                    const button = document.createElement('span');
                    button.innerHTML = '⏳ Cargando...';
                    button.className = 'text-sm text-gray-500 ml-2';
                    form.appendChild(button);
                });
            }
        });
    </script>

</body>
</html>
