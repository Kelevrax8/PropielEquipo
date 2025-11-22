<?php
session_start();

// Verificar sesión de administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] !== 4) {
    header('Location: login_admin.php?error=unauthorized');
    exit();
}

require_once '../database_connection.php';

// Obtener configuración actual de pagos
$config_pagos = null;
try {
    $stmt = $conex->query("SELECT * FROM configuracion_pagos WHERE activo = 1 LIMIT 1");
    if ($stmt->num_rows > 0) {
        $config_pagos = $stmt->fetch_assoc();
    }
} catch (Exception $e) {
    error_log("Error al obtener configuración de pagos: " . $e->getMessage());
}

// Mensajes de éxito o error
$mensaje = '';
$tipo_mensaje = '';
if (isset($_GET['success'])) {
    $mensaje = 'Datos bancarios actualizados correctamente';
    $tipo_mensaje = 'success';
} elseif (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'clabe_invalid':
            $mensaje = 'La CLABE debe tener exactamente 18 dígitos';
            $tipo_mensaje = 'error';
            break;
        case 'update_failed':
            $mensaje = 'Error al actualizar los datos. Intenta nuevamente';
            $tipo_mensaje = 'error';
            break;
        case 'missing_fields':
            $mensaje = 'Por favor completa todos los campos obligatorios';
            $tipo_mensaje = 'error';
            break;
        default:
            $mensaje = 'Error desconocido';
            $tipo_mensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Pagos - Admin</title>
    <link href="../output.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body class="bg-gray-50">

    <?php include 'shared/navbar_admin.php'; ?>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center">
                <ion-icon name="card" class="mr-3 text-red-600"></ion-icon>
                Configuración de Pagos
            </h1>
            <p class="text-gray-600">
                Administra los datos bancarios para recibir pagos SPEI de los pacientes
            </p>
        </div>

        <!-- Mensaje de éxito/error -->
        <?php if (!empty($mensaje)): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $tipo_mensaje === 'success' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500'; ?>">
                <div class="flex items-center">
                    <ion-icon name="<?php echo $tipo_mensaje === 'success' ? 'checkmark-circle' : 'alert-circle'; ?>" 
                              class="text-2xl <?php echo $tipo_mensaje === 'success' ? 'text-green-600' : 'text-red-600'; ?> mr-3"></ion-icon>
                    <p class="<?php echo $tipo_mensaje === 'success' ? 'text-green-800' : 'text-red-800'; ?> font-semibold">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Información importante -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
            <div class="flex items-start">
                <ion-icon name="information-circle" class="text-2xl text-blue-600 mr-3 mt-1"></ion-icon>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-1">Información Importante</h3>
                    <p class="text-sm text-blue-800">
                        Esta configuración afecta directamente la información bancaria mostrada a los pacientes 
                        al momento de realizar sus pagos. Asegúrate de que todos los datos sean correctos.
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-red-600 to-red-700 p-6">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <ion-icon name="create" class="mr-2"></ion-icon>
                    Datos Bancarios
                </h2>
            </div>

            <form action="php_action/actualizar_datos_bancarios.php" method="POST" class="p-6 space-y-6">
                
                <!-- Nombre del Banco -->
                <div>
                    <label for="banco" class="block text-sm font-semibold text-gray-700 mb-2">
                        <ion-icon name="business" class="mr-1 text-red-600"></ion-icon>
                        Nombre del Banco *
                    </label>
                    <input 
                        type="text" 
                        id="banco" 
                        name="banco" 
                        required
                        value="<?php echo htmlspecialchars($config_pagos['banco'] ?? ''); ?>"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition"
                        placeholder="Ej: BBVA, Banorte, Santander"
                    >
                </div>

                <!-- Titular de la Cuenta -->
                <div>
                    <label for="titular" class="block text-sm font-semibold text-gray-700 mb-2">
                        <ion-icon name="person" class="mr-1 text-red-600"></ion-icon>
                        Titular de la Cuenta *
                    </label>
                    <input 
                        type="text" 
                        id="titular" 
                        name="titular" 
                        required
                        value="<?php echo htmlspecialchars($config_pagos['titular'] ?? ''); ?>"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition"
                        placeholder="Nombre completo del titular"
                    >
                </div>

                <!-- CLABE -->
                <div>
                    <label for="clabe" class="block text-sm font-semibold text-gray-700 mb-2">
                        <ion-icon name="key" class="mr-1 text-red-600"></ion-icon>
                        CLABE Interbancaria *
                    </label>
                    <input 
                        type="text" 
                        id="clabe" 
                        name="clabe" 
                        required
                        maxlength="18"
                        pattern="[0-9]{18}"
                        value="<?php echo htmlspecialchars($config_pagos['clabe'] ?? ''); ?>"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition font-mono text-lg"
                        placeholder="18 dígitos"
                        oninput="formatCLABE(this)"
                    >
                    <p class="mt-2 text-sm text-gray-500">
                        <ion-icon name="information-circle" class="text-blue-500"></ion-icon>
                        La CLABE debe tener exactamente 18 dígitos numéricos
                    </p>
                </div>

                <!-- Número de Cuenta -->
                <div>
                    <label for="numero_cuenta" class="block text-sm font-semibold text-gray-700 mb-2">
                        <ion-icon name="card" class="mr-1 text-red-600"></ion-icon>
                        Número de Cuenta *
                    </label>
                    <input 
                        type="text" 
                        id="numero_cuenta" 
                        name="numero_cuenta" 
                        required
                        value="<?php echo htmlspecialchars($config_pagos['numero_cuenta'] ?? ''); ?>"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition font-mono"
                        placeholder="Número de cuenta (10-16 dígitos)"
                    >
                </div>

                <!-- Información de Referencia -->
                <div>
                    <label for="referencia_info" class="block text-sm font-semibold text-gray-700 mb-2">
                        <ion-icon name="document-text" class="mr-1 text-red-600"></ion-icon>
                        Información de Referencia (Opcional)
                    </label>
                    <textarea 
                        id="referencia_info" 
                        name="referencia_info" 
                        rows="4"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition resize-none"
                        placeholder="Instrucciones adicionales para los pacientes (ej: incluir nombre del paciente en la referencia)"
                    ><?php echo htmlspecialchars($config_pagos['referencia_info'] ?? ''); ?></textarea>
                    <p class="mt-2 text-sm text-gray-500">
                        Esta información se mostrará a los pacientes al momento de realizar el pago
                    </p>
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="dashboard_admin.php" 
                       class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition flex items-center">
                        <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                        Cancelar
                    </a>
                    
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-lg transition shadow-lg hover:shadow-xl flex items-center">
                        <ion-icon name="save" class="mr-2"></ion-icon>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

        <!-- Vista Previa -->
        <div class="mt-8 bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <ion-icon name="eye" class="mr-2 text-purple-600"></ion-icon>
                Vista Previa (Cómo lo verán los pacientes)
            </h3>
            <div class="bg-gradient-to-br from-red-50 to-red-100 p-6 rounded-lg border-2 border-red-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 font-semibold mb-1">Banco:</p>
                        <p class="font-bold text-gray-900" id="preview_banco">
                            <?php echo htmlspecialchars($config_pagos['banco'] ?? 'No configurado'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 font-semibold mb-1">Titular:</p>
                        <p class="font-bold text-gray-900" id="preview_titular">
                            <?php echo htmlspecialchars($config_pagos['titular'] ?? 'No configurado'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 font-semibold mb-1">CLABE:</p>
                        <p class="font-mono font-bold text-lg text-gray-900" id="preview_clabe">
                            <?php echo htmlspecialchars($config_pagos['clabe'] ?? 'No configurado'); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 font-semibold mb-1">Número de Cuenta:</p>
                        <p class="font-mono font-bold text-gray-900" id="preview_numero_cuenta">
                            <?php echo htmlspecialchars($config_pagos['numero_cuenta'] ?? 'No configurado'); ?>
                        </p>
                    </div>
                </div>
                <?php if (!empty($config_pagos['referencia_info'])): ?>
                    <div class="mt-4 pt-4 border-t border-red-300">
                        <p class="text-sm text-gray-600 font-semibold mb-1">Información de Referencia:</p>
                        <p class="text-gray-700" id="preview_referencia_info">
                            <?php echo nl2br(htmlspecialchars($config_pagos['referencia_info'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Formatear CLABE en tiempo real
        function formatCLABE(input) {
            // Solo permitir números
            input.value = input.value.replace(/\D/g, '');
            
            // Limitar a 18 dígitos
            if (input.value.length > 18) {
                input.value = input.value.slice(0, 18);
            }
            
            // Validar longitud
            if (input.value.length === 18) {
                input.classList.remove('border-red-500');
                input.classList.add('border-green-500');
            } else if (input.value.length > 0) {
                input.classList.remove('border-green-500');
                input.classList.add('border-red-500');
            } else {
                input.classList.remove('border-red-500', 'border-green-500');
            }
        }
        
        // Live preview
        document.getElementById('banco').addEventListener('input', function(e) {
            document.getElementById('preview_banco').textContent = e.target.value || 'No configurado';
        });
        
        document.getElementById('titular').addEventListener('input', function(e) {
            document.getElementById('preview_titular').textContent = e.target.value || 'No configurado';
        });
        
        document.getElementById('clabe').addEventListener('input', function(e) {
            document.getElementById('preview_clabe').textContent = e.target.value || 'No configurado';
        });
        
        document.getElementById('numero_cuenta').addEventListener('input', function(e) {
            document.getElementById('preview_numero_cuenta').textContent = e.target.value || 'No configurado';
        });
        
        document.getElementById('referencia_info').addEventListener('input', function(e) {
            document.getElementById('preview_referencia_info').textContent = e.target.value || '';
        });
    </script>

</body>
</html>
