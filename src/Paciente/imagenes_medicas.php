<?php
//Aqui puedes colocar el código para la página de imágenes médicas del paciente

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
        error_log("Error en imágenes médicas paciente: " . $e->getMessage());
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
    <title>Mis Imágenes Médicas - PropielEquipo</title>
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
                    <a href="reservar.php" class="text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium transition duration-200">
                        <ion-icon name="calendar" class="mr-2"></ion-icon>Reservar Cita
                    </a>
                    <a href="reservas.php" class="text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium transition duration-200">
                        <ion-icon name="list" class="mr-2"></ion-icon>Mis Citas
                    </a>
                    <a href="imagenes_medicas.php" class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-lg font-medium">
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
                    <a href="reservar.php" class="block text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="calendar" class="mr-2"></ion-icon>Reservar Cita
                    </a>
                    <a href="reservas.php" class="block text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 px-4 py-2 rounded-lg font-medium">
                        <ion-icon name="list" class="mr-2"></ion-icon>Mis Citas
                    </a>
                    <a href="imagenes_medicas.php" class="block bg-emerald-100 text-emerald-700 px-4 py-2 rounded-lg font-medium">
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

    <!-- Header de Imágenes Médicas -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-2xl p-8 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Mis Imágenes Médicas</h1>
                    <p class="text-emerald-100"><?php echo htmlspecialchars($nombre . " " . $apellido); ?></p>
                    <p class="text-emerald-200 text-sm">Gestiona, visualiza y elimina tus imágenes médicas</p>
                    <div class="flex items-center mt-2 text-emerald-100 text-xs">
                        <ion-icon name="information-circle" class="mr-1"></ion-icon>
                        Haz clic en el ícono de basura para eliminar imágenes
                    </div>
                </div>
                <div class="hidden md:block">
                    <ion-icon name="images" class="text-6xl opacity-30"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Imágenes -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <?php
            try {
                $imagenes = $db_queries->getPatientImages($uid);
                $imagenes_por_tipo = [];
                foreach ($imagenes as $imagen) {
                    $tipo = $imagen['tipo_imagen'];
                    if (!isset($imagenes_por_tipo[$tipo])) {
                        $imagenes_por_tipo[$tipo] = 0;
                    }
                    $imagenes_por_tipo[$tipo]++;
                }
            } catch (Exception $e) {
                $imagenes = [];
                $imagenes_por_tipo = [];
            }
            ?>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Imágenes</p>
                        <p class="text-2xl font-bold text-emerald-600"><?php echo count($imagenes); ?></p>
                    </div>
                    <ion-icon name="images" class="text-3xl text-emerald-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Dermatología</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $imagenes_por_tipo['dermatologia'] ?? 0; ?></p>
                    </div>
                    <ion-icon name="body" class="text-3xl text-blue-500"></ion-icon>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Otras Especialidades</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo ($imagenes_por_tipo['podologia'] ?? 0) + ($imagenes_por_tipo['tamiz'] ?? 0); ?></p>
                    </div>
                    <ion-icon name="medical" class="text-3xl text-purple-500"></ion-icon>
                </div>
            </div>
        </div>

        <!-- Sección de subir nueva imagen -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                    <ion-icon name="cloud-upload" class="mr-2 text-emerald-600"></ion-icon>
                    Subir Nueva Imagen
                </h2>
                <p class="text-gray-600">Sube tus imágenes médicas de forma segura</p>
            </div>
            
            <!-- Área de mensajes -->
            <div id="message-area" class="hidden mb-6 p-4 rounded-lg"></div>
            
            <form id="upload-form" action="../php_action/upload_medical_image.php" method="post" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="id_paciente" value="<?php echo $uid; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tipo_imagen" class="block text-sm font-semibold text-gray-700 mb-2">
                            <ion-icon name="medical" class="mr-1 text-emerald-600"></ion-icon>
                            Tipo de Imagen:
                        </label>
                        <select name="tipo_imagen" id="tipo_imagen" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200">
                            <option value="">Seleccionar tipo</option>
                            <option value="dermatologia">🔹 Dermatología</option>
                            <option value="podologia">🔹 Podología</option>
                            <option value="tamiz">🔹 Tamizaje</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="imagen" class="block text-sm font-semibold text-gray-700 mb-2">
                            <ion-icon name="image" class="mr-1 text-emerald-600"></ion-icon>
                            Seleccionar Imagen:
                        </label>
                        <input type="file" name="imagen_medica" id="imagen" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200">
                        <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG, GIF, WebP (máx. 10MB)</p>
                    </div>
                </div>
                
                <div>
                    <label for="descripcion" class="block text-sm font-semibold text-gray-700 mb-2">
                        <ion-icon name="document-text" class="mr-1 text-emerald-600"></ion-icon>
                        Descripción:
                    </label>
                    <textarea name="descripcion" id="descripcion" rows="3" 
                              placeholder="Descripción opcional de la imagen..." 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition duration-200"></textarea>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" 
                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                        <ion-icon name="cloud-upload" class="mr-2"></ion-icon>
                        Subir Imagen
                    </button>
                    
                    <button type="reset" 
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                        <ion-icon name="refresh" class="mr-2"></ion-icon>
                        Limpiar
                    </button>
                </div>
            </form>
        </div>

        <!-- Galería de imágenes existentes -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-emerald-600 text-white p-6">
                <h2 class="text-xl font-bold flex items-center">
                    <ion-icon name="grid" class="mr-2"></ion-icon>
                    Mis Imágenes Guardadas
                    <span class="ml-2 text-sm bg-emerald-500 px-2 py-1 rounded">
                        <?php echo count($imagenes); ?> imágenes
                    </span>
                </h2>
            </div>
            
            <?php if (count($imagenes) > 0): ?>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($imagenes as $imagen): ?>
                            <div class="bg-gray-50 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300">
                                <!-- Imagen con efecto blur por privacidad -->
                                <div class="relative h-48 bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center">
                                    <?php if (!empty($imagen['nombre_archivo'])): ?>
                                        <img src="../Images/secure_image_viewer.php?image=<?php echo urlencode($imagen['nombre_archivo']); ?>" 
                                             alt="Imagen médica" 
                                             class="w-full h-full object-cover filter blur-sm hover:blur-none transition-all duration-300 cursor-pointer"
                                             onclick="openImageModal('../Images/secure_image_viewer.php?image=<?php echo urlencode($imagen['nombre_archivo']); ?>', '<?php echo htmlspecialchars($imagen['descripcion']); ?>', '<?php echo htmlspecialchars($imagen['tipo_imagen']); ?>', <?php echo $imagen['id_imagen']; ?>)">>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent flex items-center justify-center opacity-100 hover:opacity-0 transition-opacity duration-300 pointer-events-none">
                                            <div class="text-center">
                                                <ion-icon name="eye" class="text-white text-2xl mb-1 drop-shadow-lg"></ion-icon>
                                                <p class="text-white text-xs drop-shadow-lg">Click para ver</p>
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
                                        <?php 
                                        $tipo = $imagen['tipo_imagen'];
                                        $color_tipo = 'emerald';
                                        switch(strtolower($tipo)) {
                                            case 'dermatologia':
                                                $color_tipo = 'blue';
                                                break;
                                            case 'podologia':
                                                $color_tipo = 'green';
                                                break;
                                            case 'tamiz':
                                                $color_tipo = 'purple';
                                                break;
                                        }
                                        ?>
                                        <span class="bg-<?php echo $color_tipo; ?>-100 text-<?php echo $color_tipo; ?>-800 px-2 py-1 text-xs font-semibold rounded">
                                            <?php echo htmlspecialchars(ucfirst($tipo)); ?>
                                        </span>
                                    </div>
                                    
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
                                        <button class="text-emerald-600 hover:text-emerald-800 text-sm font-medium" 
                                                onclick="openImageModal('../Images/secure_image_viewer.php?image=<?php echo urlencode($imagen['nombre_archivo']); ?>', '<?php echo htmlspecialchars($imagen['descripcion']); ?>', '<?php echo htmlspecialchars($imagen['tipo_imagen']); ?>', <?php echo $imagen['id_imagen']; ?>)">>
                                            <ion-icon name="eye" class="mr-1"></ion-icon>Ver imagen
                                        </button>
                                        <div class="flex gap-2">
                                            <button class="text-gray-500 hover:text-gray-700" 
                                                    onclick="descargarImagenSegura('<?php echo htmlspecialchars($imagen['nombre_archivo']); ?>')"
                                                    title="Descargar imagen">
                                                <ion-icon name="download"></ion-icon>
                                            </button>
                                            <button class="text-red-500 hover:text-red-700" 
                                                    onclick="showDeleteModal(<?php echo $imagen['id_imagen']; ?>, '<?php echo htmlspecialchars($imagen['tipo_imagen']); ?>', '<?php echo htmlspecialchars($imagen['descripcion']); ?>')"
                                                    title="Eliminar imagen">
                                                <ion-icon name="trash"></ion-icon>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <div class="text-gray-500">
                        <ion-icon name="images-outline" class="text-6xl text-gray-300 mb-4"></ion-icon>
                        <p class="text-lg font-medium">No tienes imágenes médicas guardadas</p>
                        <p class="text-sm mt-2">¡Sube tu primera imagen médica!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Acciones Rápidas -->
        <div class="mt-8 flex flex-wrap gap-4">
            <a href="dashboardpaciente.php" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                Volver al Perfil
            </a>
            
            <a href="reservas.php" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="calendar" class="mr-2"></ion-icon>
                Mis Citas
            </a>
            
            <a href="reservar.php" 
               class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                <ion-icon name="add-circle" class="mr-2"></ion-icon>
                Nueva Cita
            </a>
        </div>
    </div>

    <!-- Modal para ver imagen completa -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50 hidden">
        <div class="max-w-4xl max-h-full p-4 relative">
            <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
                <div class="flex items-center justify-between p-4 bg-emerald-600 text-white">
                    <h3 class="text-lg font-bold" id="modalTitulo">Imagen Médica</h3>
                    <button onclick="closeImageModal()" class="text-white hover:text-gray-200">
                        <ion-icon name="close" class="text-2xl"></ion-icon>
                    </button>
                </div>
                <div class="p-4">
                    <div class="text-center">
                        <img id="modalImage" src="" alt="Imagen médica" class="max-w-full max-h-96 object-contain mx-auto rounded-lg">
                    </div>
                    <div id="modalInfo" class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <p id="modalDescription" class="text-gray-700"></p>
                        <p id="modalType" class="text-sm text-gray-500 mt-2"></p>
                    </div>
                    <div class="mt-4 flex justify-center gap-4">
                        <button onclick="descargarImagenModal()" 
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <ion-icon name="download" class="mr-2"></ion-icon>
                            Descargar
                        </button>
                        <button onclick="eliminarImagenDesdeModal()" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <ion-icon name="trash" class="mr-2"></ion-icon>
                            Eliminar
                        </button>
                        <button onclick="closeImageModal()" 
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para eliminar imagen -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-75 items-center justify-center z-50 hidden p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <!-- Header del Modal -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-6 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <ion-icon name="warning" class="text-2xl mr-3"></ion-icon>
                        <h2 class="text-xl font-bold">Eliminar Imagen</h2>
                    </div>
                    <button onclick="closeDeleteModal()" class="text-white hover:text-gray-200 text-2xl">
                        <ion-icon name="close"></ion-icon>
                    </button>
                </div>
            </div>

            <!-- Contenido del Modal -->
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <ion-icon name="trash" class="text-2xl text-red-600"></ion-icon>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">¿Confirmas la eliminación?</h3>
                    <p class="text-sm text-gray-600 mb-4">Esta acción no se puede deshacer. La imagen será eliminada permanentemente.</p>
                </div>

                <!-- Detalles de la imagen a eliminar -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="font-semibold text-gray-800 mb-2">Detalles de la imagen:</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID Imagen:</span>
                            <span class="font-medium" id="delete-imagen-id">#--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tipo:</span>
                            <span class="font-medium" id="delete-tipo">--</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Descripción:</span>
                            <span class="font-medium" id="delete-descripcion">--</span>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" 
                            onclick="closeDeleteModal()" 
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-4 rounded-lg transition duration-200">
                        <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                        Cancelar
                    </button>
                    
                    <button type="button" 
                            onclick="confirmDeleteImage()" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200">
                        <ion-icon name="trash" class="mr-2"></ion-icon>
                        Sí, Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let imagenActual = '';
        let imagenParaEliminar = null;
        let imagenModalData = { id: null, tipo: '', descripcion: '' };

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

        function openImageModal(imagePath, description, type, imageId = null) {
            imagenActual = imagePath;
            
            // Almacenar datos para usar en eliminación
            imagenModalData = {
                id: imageId,
                tipo: type,
                descripcion: description
            };
            
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const modalDescription = document.getElementById('modalDescription');
            const modalType = document.getElementById('modalType');
            const modalTitulo = document.getElementById('modalTitulo');
            
            modalImg.src = imagePath;
            modalDescription.textContent = description || 'Sin descripción';
            modalType.textContent = 'Tipo: ' + (type ? type.charAt(0).toUpperCase() + type.slice(1) : 'No especificado');
            modalTitulo.textContent = 'Imagen Médica - ' + (type ? type.charAt(0).toUpperCase() + type.slice(1) : 'General');
            
            // Mostrar modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        function descargarImagenModal() {
            if (imagenActual) {
                const nombreArchivo = imagenActual.split('=').pop();
                descargarImagenSegura(nombreArchivo);
            }
        }

        function eliminarImagenDesdeModal() {
            if (imagenModalData.id) {
                // Cerrar modal de imagen y abrir modal de eliminación
                closeImageModal();
                showDeleteModal(imagenModalData.id, imagenModalData.tipo, imagenModalData.descripcion);
            } else {
                alert('Error: No se pudo identificar la imagen para eliminar.');
            }
        }

        function descargarImagen(nombreArchivo) {
            // Función legacy - usar descargarImagenSegura en su lugar
            descargarImagenSegura(nombreArchivo);
        }

        function descargarImagenSegura(nombreArchivo) {
            const link = document.createElement('a');
            link.href = '../Images/secure_image_viewer.php?image=' + encodeURIComponent(nombreArchivo) + '&action=download';
            link.download = nombreArchivo;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Funciones para eliminar imagen
        function showDeleteModal(imagenId, tipo, descripcion) {
            imagenParaEliminar = imagenId;
            
            // Actualizar los detalles en el modal
            document.getElementById('delete-imagen-id').textContent = '#' + imagenId;
            document.getElementById('delete-tipo').textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1);
            document.getElementById('delete-descripcion').textContent = descripcion || 'Sin descripción';
            
            // Mostrar el modal
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            
            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            
            // Restaurar scroll del body
            document.body.style.overflow = 'auto';
            
            // Limpiar la variable
            imagenParaEliminar = null;
        }

        async function confirmDeleteImage() {
            if (!imagenParaEliminar) {
                alert('Error: No se ha seleccionado una imagen para eliminar.');
                return;
            }

            try {
                // Mostrar mensaje de procesamiento
                const confirmButton = document.querySelector('button[onclick="confirmDeleteImage()"]');
                const originalText = confirmButton.innerHTML;
                confirmButton.innerHTML = '<ion-icon name="hourglass" class="mr-2"></ion-icon>Eliminando...';
                confirmButton.disabled = true;

                // Enviar petición de eliminación
                const response = await fetch('php_action/delete_medical_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        imagen_id: imagenParaEliminar
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Éxito - mostrar mensaje personalizado y recargar
                    const tipoImagen = data.tipo_imagen ? data.tipo_imagen.charAt(0).toUpperCase() + data.tipo_imagen.slice(1) : 'Imagen';
                    alert(`✅ ${tipoImagen} eliminada exitosamente.\\n\\nLa imagen #${data.imagen_id} ha sido eliminada permanentemente de tu galería médica.`);
                    window.location.reload();
                } else {
                    // Error - mostrar mensaje
                    alert('❌ Error: ' + (data.message || 'No se pudo eliminar la imagen.'));
                    
                    // Restaurar botón
                    confirmButton.innerHTML = originalText;
                    confirmButton.disabled = false;
                }
            } catch (error) {
                console.error('Error al eliminar imagen:', error);
                alert('❌ Error de conexión. Por favor intenta nuevamente.');
                
                // Restaurar botón
                const confirmButton = document.querySelector('button[onclick="confirmDeleteImage()"]');
                confirmButton.innerHTML = '<ion-icon name="trash" class="mr-2"></ion-icon>Sí, Eliminar';
                confirmButton.disabled = false;
            }
        }

        // Cerrar modales con tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal();
                closeDeleteModal();
            }
        });

        // Cerrar modal al hacer clic fuera de la imagen
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Cerrar modal de eliminación al hacer clic fuera
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Manejar el envío del formulario
        document.getElementById('upload-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const messageArea = document.getElementById('message-area');
            
            // Mostrar mensaje de carga
            showMessage('Subiendo imagen...', 'info');
            
            fetch('../php_action/upload_medical_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    // Limpiar el formulario
                    this.reset();
                    // Recargar la página después de 2 segundos para mostrar la nueva imagen
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error al subir la imagen. Por favor, inténtalo de nuevo.', 'error');
            });
        });

        function showMessage(message, type) {
            const messageArea = document.getElementById('message-area');
            messageArea.className = 'mb-6 p-4 rounded-lg ';
            
            if (type === 'success') {
                messageArea.className += 'bg-green-100 border border-green-400 text-green-700';
            } else if (type === 'error') {
                messageArea.className += 'bg-red-100 border border-red-400 text-red-700';
            } else if (type === 'info') {
                messageArea.className += 'bg-blue-100 border border-blue-400 text-blue-700';
            }
            
            messageArea.textContent = message;
            messageArea.classList.remove('hidden');
            
            // Ocultar mensaje después de 5 segundos (excepto para mensajes de éxito)
            if (type !== 'success') {
                setTimeout(() => {
                    messageArea.classList.add('hidden');
                }, 5000);
            }
        }
    </script>

</body>
</html>