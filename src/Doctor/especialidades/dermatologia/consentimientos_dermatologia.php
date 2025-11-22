<?php
// Consentimientos informados para Dermatología
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
    
    // Obtener término de búsqueda
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Obtener número de página actual
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $records_per_page = 10;
    
    // Obtener todos los archivos PDF de consentimientos
    $consentimientos_all = [];
    $consentimientos_dir = '../../../consentimientos/';
    
    if (is_dir($consentimientos_dir)) {
        $files = glob($consentimientos_dir . '*.pdf');
        
        foreach ($files as $file) {
            $filename = basename($file);
            $filepath = $file;
            
            // Obtener información del archivo
            $filesize = filesize($file);
            $filedate = filemtime($file);
            
            $info = [
                'filename' => $filename,
                'filepath' => $filepath,
                'filesize' => $filesize,
                'filedate' => $filedate,
                'paciente_nombre' => 'Desconocido',
                'paciente_apellido' => '',
                'nombre_completo' => 'Desconocido',
                'user_id' => null,
                'telefono' => null
            ];
            
            // Intentar extraer el user_id primero
            if (preg_match('/usuario_(\d+)/', $filename, $matches)) {
                $info['user_id'] = $matches[1];
                
                // Obtener el nombre completo del paciente desde la base de datos
                try {
                    $paciente = $db_queries->getUserById($info['user_id']);
                    if ($paciente) {
                        $info['paciente_nombre'] = $paciente['nombre'];
                        $info['paciente_apellido'] = $paciente['apellido'];
                        $info['nombre_completo'] = $paciente['nombre'] . ' ' . $paciente['apellido'];
                        $info['telefono'] = $paciente['telefono'];
                    }
                } catch (Exception $e) {
                    error_log("Error obteniendo datos del paciente: " . $e->getMessage());
                }
            }
            
            // Si no se pudo obtener de la BD, extraer del nombre del archivo
            if ($info['nombre_completo'] === 'Desconocido' && preg_match('/consentimiento_([^_]+_[^_]+)_/', $filename, $matches)) {
                $nombre_archivo = str_replace('_', ' ', $matches[1]);
                $info['nombre_completo'] = $nombre_archivo;
                $partes = explode(' ', $nombre_archivo, 2);
                $info['paciente_nombre'] = $partes[0] ?? 'Desconocido';
                $info['paciente_apellido'] = $partes[1] ?? '';
            }
            
            $consentimientos_all[] = $info;
        }
        
        // Ordenar por fecha más reciente primero
        usort($consentimientos_all, function($a, $b) {
            return $b['filedate'] - $a['filedate'];
        });
    }
    
    // Filtrar por término de búsqueda
    $consentimientos_filtered = $consentimientos_all;
    if (!empty($search_term)) {
        $consentimientos_filtered = array_filter($consentimientos_all, function($consent) use ($search_term) {
            $search_lower = mb_strtolower($search_term, 'UTF-8');
            $nombre_lower = mb_strtolower($consent['nombre_completo'], 'UTF-8');
            $filename_lower = mb_strtolower($consent['filename'], 'UTF-8');
            $user_id_str = $consent['user_id'] ? strval($consent['user_id']) : '';
            
            return strpos($nombre_lower, $search_lower) !== false ||
                   strpos($filename_lower, $search_lower) !== false ||
                   strpos($user_id_str, $search_lower) !== false;
        });
    }
    
    // Calcular paginación
    $total_records = count($consentimientos_filtered);
    $total_pages = ceil($total_records / $records_per_page);
    $page = min($page, max(1, $total_pages)); // Asegurar que la página esté en rango válido
    
    // Obtener registros de la página actual
    $offset = ($page - 1) * $records_per_page;
    $consentimientos = array_slice($consentimientos_filtered, $offset, $records_per_page);
    
} catch (Exception $e) {
    error_log("Error en consentimientos dermatología: " . $e->getMessage());
    header('location: ../../../Landing/login.html');
    exit();
}

// Función para formatear tamaño de archivo
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consentimientos Informados - Dermatología</title>
    <link href="../../../output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-blue-100 flex flex-col min-h-screen">
    
    <?php include '../../shared/navbar_doctor.php'; ?>

    <!-- Header -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Consentimientos Informados</h1>
                    <p class="text-blue-100">Dr. <?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-blue-200 text-sm">Documentos legales firmados por los pacientes</p>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="document-text" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <!-- Buscador -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <form method="GET" action="" id="search-form" class="flex gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" 
                               id="search-input"
                               name="search" 
                               value="<?php echo htmlspecialchars($search_term); ?>"
                               placeholder="Buscar por nombre del paciente, ID o nombre de archivo..."
                               class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               autocomplete="off">
                        <ion-icon name="search" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></ion-icon>
                        <div id="search-loading" class="absolute right-4 top-1/2 transform -translate-y-1/2 hidden">
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                        </div>
                    </div>
                </div>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                    <ion-icon name="search" class="mr-2"></ion-icon>
                    Buscar
                </button>
                <?php if (!empty($search_term)): ?>
                <button type="button"
                        onclick="clearSearch()"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                    <ion-icon name="close" class="mr-2"></ion-icon>
                    Limpiar
                </button>
                <?php endif; ?>
            </form>
            
            <div id="search-info" class="mt-4 text-sm text-gray-600 <?php echo empty($search_term) ? 'hidden' : ''; ?>">
                <ion-icon name="information-circle" class="mr-1"></ion-icon>
                <span id="search-results-text">
                    Mostrando <?php echo $total_records; ?> resultado(s) para "<?php echo htmlspecialchars($search_term); ?>"
                </span>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Consentimientos</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo count($consentimientos_all); ?></p>
                        <?php if (!empty($search_term)): ?>
                        <p class="text-xs text-gray-500 mt-1">Mostrando: <?php echo $total_records; ?></p>
                        <?php endif; ?>
                    </div>
                    <ion-icon name="documents" class="text-3xl text-blue-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Espacio Utilizado</p>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php 
                            $total_size = array_sum(array_column($consentimientos_all, 'filesize'));
                            echo formatBytes($total_size);
                            ?>
                        </p>
                    </div>
                    <ion-icon name="folder" class="text-3xl text-blue-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Último Firmado</p>
                        <p class="text-lg font-bold text-blue-600">
                            <?php 
                            if (!empty($consentimientos_all)) {
                                echo date('d/m/Y', $consentimientos_all[0]['filedate']);
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </p>
                    </div>
                    <ion-icon name="time" class="text-3xl text-blue-500"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Lista de Consentimientos -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    <ion-icon name="folder-open" class="mr-2"></ion-icon>
                    Archivo de Consentimientos
                </h2>
                <div class="text-sm text-gray-500">
                    <?php 
                    if (!empty($search_term)) {
                        echo "Mostrando " . count($consentimientos) . " de " . $total_records . " resultado(s) filtrado(s)";
                    } else {
                        echo "Página " . $page . " de " . $total_pages . " (" . $total_records . " total)";
                    }
                    ?>
                </div>
            </div>

            <?php if (empty($consentimientos)): ?>
                <!-- Sin consentimientos -->
                <div class="text-center py-12">
                    <ion-icon name="document-text" class="text-6xl text-gray-300 mb-4"></ion-icon>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay consentimientos registrados</h3>
                    <p class="text-gray-500">Los consentimientos firmados por los pacientes aparecerán aquí</p>
                </div>
            <?php else: ?>
                <!-- Tabla de consentimientos -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Paciente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha de Firma
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tamaño
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($consentimientos as $consent): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <ion-icon name="person" class="text-blue-600 text-xl"></ion-icon>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($consent['nombre_completo']); ?>
                                            </div>
                                            <?php if ($consent['telefono']): ?>
                                            <div class="text-sm text-gray-500">
                                                Tel: <?php echo htmlspecialchars($consent['telefono']); ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo date('d/m/Y', $consent['filedate']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('H:i:s', $consent['filedate']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo formatBytes($consent['filesize']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="viewPDF('<?php echo htmlspecialchars($consent['filename']); ?>')"
                                            class="text-blue-600 hover:text-blue-900 mr-3 inline-flex items-center">
                                        <ion-icon name="eye" class="mr-1"></ion-icon>
                                        Ver
                                    </button>
                                    <button onclick="downloadPDF('<?php echo htmlspecialchars($consent['filename']); ?>')"
                                            class="text-green-600 hover:text-green-900 inline-flex items-center">
                                        <ion-icon name="download" class="mr-1"></ion-icon>
                                        Descargar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <?php if ($total_pages > 1): ?>
                <div class="mt-6 flex items-center justify-between border-t pt-4">
                    <div class="text-sm text-gray-700">
                        Mostrando 
                        <span class="font-medium"><?php echo $offset + 1; ?></span>
                        a 
                        <span class="font-medium"><?php echo min($offset + $records_per_page, $total_records); ?></span>
                        de 
                        <span class="font-medium"><?php echo $total_records; ?></span>
                        resultados
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <!-- Botón Primera página -->
                        <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                            <ion-icon name="play-skip-back"></ion-icon>
                        </a>
                        <?php endif; ?>
                        
                        <!-- Botón Anterior -->
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                            <ion-icon name="chevron-back"></ion-icon>
                        </a>
                        <?php else: ?>
                        <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">
                            <ion-icon name="chevron-back"></ion-icon>
                        </span>
                        <?php endif; ?>
                        
                        <!-- Números de página -->
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++): 
                        ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                               class="px-4 py-2 rounded-lg transition duration-200 <?php echo $i === $page ? 'bg-blue-600 text-white font-bold' : 'bg-gray-200 hover:bg-gray-300 text-gray-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <!-- Botón Siguiente -->
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                            <ion-icon name="chevron-forward"></ion-icon>
                        </a>
                        <?php else: ?>
                        <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">
                            <ion-icon name="chevron-forward"></ion-icon>
                        </span>
                        <?php endif; ?>
                        
                        <!-- Botón Última página -->
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search_term) ? '&search=' . urlencode($search_term) : ''; ?>" 
                           class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                            <ion-icon name="play-skip-forward"></ion-icon>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para visualizar PDF -->
    <div id="pdf-modal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50 hidden p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl h-[90vh] overflow-hidden flex flex-col">
            <!-- Header del Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 flex items-center justify-between">
                <div class="flex items-center">
                    <ion-icon name="document-text" class="text-2xl mr-3"></ion-icon>
                    <h2 class="text-xl font-bold" id="modal-title">Visualizar Consentimiento</h2>
                </div>
                <button onclick="closePDFModal()" class="text-white hover:text-gray-200 text-2xl">
                    <ion-icon name="close"></ion-icon>
                </button>
            </div>

            <!-- Contenido del Modal -->
            <div class="flex-1 overflow-hidden">
                <iframe id="pdf-frame" class="w-full h-full" frameborder="0"></iframe>
            </div>

            <!-- Footer del Modal -->
            <div class="border-t bg-gray-50 px-4 py-3 flex justify-end space-x-3">
                <button onclick="closePDFModal()" 
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition duration-200">
                    Cerrar
                </button>
                <button id="download-link" onclick="return false;"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 inline-flex items-center">
                    <ion-icon name="download" class="mr-2"></ion-icon>
                    Descargar
                </button>
            </div>
        </div>
    </div>

    <script>
        function viewPDF(filename) {
            const modal = document.getElementById('pdf-modal');
            const frame = document.getElementById('pdf-frame');
            const title = document.getElementById('modal-title');
            const downloadLink = document.getElementById('download-link');
            
            // Usar el viewer seguro de PHP
            const pdfPath = '../../../consentimientos/view_pdf.php?file=' + encodeURIComponent(filename);
            
            frame.src = pdfPath;
            title.textContent = filename;
            downloadLink.onclick = function(e) {
                e.preventDefault();
                downloadPDF(filename);
            };
            
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            
            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        }

        function downloadPDF(filename) {
            // Usar el viewer seguro con parámetro de descarga
            const downloadUrl = '../../../consentimientos/view_pdf.php?file=' + encodeURIComponent(filename) + '&download=1';
            
            // Crear un enlace temporal y hacer clic en él
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function closePDFModal() {
            const modal = document.getElementById('pdf-modal');
            const frame = document.getElementById('pdf-frame');
            
            modal.classList.add('hidden');
            modal.style.display = 'none';
            frame.src = '';
            
            // Restaurar scroll del body
            document.body.style.overflow = 'auto';
        }

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePDFModal();
            }
        });

        // Real-time search functionality
        let searchTimeout = null;
        const searchInput = document.getElementById('search-input');
        const searchLoading = document.getElementById('search-loading');
        
        function clearSearch() {
            window.location.href = '?';
        }
        
        function performSearch() {
            const searchTerm = searchInput.value.trim();
            
            // Si el término de búsqueda está vacío, redirigir sin parámetros
            if (searchTerm === '') {
                window.location.href = '?';
                return;
            }
            
            // Construir URL con término de búsqueda
            const url = new URL(window.location.href);
            url.searchParams.set('search', searchTerm);
            url.searchParams.delete('page'); // Reset a la primera página al buscar
            
            // Mostrar indicador de carga
            searchLoading.classList.remove('hidden');
            
            // Redirigir a la nueva URL
            window.location.href = url.toString();
        }
        
        // Escuchar cambios en el input de búsqueda
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                // Limpiar timeout anterior
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                
                // Mostrar indicador de carga después de 300ms
                searchTimeout = setTimeout(function() {
                    performSearch();
                }, 500); // Esperar 500ms después de que el usuario deje de escribir
            });
            
            // También permitir buscar con Enter
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    
                    // Cancelar timeout si existe
                    if (searchTimeout) {
                        clearTimeout(searchTimeout);
                    }
                    
                    // Buscar inmediatamente
                    performSearch();
                }
            });
        }
        
        // Prevenir envío normal del formulario (ya que usamos JavaScript)
        const searchForm = document.getElementById('search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Cancelar timeout si existe
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                
                // Buscar inmediatamente
                performSearch();
            });
        }
    </script>

</body>
</html>
