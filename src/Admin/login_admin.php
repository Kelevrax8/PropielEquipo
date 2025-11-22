<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador - PropielEquipo</title>
    <link href="../output.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center p-4">
    
    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden opacity-10">
        <div class="absolute -inset-[10px] opacity-50">
            <div class="absolute top-0 -left-4 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl animate-blob"></div>
            <div class="absolute top-0 -right-4 w-72 h-72 bg-yellow-500 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-4000"></div>
        </div>
    </div>

    <div class="relative w-full max-w-md">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 p-8 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full mb-4 shadow-lg">
                    <ion-icon name="shield-checkmark" class="text-5xl text-red-600"></ion-icon>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Administrador</h1>
                <p class="text-red-100">Panel de Control del Sistema</p>
            </div>

            <!-- Form -->
            <div class="p-8">
                <form action="php_action/login_admin.php" method="POST" class="space-y-6">
                    
                    <!-- Usuario -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                            <ion-icon name="person" class="mr-2 text-red-600"></ion-icon>
                            Usuario
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required
                            autocomplete="username"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200"
                            placeholder="Ingresa tu usuario"
                        >
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <ion-icon name="lock-closed" class="mr-2 text-red-600"></ion-icon>
                            Contraseña
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                autocomplete="current-password"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200"
                                placeholder="Ingresa tu contraseña"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword()"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <ion-icon name="eye" id="toggleIcon" class="text-xl"></ion-icon>
                            </button>
                        </div>
                    </div>

                    <!-- Warning Box -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                        <div class="flex items-center">
                            <ion-icon name="warning" class="text-yellow-600 text-xl mr-3"></ion-icon>
                            <p class="text-sm text-yellow-800">
                                Acceso restringido solo para personal autorizado
                            </p>
                        </div>
                    </div>

                    <!-- Error Messages -->
                    <?php
                    if (isset($_GET['error'])) {
                        $error_message = '';
                        $debug_info = '';
                        
                        switch ($_GET['error']) {
                            case 'invalid_credentials':
                                $error_message = 'Usuario o contraseña incorrectos';
                                if (isset($_GET['debug'])) {
                                    $debug_info = ' (Debug: ' . htmlspecialchars($_GET['debug']) . ')';
                                }
                                break;
                            case 'empty_fields':
                                $error_message = 'Por favor completa todos los campos';
                                break;
                            case 'invalid_request':
                                $error_message = 'Solicitud inválida';
                                break;
                            case 'system_error':
                                $error_message = 'Error del sistema. Intenta de nuevo';
                                if (isset($_GET['debug'])) {
                                    $debug_info = '<br><small>' . htmlspecialchars($_GET['debug']) . '</small>';
                                }
                                break;
                            case 'session_expired':
                                $error_message = 'Tu sesión ha expirado. Por favor inicia sesión nuevamente';
                                break;
                            case 'unauthorized':
                                $error_message = 'Acceso no autorizado';
                                break;
                            default:
                                $error_message = 'Error desconocido';
                        }
                        
                        echo '<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-4">
                                <div class="flex items-center">
                                    <ion-icon name="alert-circle" class="text-red-600 text-xl mr-3"></ion-icon>
                                    <div>
                                        <p class="text-sm text-red-800">' . htmlspecialchars($error_message) . $debug_info . '</p>
                                    </div>
                                </div>
                              </div>';
                    }
                    ?>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-4 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl flex items-center justify-center"
                    >
                        <ion-icon name="log-in" class="mr-2 text-xl"></ion-icon>
                        Iniciar Sesión
                    </button>
                </form>

                <!-- Back to Home -->
                <div class="mt-6 text-center">
                    <a href="../../index.html" class="text-sm text-gray-600 hover:text-red-600 transition duration-200 flex items-center justify-center">
                        <ion-icon name="arrow-back" class="mr-2"></ion-icon>
                        Volver al Inicio
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-200">
                <p class="text-xs text-center text-gray-500">
                    © 2025 PropielEquipo • Panel Administrativo
                </p>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-400 flex items-center justify-center">
                <ion-icon name="lock-closed" class="mr-2"></ion-icon>
                Conexión segura y encriptada
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.name = 'eye-off';
            } else {
                passwordInput.type = 'password';
                toggleIcon.name = 'eye';
            }
        }

        // Animation keyframes for blobs
        const style = document.createElement('style');
        style.textContent = `
            @keyframes blob {
                0% { transform: translate(0px, 0px) scale(1); }
                33% { transform: translate(30px, -50px) scale(1.1); }
                66% { transform: translate(-20px, 20px) scale(0.9); }
                100% { transform: translate(0px, 0px) scale(1); }
            }
            .animate-blob {
                animation: blob 7s infinite;
            }
            .animation-delay-2000 {
                animation-delay: 2s;
            }
            .animation-delay-4000 {
                animation-delay: 4s;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
