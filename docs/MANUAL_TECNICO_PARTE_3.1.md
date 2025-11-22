# MANUAL TÉCNICO - SISTEMA PROPIELEQUIPO
## PARTE 3.1: Estructura de Código Fuente y Módulos Críticos

**Versión:** 1.0  
**Fecha:** Noviembre 2025  
**Continuación de:** MANUAL_TECNICO_PARTE_2.2.md

---

# ÍNDICE DE CONTENIDOS - PARTE 3.1

6. [Estructura del Código Fuente](#6-estructura-del-código-fuente)
   - 6.1 Organización de Directorios
   - 6.2 Convenciones de Nomenclatura
   - 6.3 Estándares de Codificación
7. [Módulos Críticos del Sistema](#7-módulos-críticos-del-sistema)
   - 7.1 Módulo de Autenticación
   - 7.2 Módulo de Gestión de Citas
   - 7.3 Módulo de Gestión de Horarios
   - 7.4 Módulo de Verificación de Pagos

---

# 6. ESTRUCTURA DEL CÓDIGO FUENTE

## 6.1 Organización de Directorios

### 6.1.1 Estructura General del Proyecto

```
PropielEquipo/
│
├── index.html                          # Página de inicio/landing principal
├── package.json                        # Dependencias del proyecto (Tailwind)
├── docker-compose.yml                  # Configuración Docker (desarrollo)
├── schema.sql                          # Schema completo de la BD
├── currentdb.sql                       # Dump actual de la BD
├── README.md                           # Documentación general del proyecto
├── GIT_WORKFLOW.md                     # Guía de trabajo con Git
│
├── docs/                               # 📁 Documentación técnica
│   ├── MANUAL_TECNICO_PARTE_1.md
│   ├── MANUAL_TECNICO_PARTE_2.1.md
│   ├── MANUAL_TECNICO_PARTE_2.2.md
│   ├── MANUAL_TECNICO_PARTE_3.1.md
│   ├── HORARIOS_SISTEMA.md
│   ├── SETUP_HORARIOS.md
│   ├── SCHEDULE_COMPARISON.md
│   ├── nomenclatura_archivos_pdf.md
│   └── *.sql                          # Scripts SQL de migración
│
├── tests/                              # 📁 Tests y datos de prueba
│   ├── README.md
│   ├── generate_test_data.php
│   └── seed_database.sql
│
└── src/                                # 📁 Código fuente principal
    │
    ├── database_connection.php         # Conexión a BD (singleton)
    ├── database_connection.template.php # Template sin credenciales
    ├── database_queries.php            # Clase de consultas reutilizables
    ├── input.css                       # CSS fuente de Tailwind
    ├── output.css                      # CSS compilado de Tailwind
    │
    ├── Landing/                        # 📁 Módulo de páginas públicas
    │   ├── login.html
    │   ├── registrar.html
    │   ├── registrarroot.html          # Registro de admin/doctor
    │   ├── contacto.html
    │   ├── servicios.html
    │   ├── valores.html
    │   └── php_action/
    │       ├── login.php
    │       ├── registrar.php
    │       └── registrarroot.php
    │
    ├── Paciente/                       # 📁 Módulo del Paciente
    │   ├── dashboardpaciente.php       # Dashboard principal
    │   ├── reservar.php                # Reservar nueva cita
    │   ├── Citas.php                   # Ver mis citas
    │   ├── reservas.php                # Historial de reservas
    │   ├── consentimiento.php          # Firmar consentimiento
    │   ├── imagenes_medicas.php        # Gestionar imágenes médicas
    │   ├── subir_comprobante.php       # Subir comprobante de pago
    │   ├── test_api.html               # Testing de endpoints
    │   └── php_action/
    │       ├── reservar.php
    │       ├── cancel_appointment.php
    │       ├── check_availability.php
    │       ├── check_user_appointment.php
    │       ├── get_available_hours.php
    │       ├── upload_comprobante.php
    │       └── ...
    │
    ├── Doctor/                         # 📁 Módulo del Doctor
    │   ├── Citas.php                   # Vista general de citas
    │   ├── Citas_updated.php           # Vista actualizada
    │   ├── especialidades/             # 📁 Por especialidad
    │   │   ├── dermatologia/
    │   │   │   ├── dashboard_dermatologia.php
    │   │   │   ├── pacientes_dermatologia.php
    │   │   │   ├── verificar_pagos_dermatologia.php
    │   │   │   └── Citas_dermatologia.php
    │   │   ├── podologia/
    │   │   │   ├── dashboard_podologia.php
    │   │   │   ├── pacientes_podologia.php
    │   │   │   ├── verificar_pagos_podologia.php
    │   │   │   └── Citas_podologia.php
    │   │   └── tamizaje/
    │   │       ├── dashboard_tamizaje.php
    │   │       ├── pacientes_tamizaje.php
    │   │       ├── verificar_pagos_tamizaje.php
    │   │       └── Citas_tamizaje.php
    │   ├── php_action/
    │   │   ├── logout.php
    │   │   ├── switch_specialty.php
    │   │   ├── update_observations.php
    │   │   └── verificar_pago.php
    │   └── shared/
    │       └── navbar_doctor.php
    │
    ├── Admin/                          # 📁 Módulo del Administrador
    │   ├── dashboard_admin.php
    │   ├── login_admin.php
    │   ├── gestionar_horarios.php      # CRUD de horarios
    │   ├── configuracion_pagos.php     # Config bancaria
    │   ├── README.md
    │   ├── php_action/
    │   │   ├── login_admin.php
    │   │   ├── logout_admin.php
    │   │   ├── get_doctor_schedule.php
    │   │   ├── save_schedule.php
    │   │   ├── toggle_schedule.php
    │   │   ├── delete_schedule.php
    │   │   ├── get_doctor_blocks.php
    │   │   ├── save_block.php
    │   │   ├── delete_block.php
    │   │   └── actualizar_datos_bancarios.php
    │   └── shared/
    │       └── navbar_admin.php
    │
    ├── consentimientos/                # 📁 PDFs de consentimientos
    │   ├── index.php                   # Listado
    │   ├── save_consent.php            # Guardar PDF
    │   └── view_pdf.php                # Visualizar
    │
    ├── comprobantes_pago/              # 📁 Imágenes de transferencias
    │   └── [archivos subidos por pacientes]
    │
    ├── Images/                         # 📁 Imágenes del sistema
    │   ├── secure_image_viewer.php     # Viewer seguro
    │   └── ImgMedicas/                 # Imágenes médicas
    │       ├── index.php
    │       └── [user_id]/              # Carpeta por usuario
    │
    ├── php_action/                     # 📁 Endpoints compartidos
    │   ├── get_patient_history.php     # Dermatología
    │   ├── get_patient_history_podologia.php
    │   ├── get_patient_history_tamizaje.php
    │   ├── get_patient_history_test.php
    │   └── upload_medical_image.php
    │
    └── logs/                           # 📁 Logs del sistema (futuro)
        └── [archivos de log]
```

### 6.1.2 Descripción de Carpetas Principales

| Directorio | Propósito | Acceso |
|------------|-----------|--------|
| **`/src/Landing/`** | Páginas públicas sin autenticación (login, registro, información) | Público |
| **`/src/Paciente/`** | Interfaz completa del paciente (reservas, citas, comprobantes, imágenes) | `rol=3` |
| **`/src/Doctor/`** | Interfaz del doctor, separada por especialidad | `rol=1` |
| **`/src/Admin/`** | Panel administrativo (horarios, configuración) | `rol=4` |
| **`/src/consentimientos/`** | Almacenamiento y gestión de PDFs de consentimiento informado | `rol=1,3` |
| **`/src/comprobantes_pago/`** | Almacenamiento de imágenes de comprobantes bancarios | `rol=1,3` |
| **`/src/Images/ImgMedicas/`** | Almacenamiento de imágenes médicas organizadas por paciente | `rol=1,3` |
| **`/src/php_action/`** | Endpoints/APIs compartidos entre módulos | Varios roles |
| **`/docs/`** | Documentación técnica, manuales, guías SQL | Desarrollo |
| **`/tests/`** | Scripts de testing y datos de prueba | Desarrollo |

---

## 6.2 Convenciones de Nomenclatura

### 6.2.1 Archivos PHP

**Páginas (Vistas):**
- Formato: `nombre_descriptivo.php`
- Ejemplos: `dashboardpaciente.php`, `reservar.php`, `gestionar_horarios.php`
- Convención: lowercase con guiones bajos

**Endpoints/APIs:**
- Formato: `verbo_accion_entidad.php`
- Ejemplos: `get_patient_history.php`, `save_schedule.php`, `update_observations.php`
- Convención: verbo + sustantivo en inglés, lowercase con guiones bajos

**Clases:**
- Formato: `PascalCase`
- Ejemplos: `DatabaseQueries`, `AppointmentManager`
- Convención: Primera letra de cada palabra en mayúscula

### 6.2.2 Archivos HTML

- Formato: `nombre.html`
- Ejemplos: `login.html`, `registrar.html`, `contacto.html`
- Convención: lowercase sin guiones bajos

### 6.2.3 Variables PHP

**Variables locales:**
```php
$user_id            // Snake case para variables simples
$appointment_data   // Snake case
$is_active          // Booleanos con prefijo is_/has_
```

**Constantes:**
```php
define('DB_HOST', 'localhost');     // SCREAMING_SNAKE_CASE
define('MAX_FILE_SIZE', 10485760);  // Constantes en mayúsculas
```

**Variables de sesión:**
```php
$_SESSION['user_id']        // Snake case
$_SESSION['admin_id']       // Prefijo según contexto
$_SESSION['telefono']       // Nombres descriptivos
$_SESSION['rol']            // Coherente en todo el sistema
```

### 6.2.4 Base de Datos

**Tablas:**
- Formato: `nombre_plural` o `nombre_descriptivo`
- Ejemplos: `usuarios`, `citas`, `horarios`, `bloqueos_horarios`
- Convención: lowercase, snake_case, plural preferido

**Columnas:**
- Formato: `nombre_columna`
- Ejemplos: `user_id`, `fecha_creacion`, `activo`
- Convención: lowercase, snake_case

**Foreign Keys:**
- Formato: `fk_tabla_referencia`
- Ejemplos: `fk_cita_usuario`, `fk_horarios_doctor`
- Convención: Prefijo `fk_` + tabla + referencia

**Índices:**
- Formato: `idx_campo` o `idx_campo1_campo2`
- Ejemplos: `idx_telefono`, `idx_fecha_horario`
- Convención: Prefijo `idx_` + campo(s) indexado(s)

---

## 6.3 Estándares de Codificación

### 6.3.1 PHP

**Estructura básica de archivo:**

```php
<?php
// 1. Definición de sesión (si aplica)
session_start();

// 2. Includes/Requires
require_once '../database_connection.php';
require_once '../database_queries.php';

// 3. Validación de autenticación
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Landing/login.html");
    exit();
}

// 4. Validación de rol
if ($_SESSION['rol'] != 3) { // Paciente
    header("Location: ../Landing/login.html");
    exit();
}

// 5. Lógica de negocio
$user_id = $_SESSION['user_id'];
$db = new DatabaseQueries($conex);

// 6. Procesamiento de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Manejar POST
}

// 7. Consultas a BD
$appointments = $db->getAppointmentsByUser($user_id);

// 8. HTML/Vista (si aplica)
?>
<!DOCTYPE html>
<html lang="es">
...
</html>
```

**Prepared Statements (Obligatorio):**

```php
// ✅ CORRECTO - Prepared statements con PDO
$stmt = $conex->prepare("SELECT * FROM usuarios WHERE telefono = ?");
$stmt->bind_param("s", $telefono);
$stmt->execute();
$result = $stmt->get_result();

// ❌ INCORRECTO - Concatenación directa (vulnerable a SQL injection)
$query = "SELECT * FROM usuarios WHERE telefono = '$telefono'";
$result = mysqli_query($conex, $query);
```

**Manejo de errores:**

```php
try {
    $stmt = $conex->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conex->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
} catch (Exception $e) {
    error_log("Error en reservar.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error procesando solicitud'
    ]);
    exit();
}
```

**Validación de inputs:**

```php
// Sanitización
$telefono = trim($_POST['telefono']);
$telefono = filter_var($telefono, FILTER_SANITIZE_STRING);

// Validación
if (empty($telefono)) {
    $errors[] = "El teléfono es obligatorio";
}

if (!preg_match('/^[0-9]{10,15}$/', $telefono)) {
    $errors[] = "Formato de teléfono inválido";
}

// Validación de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email inválido";
}
```

### 6.3.2 JavaScript

**Estructura de funciones:**

```javascript
// Función para reservar cita
async function reservarCita(doctorId, fecha, horario) {
    try {
        const response = await fetch('php_action/reservar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                doctor_id: doctorId,
                fecha: fecha,
                horario: horario
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje('Cita reservada exitosamente', 'success');
            window.location.href = 'subir_comprobante.php';
        } else {
            mostrarMensaje(data.message, 'error');
        }
        
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión', 'error');
    }
}
```

**Convenciones:**
- `camelCase` para variables y funciones
- Async/await para operaciones asíncronas
- Try-catch para manejo de errores
- Comentarios descriptivos

### 6.3.3 HTML/CSS (Tailwind)

**Estructura HTML:**

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PropielEquipo - Título de página</title>
    
    <!-- Tailwind CSS -->
    <link href="../output.css" rel="stylesheet">
    
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    
    <!-- Scripts adicionales -->
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <?php include 'shared/navbar_paciente.php'; ?>
    
    <!-- Contenido principal -->
    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">
            Título de Página
        </h1>
        
        <!-- Contenido -->
    </main>
    
    <!-- Footer (opcional) -->
    
    <!-- Scripts al final -->
    <script src="js/main.js"></script>
</body>
</html>
```

**Clases Tailwind - Patrones comunes:**

```html
<!-- Botón primario -->
<button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
    Reservar Cita
</button>

<!-- Card -->
<div class="bg-white rounded-lg shadow-md p-6 mb-4">
    <h3 class="text-xl font-semibold mb-2">Título</h3>
    <p class="text-gray-600">Contenido</p>
</div>

<!-- Input de formulario -->
<input type="text" 
       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
       placeholder="Ingrese texto">

<!-- Badge de estado -->
<span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
    Completada
</span>
```

---

# 7. MÓDULOS CRÍTICOS DEL SISTEMA

## 7.1 Módulo de Autenticación

### 7.1.1 Componentes

**Archivos principales:**
- `src/Landing/login.html` - Formulario de login
- `src/Landing/php_action/login.php` - Procesamiento de login
- `src/Landing/registrar.html` - Formulario de registro
- `src/Landing/php_action/registrar.php` - Procesamiento de registro
- `src/Doctor/php_action/logout.php` - Cerrar sesión

### 7.1.2 Código Crítico: Login

**Archivo:** `src/Landing/php_action/login.php`

```php
<?php
session_start();
require_once '../../database_connection.php';

// Obtener credenciales
$telefono = trim($_POST['telefono']);
$password = $_POST['password'];

// Validaciones básicas
if (empty($telefono) || empty($password)) {
    header("Location: ../login.html?error=empty");
    exit();
}

// Buscar usuario por teléfono
$stmt = $conex->prepare("SELECT * FROM usuarios WHERE telefono = ?");
$stmt->bind_param("s", $telefono);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Usuario no encontrado
    header("Location: ../login.html?error=invalid");
    exit();
}

$user = $result->fetch_assoc();

// Verificar contraseña hasheada
if (!password_verify($password, $user['password'])) {
    // Contraseña incorrecta
    header("Location: ../login.html?error=invalid");
    exit();
}

// Validar rol (solo pacientes por este login)
if ($user['rol'] != 3) {
    header("Location: ../login.html?error=rol");
    exit();
}

// Crear sesión
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['telefono'] = $user['telefono'];
$_SESSION['rol'] = $user['rol'];
$_SESSION['nombre'] = $user['nombre'];
$_SESSION['apellido'] = $user['apellido'];

// Actualizar último acceso
$update_stmt = $conex->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE user_id = ?");
$update_stmt->bind_param("i", $user['user_id']);
$update_stmt->execute();

// Redirigir según rol
switch ($user['rol']) {
    case 1: // Doctor
        header("Location: ../../Doctor/Citas.php");
        break;
    case 3: // Paciente
        header("Location: ../../Paciente/dashboardpaciente.php");
        break;
    case 4: // Admin
        header("Location: ../../Admin/dashboard_admin.php");
        break;
    default:
        header("Location: ../login.html?error=rol");
}

exit();
?>
```

**Puntos críticos:**
1. **Sanitización:** `trim()` elimina espacios
2. **Prepared statements:** Protección contra SQL injection
3. **Password hashing:** Uso de `password_verify()` con bcrypt
4. **Validación de rol:** Evita acceso no autorizado
5. **Variables de sesión:** Consistentes en todo el sistema
6. **Último acceso:** Registro de actividad

### 7.1.3 Código Crítico: Registro

**Archivo:** `src/Landing/php_action/registrar.php`

```php
<?php
session_start();
require_once '../../database_connection.php';

// Obtener datos del formulario
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$telefono = trim($_POST['telefono']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$edad = intval($_POST['edad']);
$id_genero = intval($_POST['id_genero']);

// Validaciones
$errors = [];

if (empty($nombre) || empty($apellido) || empty($telefono) || empty($password)) {
    $errors[] = "Todos los campos obligatorios deben ser llenados";
}

if ($password !== $confirm_password) {
    $errors[] = "Las contraseñas no coinciden";
}

if (strlen($password) < 6) {
    $errors[] = "La contraseña debe tener al menos 6 caracteres";
}

if ($edad < 18 || $edad > 120) {
    $errors[] = "Edad inválida";
}

// Validar teléfono único
$stmt = $conex->prepare("SELECT user_id FROM usuarios WHERE telefono = ?");
$stmt->bind_param("s", $telefono);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $errors[] = "Este teléfono ya está registrado";
}

// Validar email único (si se proporcionó)
if (!empty($email)) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }
    
    $stmt = $conex->prepare("SELECT user_id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Este email ya está registrado";
    }
}

// Si hay errores, redirigir
if (!empty($errors)) {
    $_SESSION['registro_errors'] = $errors;
    header("Location: ../registrar.html");
    exit();
}

// Hashear contraseña
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insertar usuario
$stmt = $conex->prepare(
    "INSERT INTO usuarios (nombre, apellido, telefono, email, password, edad, id_genero, rol) 
     VALUES (?, ?, ?, ?, ?, ?, ?, 3)"
);

$stmt->bind_param(
    "sssssii",
    $nombre,
    $apellido,
    $telefono,
    $email,
    $password_hash,
    $edad,
    $id_genero
);

if ($stmt->execute()) {
    // Crear sesión automática
    $new_user_id = $stmt->insert_id;
    
    $_SESSION['user_id'] = $new_user_id;
    $_SESSION['telefono'] = $telefono;
    $_SESSION['rol'] = 3; // Paciente
    $_SESSION['nombre'] = $nombre;
    $_SESSION['apellido'] = $apellido;
    
    // Redirigir a dashboard
    header("Location: ../../Paciente/dashboardpaciente.php");
} else {
    $_SESSION['registro_errors'] = ["Error al crear cuenta. Intente nuevamente."];
    header("Location: ../registrar.html");
}

exit();
?>
```

**Puntos críticos:**
1. **Validación exhaustiva:** Campos obligatorios, formato, unicidad
2. **Password hashing:** `password_hash()` con bcrypt (cost=10 por defecto)
3. **Sanitización:** `trim()`, `intval()`, `filter_var()`
4. **Unicidad:** Verificación de teléfono y email antes de insertar
5. **Sesión automática:** Login inmediato tras registro exitoso
6. **Rol por defecto:** Siempre 3 (Paciente) para registros públicos

### 7.1.4 Validación de Sesión (Pattern usado en todo el sistema)

```php
<?php
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Landing/login.html");
    exit();
}

// Verificar rol específico (ejemplo: solo pacientes)
if ($_SESSION['rol'] != 3) {
    header("Location: ../Landing/login.html");
    exit();
}

// IMPORTANTE: Diferentes módulos usan diferentes variables de sesión
// Paciente/Doctor: $_SESSION['user_id'], $_SESSION['telefono'], $_SESSION['rol']
// Admin: $_SESSION['admin_id'], $_SESSION['admin_rol']
?>
```

---

## 7.2 Módulo de Gestión de Citas

### 7.2.1 Componentes

**Archivos principales:**
- `src/Paciente/reservar.php` - Interfaz de reserva
- `src/Paciente/php_action/get_available_hours.php` - Calcular disponibilidad
- `src/Paciente/php_action/check_availability.php` - Validar slot
- `src/Paciente/php_action/reservar.php` - Crear cita
- `src/Paciente/php_action/cancel_appointment.php` - Cancelar cita

### 7.2.2 Código Crítico: Calcular Horarios Disponibles

**Archivo:** `src/Paciente/php_action/get_available_hours.php`

```php
<?php
session_start();
header('Content-Type: application/json');
require_once '../../database_connection.php';

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Obtener parámetros
$doctor_id = intval($_GET['doctor_id']);
$fecha = $_GET['fecha']; // Formato: YYYY-MM-DD

// Validar fecha (no pasada, no más de 3 meses adelante)
$fecha_obj = new DateTime($fecha);
$hoy = new DateTime();
$max_fecha = (new DateTime())->modify('+3 months');

if ($fecha_obj < $hoy) {
    echo json_encode(['success' => false, 'message' => 'No se pueden reservar citas en fechas pasadas']);
    exit();
}

if ($fecha_obj > $max_fecha) {
    echo json_encode(['success' => false, 'message' => 'Solo se pueden reservar citas hasta 3 meses adelante']);
    exit();
}

// Obtener día de la semana en español
$dias_semana = [
    'Monday' => 'lunes',
    'Tuesday' => 'martes',
    'Wednesday' => 'miércoles',
    'Thursday' => 'jueves',
    'Friday' => 'viernes',
    'Saturday' => 'sábado',
    'Sunday' => 'domingo'
];

$dia_ingles = $fecha_obj->format('l');
$dia_semana = $dias_semana[$dia_ingles];

// 1. Consultar horarios del doctor para ese día
$stmt = $conex->prepare(
    "SELECT hora_inicio, hora_fin, intervalo_minutos 
     FROM horarios 
     WHERE id_doctor = ? AND dia_semana = ? AND activo = 1"
);
$stmt->bind_param("is", $doctor_id, $dia_semana);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'El doctor no tiene horarios configurados para este día']);
    exit();
}

$horario = $result->fetch_assoc();
$hora_inicio = new DateTime($horario['hora_inicio']);
$hora_fin = new DateTime($horario['hora_fin']);
$intervalo = intval($horario['intervalo_minutos']);

// 2. Verificar si la fecha está bloqueada
$stmt = $conex->prepare(
    "SELECT id_bloqueo 
     FROM bloqueos_horarios 
     WHERE id_doctor = ? 
       AND ? BETWEEN fecha_inicio AND fecha_fin 
       AND activo = 1"
);
$stmt->bind_param("is", $doctor_id, $fecha);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El doctor no está disponible en esta fecha']);
    exit();
}

// 3. Generar todos los slots posibles
$slots_disponibles = [];
$hora_actual = clone $hora_inicio;

while ($hora_actual < $hora_fin) {
    $slots_disponibles[] = $hora_actual->format('H:i:s');
    $hora_actual->modify("+{$intervalo} minutes");
}

// 4. Consultar citas ya reservadas para esa fecha
$stmt = $conex->prepare(
    "SELECT horario 
     FROM citas 
     WHERE id_doctor = ? 
       AND fecha = ? 
       AND estado NOT IN ('cancelada', 'rechazada')"
);
$stmt->bind_param("is", $doctor_id, $fecha);
$stmt->execute();
$result = $stmt->get_result();

$slots_ocupados = [];
while ($row = $result->fetch_assoc()) {
    $slots_ocupados[] = $row['horario'];
}

// 5. Filtrar slots ocupados
$slots_finales = array_diff($slots_disponibles, $slots_ocupados);

// 6. Si es hoy, filtrar horas pasadas
if ($fecha === $hoy->format('Y-m-d')) {
    $hora_actual_hoy = new DateTime();
    $slots_finales = array_filter($slots_finales, function($slot) use ($hora_actual_hoy) {
        $slot_time = DateTime::createFromFormat('H:i:s', $slot);
        return $slot_time > $hora_actual_hoy;
    });
}

// Retornar slots disponibles
echo json_encode([
    'success' => true,
    'slots' => array_values($slots_finales),
    'fecha' => $fecha,
    'dia_semana' => $dia_semana
]);
?>
```

**Algoritmo de disponibilidad:**

1. **Validar fecha:** No pasada, no más de 3 meses adelante
2. **Obtener día de semana:** Convertir de inglés a español
3. **Consultar horarios:** Buscar en tabla `horarios` por doctor + día
4. **Verificar bloqueos:** Consultar tabla `bloqueos_horarios`
5. **Generar slots:** Crear array desde hora_inicio hasta hora_fin con intervalos
6. **Consultar citas:** Obtener slots ya ocupados
7. **Filtrar:** Eliminar slots ocupados del array de disponibles
8. **Filtrar horas pasadas:** Si es hoy, eliminar slots anteriores a la hora actual

### 7.2.3 Código Crítico: Crear Cita

**Archivo:** `src/Paciente/php_action/reservar.php`

```php
<?php
session_start();
header('Content-Type: application/json');
require_once '../../database_connection.php';

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Obtener datos
$user_id = $_SESSION['user_id'];
$doctor_id = intval($_POST['doctor_id']);
$fecha = $_POST['fecha'];
$horario = $_POST['horario'];
$servicio = $_POST['servicio']; // dermatología, podología, tamiz

// Validaciones
if (empty($doctor_id) || empty($fecha) || empty($horario) || empty($servicio)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

// CRITICAL: Verificar nuevamente disponibilidad (race condition check)
$stmt = $conex->prepare(
    "SELECT id_cita 
     FROM citas 
     WHERE id_doctor = ? 
       AND fecha = ? 
       AND horario = ? 
       AND estado NOT IN ('cancelada', 'rechazada')"
);
$stmt->bind_param("iss", $doctor_id, $fecha, $horario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Este horario acaba de ser reservado. Por favor elija otro.']);
    exit();
}

// Verificar que el usuario no tenga más de 3 citas pendientes
$stmt = $conex->prepare(
    "SELECT COUNT(*) as count 
     FROM citas 
     WHERE id_usuario = ? 
       AND estado IN ('pendiente', 'pendiente_pago')"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] >= 3) {
    echo json_encode(['success' => false, 'message' => 'No puede tener más de 3 citas pendientes']);
    exit();
}

// Insertar cita
$monto = 500.00; // Precio fijo (puede ser dinámico según servicio)
$stmt = $conex->prepare(
    "INSERT INTO citas (id_usuario, id_doctor, fecha, horario, servicio, estado, monto, requiere_pago) 
     VALUES (?, ?, ?, ?, ?, 'pendiente_pago', ?, 1)"
);
$stmt->bind_param("iisssd", $user_id, $doctor_id, $fecha, $horario, $servicio, $monto);

if ($stmt->execute()) {
    $cita_id = $stmt->insert_id;
    
    echo json_encode([
        'success' => true,
        'message' => 'Cita reservada exitosamente',
        'cita_id' => $cita_id,
        'redirect' => '../subir_comprobante.php?cita=' . $cita_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al reservar cita: ' . $conex->error]);
}
?>
```

**Puntos críticos:**
1. **Race condition check:** Verificar disponibilidad justo antes de insertar
2. **Límite de citas:** Máximo 3 citas pendientes por usuario
3. **Estado inicial:** Siempre `pendiente_pago`
4. **Transacción implícita:** INSERT atómico garantiza consistencia
5. **Retorno de ID:** Devolver `cita_id` para siguiente paso (comprobante)

---

## 7.3 Módulo de Gestión de Horarios

### 7.3.1 Componentes

**Archivos principales:**
- `src/Admin/gestionar_horarios.php` - Interfaz de gestión
- `src/Admin/php_action/get_doctor_schedule.php` - Obtener horarios
- `src/Admin/php_action/save_schedule.php` - Crear/actualizar horario
- `src/Admin/php_action/toggle_schedule.php` - Activar/desactivar
- `src/Admin/php_action/delete_schedule.php` - Eliminar horario
- `src/Admin/php_action/save_block.php` - Crear bloqueo
- `src/Admin/php_action/delete_block.php` - Eliminar bloqueo

### 7.3.2 Código Crítico: Guardar Horario

**Archivo:** `src/Admin/php_action/save_schedule.php`

```php
<?php
session_start();
header('Content-Type: application/json');
require_once '../../database_connection.php';

// Validar sesión de admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_rol'] != 4) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Obtener datos
$doctor_id = intval($_POST['doctor_id']);
$dia_semana = $_POST['dia_semana'];
$hora_inicio = $_POST['hora_inicio'];
$hora_fin = $_POST['hora_fin'];
$intervalo_minutos = intval($_POST['intervalo_minutos']);
$aplicar_todos = isset($_POST['aplicar_todos']) && $_POST['aplicar_todos'] === 'true';

// Validaciones
if (empty($doctor_id) || empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

// Validar que hora_fin > hora_inicio
$inicio = new DateTime($hora_inicio);
$fin = new DateTime($hora_fin);

if ($fin <= $inicio) {
    echo json_encode(['success' => false, 'message' => 'La hora de fin debe ser posterior a la hora de inicio']);
    exit();
}

// Validar intervalo
$intervalos_validos = [30, 45, 60, 90, 120];
if (!in_array($intervalo_minutos, $intervalos_validos)) {
    echo json_encode(['success' => false, 'message' => 'Intervalo inválido']);
    exit();
}

// Determinar qué días procesar
$dias_procesar = [];
if ($aplicar_todos) {
    $dias_procesar = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];
} else {
    $dias_procesar = [$dia_semana];
}

// Procesar cada día
$conex->begin_transaction();

try {
    foreach ($dias_procesar as $dia) {
        // Usar INSERT ... ON DUPLICATE KEY UPDATE (upsert)
        $stmt = $conex->prepare(
            "INSERT INTO horarios (id_doctor, dia_semana, hora_inicio, hora_fin, intervalo_minutos, activo) 
             VALUES (?, ?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE 
                hora_inicio = VALUES(hora_inicio),
                hora_fin = VALUES(hora_fin),
                intervalo_minutos = VALUES(intervalo_minutos),
                activo = 1,
                actualizado = NOW()"
        );
        
        $stmt->bind_param("isssi", $doctor_id, $dia, $hora_inicio, $hora_fin, $intervalo_minutos);
        $stmt->execute();
    }
    
    $conex->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Horario(s) guardado(s) exitosamente',
        'dias_procesados' => count($dias_procesar)
    ]);
    
} catch (Exception $e) {
    $conex->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
}
?>
```

**Puntos críticos:**
1. **Validación de admin:** Solo `rol=4` puede gestionar horarios
2. **Upsert pattern:** `INSERT ... ON DUPLICATE KEY UPDATE`
3. **Transacción:** `BEGIN TRANSACTION` + `COMMIT`/`ROLLBACK`
4. **Aplicar a toda semana:** Loop sobre lunes-viernes
5. **Validación de lógica:** hora_fin > hora_inicio
6. **Intervalos permitidos:** Lista blanca de valores válidos

---

## 7.4 Módulo de Verificación de Pagos

### 7.4.1 Componentes

**Archivos principales:**
- `src/Doctor/especialidades/*/verificar_pagos_*.php` - Interfaz por especialidad
- `src/Doctor/php_action/verificar_pago.php` - Aprobar/rechazar comprobante
- `src/Paciente/subir_comprobante.php` - Subir imagen de comprobante
- `src/Paciente/php_action/upload_comprobante.php` - Procesar upload

### 7.4.2 Código Crítico: Verificar Pago

**Archivo:** `src/Doctor/php_action/verificar_pago.php`

```php
<?php
session_start();
header('Content-Type: application/json');
require_once '../../database_connection.php';

// Validar sesión de doctor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$doctor_id = $_SESSION['user_id'];
$cita_id = intval($_POST['cita_id']);
$accion = $_POST['accion']; // 'aprobar' o 'rechazar'
$notas = isset($_POST['notas']) ? trim($_POST['notas']) : '';

// Validar que la cita pertenezca al doctor
$stmt = $conex->prepare(
    "SELECT id_cita, id_usuario, estado 
     FROM citas 
     WHERE id_cita = ? AND id_doctor = ?"
);
$stmt->bind_param("ii", $cita_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Cita no encontrada o no autorizado']);
    exit();
}

$cita = $result->fetch_assoc();

// Validar que esté en estado pendiente_pago
if ($cita['estado'] !== 'pendiente_pago') {
    echo json_encode(['success' => false, 'message' => 'Esta cita ya fue procesada']);
    exit();
}

// Procesar según acción
if ($accion === 'aprobar') {
    $nuevo_estado = 'pendiente'; // Cita confirmada, esperando atención
    
    $stmt = $conex->prepare(
        "UPDATE citas 
         SET estado = ?,
             verificado_por = ?,
             fecha_verificacion = NOW(),
             notas_pago = ?
         WHERE id_cita = ?"
    );
    $stmt->bind_param("sisi", $nuevo_estado, $doctor_id, $notas, $cita_id);
    
    if ($stmt->execute()) {
        // TODO: Enviar notificación al paciente (email/SMS)
        
        echo json_encode([
            'success' => true,
            'message' => 'Pago verificado correctamente',
            'nuevo_estado' => $nuevo_estado
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $conex->error]);
    }
    
} elseif ($accion === 'rechazar') {
    $nuevo_estado = 'rechazada';
    
    if (empty($notas)) {
        echo json_encode(['success' => false, 'message' => 'Debe proporcionar un motivo de rechazo']);
        exit();
    }
    
    $stmt = $conex->prepare(
        "UPDATE citas 
         SET estado = ?,
             verificado_por = ?,
             fecha_verificacion = NOW(),
             notas_pago = ?
         WHERE id_cita = ?"
    );
    $stmt->bind_param("sisi", $nuevo_estado, $doctor_id, $notas, $cita_id);
    
    if ($stmt->execute()) {
        // TODO: Notificar al paciente con el motivo
        
        echo json_encode([
            'success' => true,
            'message' => 'Pago rechazado',
            'nuevo_estado' => $nuevo_estado
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $conex->error]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Acción inválida']);
}
?>
```

**Flujo de estados de cita:**
```
pendiente_pago → [aprobar] → pendiente → completada
                ↓
             [rechazar]
                ↓
             rechazada
```

**Puntos críticos:**
1. **Autorización:** Solo el doctor dueño de la cita puede verificar
2. **Validación de estado:** Solo se pueden procesar citas en `pendiente_pago`
3. **Registro de verificador:** Se guarda quién aprobó/rechazó
4. **Timestamp:** `fecha_verificacion` registra cuándo se procesó
5. **Notas obligatorias:** Al rechazar, debe proporcionar motivo
6. **Idempotencia:** No se puede procesar dos veces la misma cita

---

**FIN DE PARTE 3.1**

---

*Este documento continúa en:*
- **MANUAL_TECNICO_PARTE_3.2.md** (Módulos de Imágenes Médicas, Consentimientos, Testing y Seguridad)

---

**Elaborado por:** Equipo de Desarrollo PropielEquipo  
**Revisión:** v1.0 - Noviembre 2025  
**Contacto Técnico:** desarrollo@propielequipo.com
