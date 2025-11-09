<?php
/**
 * Protección de Imágenes Médicas - PropielEquipo
 * Este archivo protege el acceso no autorizado a imágenes médicas sensibles
 * Información Médica Protegida (PHI) - Acceso Restringido
 */

// Enviar código de respuesta 403 (Prohibido)
http_response_code(403);

// Cabeceras de seguridad adicionales
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Log del intento de acceso no autorizado
$log_entry = date('Y-m-d H:i:s') . " - Unauthorized access attempt to medical images from IP: " . 
             ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . 
             " - User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . 
             " - Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown') . PHP_EOL;

error_log($log_entry, 3, __DIR__ . '/../../logs/security.log');

// Mensaje de error
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - PropielEquipo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        .container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }
        .icon {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }
        p {
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: transform 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <h1>Acceso Denegado</h1>
        <p>
            <strong>Esta es un área restringida que contiene información médica protegida.</strong>
        </p>
        <p>
            El acceso a estas imágenes médicas está limitado únicamente a personal autorizado 
            a través del sistema de autenticación de PropielEquipo.
        </p>
        
        <div class="warning">
            <strong>⚠️ Aviso de Seguridad:</strong><br>
            Esta área contiene Información Médica Protegida (PHI) regulada por las normas 
            de privacidad médica. El acceso no autorizado está prohibido.
        </div>
        
        <div style="margin-top: 2rem;">
            <a href="../../Landing/login.html" class="btn">Iniciar Sesión</a>
        </div>
        
        <p style="margin-top: 2rem; font-size: 0.8rem; color: #bdc3c7;">
            PropielEquipo Medical System v1.0 - Sistema de Gestión Médica Seguro
        </p>
    </div>
</body>
</html>
<?php
exit();
?>
