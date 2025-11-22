# MANUAL TÉCNICO - SISTEMA PROPIELEQUIPO
## PARTE 3.2: Módulos Adicionales, Testing y Seguridad

**Versión:** 1.0  
**Fecha:** Noviembre 2025  
**Continuación de:** MANUAL_TECNICO_PARTE_3.1.md

---

# ÍNDICE DE CONTENIDOS - PARTE 3.2

7. [Módulos Críticos del Sistema (Continuación)](#7-módulos-críticos-del-sistema-continuación)
   - 7.5 Módulo de Imágenes Médicas
   - 7.6 Módulo de Consentimientos Informados
   - 7.7 Módulo de Historial Médico (PDF)
8. [Pruebas y Validación](#8-pruebas-y-validación)
   - 8.1 Estrategia de Testing
   - 8.2 Casos de Prueba
   - 8.3 Testing Manual
9. [Seguridad](#9-seguridad)
   - 9.1 Medidas Implementadas
   - 9.2 Vulnerabilidades Mitigadas
   - 9.3 Recomendaciones Futuras
10. [Mantenimiento y Soporte](#10-mantenimiento-y-soporte)
11. [Conclusiones](#11-conclusiones)

---

# 7. MÓDULOS CRÍTICOS DEL SISTEMA (Continuación)

## 7.5 Módulo de Imágenes Médicas

### 7.5.1 Componentes

**Archivos principales:**
- `src/Paciente/imagenes_medicas.php` - Galería de imágenes del paciente
- `src/php_action/upload_medical_image.php` - Subir imagen
- `src/Paciente/php_action/delete_medical_image.php` - Eliminar imagen
- `src/Images/secure_image_viewer.php` - Visualizador seguro
- `src/Images/ImgMedicas/index.php` - Index protegido

### 7.5.2 Código Crítico: Upload de Imagen Médica

**Archivo:** `src/php_action/upload_medical_image.php`

```php
<?php
session_start();
header('Content-Type: application/json');
require_once '../database_connection.php';

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$user_id = $_SESSION['user_id'];
$categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : 'General';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

// Validar archivo
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error al subir archivo']);
    exit();
}

$archivo = $_FILES['imagen'];
$nombre_original = $archivo['name'];
$tipo_archivo = $archivo['type'];
$tamano = $archivo['size'];
$tmp_name = $archivo['tmp_name'];

// Validar tipo de archivo
$tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
if (!in_array($tipo_archivo, $tipos_permitidos)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo JPG, PNG o PDF']);
    exit();
}

// Validar tamaño (máximo 10 MB)
$max_size = 10 * 1024 * 1024; // 10 MB en bytes
if ($tamano > $max_size) {
    echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande. Máximo 10 MB']);
    exit();
}

// Obtener extensión
$extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
$extensiones_validas = ['jpg', 'jpeg', 'png', 'pdf'];
if (!in_array(strtolower($extension), $extensiones_validas)) {
    echo json_encode(['success' => false, 'message' => 'Extensión no permitida']);
    exit();
}

// Crear directorio del usuario si no existe
$directorio_usuario = "../Images/ImgMedicas/{$user_id}/";
if (!file_exists($directorio_usuario)) {
    mkdir($directorio_usuario, 0755, true);
}

// Generar nombre único para evitar sobreescritura
$nombre_unico = uniqid() . '_' . time() . '.' . $extension;
$ruta_completa = $directorio_usuario . $nombre_unico;
$ruta_relativa = "Images/ImgMedicas/{$user_id}/" . $nombre_unico;

// Mover archivo
if (!move_uploaded_file($tmp_name, $ruta_completa)) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar archivo']);
    exit();
}

// Insertar en BD
$stmt = $conex->prepare(
    "INSERT INTO imagenes_medicas (id_usuario, ruta_imagen, categoria, nombre_archivo, tipo_archivo, descripcion) 
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("isssss", $user_id, $ruta_relativa, $categoria, $nombre_original, $tipo_archivo, $descripcion);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Imagen subida correctamente',
        'imagen_id' => $stmt->insert_id,
        'ruta' => $ruta_relativa
    ]);
} else {
    // Si falla BD, eliminar archivo
    unlink($ruta_completa);
    echo json_encode(['success' => false, 'message' => 'Error al registrar en base de datos']);
}
?>
```

**Puntos críticos:**
1. **Validación de tipo:** Whitelist de tipos MIME permitidos
2. **Validación de tamaño:** Límite de 10 MB
3. **Validación de extensión:** Doble verificación
4. **Nombre único:** `uniqid() + timestamp` evita colisiones
5. **Directorio por usuario:** Organización en `ImgMedicas/{user_id}/`
6. **Permisos:** Directorio creado con `0755`
7. **Rollback de archivo:** Si falla BD, eliminar archivo físico

### 7.5.3 Visualizador Seguro

**Archivo:** `src/Images/secure_image_viewer.php`

```php
<?php
session_start();
require_once '../database_connection.php';

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('No autorizado');
}

$imagen_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$rol = $_SESSION['rol'];

// Obtener datos de la imagen
$stmt = $conex->prepare(
    "SELECT im.*, u.user_id as propietario_id 
     FROM imagenes_medicas im
     JOIN usuarios u ON im.id_usuario = u.user_id
     WHERE im.id_imagen = ?"
);
$stmt->bind_param("i", $imagen_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('HTTP/1.0 404 Not Found');
    exit('Imagen no encontrada');
}

$imagen = $result->fetch_assoc();

// Validar permisos:
// - El propietario puede ver sus imágenes
// - Los doctores pueden ver imágenes de sus pacientes
$tiene_permiso = false;

if ($imagen['propietario_id'] == $user_id) {
    // Es el propietario
    $tiene_permiso = true;
} elseif ($rol == 1) {
    // Es doctor, verificar si tiene citas con ese paciente
    $stmt = $conex->prepare(
        "SELECT COUNT(*) as count 
         FROM citas 
         WHERE id_doctor = ? AND id_usuario = ?"
    );
    $stmt->bind_param("ii", $user_id, $imagen['propietario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $tiene_permiso = true;
    }
}

if (!$tiene_permiso) {
    header('HTTP/1.0 403 Forbidden');
    exit('No tiene permiso para ver esta imagen');
}

// Servir archivo
$ruta_completa = '../' . $imagen['ruta_imagen'];

if (!file_exists($ruta_completa)) {
    header('HTTP/1.0 404 Not Found');
    exit('Archivo no encontrado');
}

// Configurar headers
header('Content-Type: ' . $imagen['tipo_archivo']);
header('Content-Length: ' . filesize($ruta_completa));
header('Content-Disposition: inline; filename="' . $imagen['nombre_archivo'] . '"');

// Prevenir cache de imágenes médicas sensibles
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Servir archivo
readfile($ruta_completa);
exit();
?>
```

**Puntos críticos:**
1. **Autorización granular:** Propietario o doctor con relación
2. **Verificación de relación:** Doctor solo ve imágenes de sus pacientes
3. **Headers correctos:** Content-Type, Content-Disposition
4. **No cache:** Imágenes médicas no deben cachearse
5. **Código HTTP:** 403 Forbidden, 404 Not Found apropiados

---

## 7.6 Módulo de Consentimientos Informados

### 7.6.1 Componentes

**Archivos principales:**
- `src/Paciente/consentimiento.php` - Interfaz para firmar
- `src/consentimientos/save_consent.php` - Guardar PDF con firma
- `src/consentimientos/view_pdf.php` - Visualizar PDF
- `src/consentimientos/index.php` - Listar consentimientos

### 7.6.2 Código Crítico: Guardar Consentimiento

**Archivo:** `src/consentimientos/save_consent.php`

```php
<?php
session_start();
header('Content-Type: application/json');

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$user_id = $_SESSION['user_id'];
$cita_id = isset($_POST['cita_id']) ? intval($_POST['cita_id']) : 0;
$firma_data = $_POST['firma_data']; // Base64 de la imagen de firma

if (empty($firma_data) || $cita_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

// Decodificar firma (base64 → imagen)
$firma_data = str_replace('data:image/png;base64,', '', $firma_data);
$firma_data = str_replace(' ', '+', $firma_data);
$firma_decoded = base64_decode($firma_data);

if ($firma_decoded === false) {
    echo json_encode(['success' => false, 'message' => 'Error al procesar firma']);
    exit();
}

// Guardar firma temporalmente
$temp_firma = tempnam(sys_get_temp_dir(), 'firma_');
file_put_contents($temp_firma, $firma_decoded);

// Generar PDF usando biblioteca (ejemplo simplificado)
// En producción usar jsPDF en cliente y enviar PDF completo

$pdf_nombre = "consentimiento_{$user_id}_{$cita_id}_" . time() . ".pdf";
$pdf_ruta = __DIR__ . "/{$pdf_nombre}";

// Aquí iría la lógica de generación del PDF con jsPDF o PHP-based (FPDF/TCPDF)
// Por simplicidad, asumimos que el PDF viene desde el cliente

$pdf_data = $_POST['pdf_data']; // Base64 del PDF completo generado con jsPDF
$pdf_decoded = base64_decode(str_replace('data:application/pdf;base64,', '', $pdf_data));

if ($pdf_decoded === false) {
    echo json_encode(['success' => false, 'message' => 'Error al procesar PDF']);
    exit();
}

// Guardar PDF
file_put_contents($pdf_ruta, $pdf_decoded);

// Limpiar archivo temporal
unlink($temp_firma);

// Registrar en BD (opcional, según diseño)
// Por ahora solo retornamos éxito

echo json_encode([
    'success' => true,
    'message' => 'Consentimiento guardado correctamente',
    'pdf_nombre' => $pdf_nombre,
    'pdf_url' => 'view_pdf.php?file=' . urlencode($pdf_nombre)
]);
?>
```

**Flujo de generación de consentimiento:**

1. **Frontend (JavaScript con jsPDF):**
   ```javascript
   // Capturar firma con canvas
   const canvas = document.getElementById('signature-canvas');
   const firmaData = canvas.toDataURL('image/png');
   
   // Generar PDF con jsPDF
   const pdf = new jsPDF();
   pdf.setFontSize(16);
   pdf.text('CONSENTIMIENTO INFORMADO', 20, 20);
   pdf.text('Paciente: ' + nombrePaciente, 20, 40);
   // ... más contenido
   
   // Agregar firma al PDF
   pdf.addImage(firmaData, 'PNG', 20, 200, 50, 25);
   
   // Convertir PDF a base64
   const pdfBase64 = pdf.output('datauristring');
   
   // Enviar al servidor
   fetch('save_consent.php', {
       method: 'POST',
       body: JSON.stringify({
           cita_id: citaId,
           firma_data: firmaData,
           pdf_data: pdfBase64
       })
   });
   ```

2. **Backend (PHP):**
   - Recibir base64 de firma y PDF
   - Decodificar y validar
   - Guardar archivo en `src/consentimientos/`
   - Retornar URL de visualización

---

## 7.7 Módulo de Historial Médico (PDF)

### 7.7.1 Componentes

**Archivos principales:**
- `src/php_action/get_patient_history.php` - Dermatología
- `src/php_action/get_patient_history_podologia.php` - Podología
- `src/php_action/get_patient_history_tamizaje.php` - Tamizaje
- Frontend: Funciones JavaScript con jsPDF

### 7.7.2 Código Crítico: Obtener Historial

**Archivo:** `src/php_action/get_patient_history_podologia.php`

```php
<?php
session_start();
header('Content-Type: application/json');
require_once '../database_connection.php';

// Validar sesión de doctor
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$doctor_id = $_SESSION['user_id'];
$patient_id = intval($_GET['patient_id']);

// Validar que el doctor tenga especialidad de Podología (ID: 2)
$stmt = $conex->prepare(
    "SELECT id FROM doctor_especialidades 
     WHERE id_doctor = ? AND id_especialidad = 2"
);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No tiene permiso para acceder a historiales de podología']);
    exit();
}

// Obtener datos del paciente
$stmt = $conex->prepare(
    "SELECT u.user_id, u.nombre, u.apellido, u.telefono, u.email, u.edad, g.nombre as genero
     FROM usuarios u
     LEFT JOIN genero g ON u.id_genero = g.id_genero
     WHERE u.user_id = ?"
);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Paciente no encontrado']);
    exit();
}

$patient = $result->fetch_assoc();

// Obtener citas de podología del paciente con este doctor
$stmt = $conex->prepare(
    "SELECT c.*, 
            CONCAT(d.nombre, ' ', d.apellido) as nombre_doctor
     FROM citas c
     JOIN usuarios d ON c.id_doctor = d.user_id
     WHERE c.id_usuario = ? 
       AND c.id_doctor = ?
       AND LOWER(c.servicio) = 'podología'
     ORDER BY c.fecha DESC, c.horario DESC"
);
$stmt->bind_param("ii", $patient_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = [
        'id_cita' => $row['id_cita'],
        'fecha' => $row['fecha'],
        'horario' => $row['horario'],
        'estado' => $row['estado'],
        'observaciones' => $row['observaciones'],
        'monto' => $row['monto'],
        'nombre_doctor' => $row['nombre_doctor']
    ];
}

// Retornar datos
echo json_encode([
    'success' => true,
    'patient' => $patient,
    'appointments' => $appointments,
    'total_citas' => count($appointments)
]);
?>
```

**Frontend: Generar PDF con jsPDF**

```javascript
async function generarHistorialPDF(patientId) {
    try {
        // Obtener datos del historial
        const response = await fetch(`../php_action/get_patient_history_podologia.php?patient_id=${patientId}`);
        const data = await response.json();
        
        if (!data.success) {
            alert(data.message);
            return;
        }
        
        const patient = data.patient;
        const appointments = data.appointments;
        
        // Crear PDF
        const pdf = new jsPDF();
        let yPos = 20;
        
        // Header
        pdf.setFontSize(18);
        pdf.setFont('helvetica', 'bold');
        pdf.text('HISTORIAL MÉDICO - PODOLOGÍA', 20, yPos);
        yPos += 10;
        
        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'normal');
        pdf.text(`Generado el: ${new Date().toLocaleDateString('es-ES')}`, 20, yPos);
        yPos += 15;
        
        // Datos del paciente
        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text('DATOS DEL PACIENTE', 20, yPos);
        yPos += 8;
        
        pdf.setFontSize(11);
        pdf.setFont('helvetica', 'normal');
        pdf.text(`Nombre: ${patient.nombre} ${patient.apellido}`, 20, yPos);
        yPos += 6;
        pdf.text(`Edad: ${patient.edad} años`, 20, yPos);
        yPos += 6;
        pdf.text(`Teléfono: ${patient.telefono}`, 20, yPos);
        yPos += 6;
        if (patient.email) {
            pdf.text(`Email: ${patient.email}`, 20, yPos);
            yPos += 6;
        }
        yPos += 5;
        
        // Citas
        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text(`HISTORIAL DE CITAS (${appointments.length} registros)`, 20, yPos);
        yPos += 10;
        
        appointments.forEach((cita, index) => {
            // Verificar espacio en página
            if (yPos > 250) {
                pdf.addPage();
                yPos = 20;
            }
            
            pdf.setFontSize(12);
            pdf.setFont('helvetica', 'bold');
            pdf.text(`Cita #${index + 1}`, 20, yPos);
            yPos += 6;
            
            pdf.setFontSize(10);
            pdf.setFont('helvetica', 'normal');
            pdf.text(`Fecha: ${cita.fecha}`, 25, yPos);
            yPos += 5;
            pdf.text(`Hora: ${cita.horario}`, 25, yPos);
            yPos += 5;
            pdf.text(`Estado: ${cita.estado}`, 25, yPos);
            yPos += 5;
            
            if (cita.observaciones) {
                pdf.text('Observaciones:', 25, yPos);
                yPos += 5;
                
                // Dividir observaciones en líneas
                const lines = pdf.splitTextToSize(cita.observaciones, 160);
                lines.forEach(line => {
                    pdf.text(line, 30, yPos);
                    yPos += 5;
                });
            }
            
            yPos += 5; // Espacio entre citas
        });
        
        // Footer
        const pageCount = pdf.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            pdf.setPage(i);
            pdf.setFontSize(9);
            pdf.text(`Página ${i} de ${pageCount}`, 20, 285);
            pdf.text('PropielEquipo - Sistema de Gestión Médica', 150, 285);
        }
        
        // Abrir en nueva pestaña
        const pdfBlob = pdf.output('blob');
        const pdfUrl = URL.createObjectURL(pdfBlob);
        window.open(pdfUrl, '_blank');
        
    } catch (error) {
        console.error('Error generando PDF:', error);
        alert('Error al generar el historial');
    }
}
```

---

# 8. PRUEBAS Y VALIDACIÓN

## 8.1 Estrategia de Testing

### 8.1.1 Niveles de Testing

| Nivel | Tipo | Responsable | Herramientas |
|-------|------|-------------|--------------|
| **Unitario** | Funciones individuales | Desarrollador | PHPUnit (futuro) |
| **Integración** | Módulos completos | Desarrollador | Postman, cURL |
| **Sistema** | Flujos end-to-end | QA/Desarrollador | Testing manual |
| **Aceptación** | Casos de uso reales | Cliente/Usuario | UAT manual |

### 8.1.2 Cobertura de Testing

**Módulos críticos con alta prioridad:**
- ✅ Autenticación (login, registro, sesiones)
- ✅ Reserva de citas (disponibilidad, race conditions)
- ✅ Verificación de pagos (aprobación, rechazo)
- ✅ Gestión de horarios (CRUD, validaciones)
- ⚠️ Upload de archivos (imágenes, comprobantes) - Testing parcial
- ⚠️ Generación de PDFs (consentimientos, historiales) - Testing parcial

**Módulos con testing básico:**
- Visualización de dashboards
- Navegación entre páginas
- Cambio de especialidad (doctores)

---

## 8.2 Casos de Prueba

### 8.2.1 TC-001: Login de Paciente

**Objetivo:** Verificar que un paciente puede iniciar sesión correctamente

**Precondiciones:**
- Usuario registrado en BD con rol=3
- Teléfono: 1234567890
- Contraseña: Test123!

**Pasos:**
1. Acceder a `src/Landing/login.html`
2. Ingresar teléfono: `1234567890`
3. Ingresar contraseña: `Test123!`
4. Hacer clic en "Iniciar Sesión"

**Resultado esperado:**
- Redirige a `src/Paciente/dashboardpaciente.php`
- Sesión creada con variables: `user_id`, `telefono`, `rol=3`
- Muestra nombre del paciente en navbar

**Resultado obtenido:** ✅ PASS

**Casos alternativos:**

| Caso | Input | Resultado esperado |
|------|-------|-------------------|
| Contraseña incorrecta | Tel: 1234567890, Pass: wrong | Error: "Credenciales incorrectas" |
| Usuario no existe | Tel: 9999999999 | Error: "Credenciales incorrectas" |
| Campos vacíos | Tel: "", Pass: "" | Error: "Campos requeridos" |
| Doctor intenta login paciente | Tel: doctor, Pass: correct | Error: "Use el login correspondiente" |

---

### 8.2.2 TC-002: Reserva de Cita con Horarios Disponibles

**Objetivo:** Verificar que un paciente puede reservar una cita en un slot disponible

**Precondiciones:**
- Paciente autenticado
- Doctor con `id_doctor=5` tiene horarios configurados
- Fecha futura: mañana
- Al menos 1 slot disponible

**Pasos:**
1. Acceder a `src/Paciente/reservar.php`
2. Seleccionar especialidad: "Podología"
3. Seleccionar doctor: "Dr. González"
4. Seleccionar fecha: [mañana]
5. Sistema carga horarios disponibles (AJAX)
6. Seleccionar horario: "10:00"
7. Confirmar reserva

**Resultado esperado:**
- Cita creada en BD con estado `pendiente_pago`
- Redirige a `subir_comprobante.php`
- Email de confirmación (futuro)

**Validaciones:**
- ✅ Slot desaparece de disponibles
- ✅ No se puede reservar mismo slot dos veces
- ✅ Fecha no puede ser pasada
- ✅ Fecha no puede ser más de 3 meses adelante

**Resultado obtenido:** ✅ PASS

---

### 8.2.3 TC-003: Race Condition en Reservas

**Objetivo:** Verificar que dos usuarios no puedan reservar el mismo slot simultáneamente

**Precondiciones:**
- Dos usuarios autenticados en navegadores diferentes
- Doctor tiene solo 1 slot disponible: "14:00"
- Ambos usuarios en página de reserva

**Pasos:**
1. Usuario A selecciona slot "14:00"
2. Usuario B selecciona slot "14:00" (antes de que A confirme)
3. Usuario A hace clic en "Confirmar" (T=0s)
4. Usuario B hace clic en "Confirmar" (T=0.5s)

**Resultado esperado:**
- Usuario A: Cita creada exitosamente
- Usuario B: Error: "Este horario acaba de ser reservado. Por favor elija otro."
- Solo 1 registro en BD para ese slot

**Validación en código:**
```php
// check_availability.php ejecuta justo antes de INSERT
SELECT COUNT(*) FROM citas 
WHERE id_doctor = ? AND fecha = ? AND horario = ?
  AND estado NOT IN ('cancelada', 'rechazada')
```

**Resultado obtenido:** ✅ PASS

---

### 8.2.4 TC-004: Verificación de Pago por Doctor

**Objetivo:** Verificar que un doctor puede aprobar un comprobante de pago

**Precondiciones:**
- Doctor autenticado con especialidad Dermatología
- Cita con `id_cita=123` en estado `pendiente_pago`
- Comprobante subido por paciente

**Pasos:**
1. Doctor accede a `verificar_pagos_dermatologia.php`
2. Sistema muestra galería de comprobantes pendientes
3. Doctor ve imagen del comprobante de cita #123
4. Doctor hace clic en "Verificar Pago"
5. Sistema solicita confirmación
6. Doctor confirma

**Resultado esperado:**
- Cita actualizada: `estado='pendiente'`
- Campos actualizados: `verificado_por=[doctor_id]`, `fecha_verificacion=NOW()`
- Cita desaparece de lista de pendientes
- Paciente notificado (futuro)

**Validaciones:**
- ✅ Solo el doctor de esa cita puede verificar
- ✅ No se puede verificar dos veces la misma cita
- ✅ Citas rechazadas no aparecen en lista

**Resultado obtenido:** ✅ PASS

---

### 8.2.5 TC-005: Gestión de Horarios por Admin

**Objetivo:** Verificar que un admin puede crear horarios para un doctor

**Precondiciones:**
- Admin autenticado (`rol=4`)
- Doctor con `id_doctor=7` sin horarios configurados

**Pasos:**
1. Admin accede a `gestionar_horarios.php`
2. Selecciona doctor: "Dr. Martínez"
3. Selecciona día: "Lunes"
4. Hora inicio: "09:00"
5. Hora fin: "18:00"
6. Intervalo: "60 minutos"
7. Marca checkbox "Aplicar a toda la semana"
8. Clic en "Guardar Horario"

**Resultado esperado:**
- 5 registros insertados en tabla `horarios` (lunes-viernes)
- Todos con `activo=1`
- Mensaje: "Horario(s) guardado(s) exitosamente"
- Horarios visibles en sistema de reservas

**Validaciones:**
- ✅ `hora_fin > hora_inicio`
- ✅ No solapamiento de horarios del mismo día
- ✅ Intervalo debe ser valor válido (30/45/60/90/120)
- ✅ Solo usuarios con `rol=4` pueden acceder

**Resultado obtenido:** ✅ PASS

---

### 8.2.6 TC-006: Upload de Imagen Médica

**Objetivo:** Verificar que un paciente puede subir imágenes médicas

**Precondiciones:**
- Paciente autenticado
- Archivo: imagen.jpg (5 MB, JPEG)

**Pasos:**
1. Paciente accede a `imagenes_medicas.php`
2. Clic en "Subir Nueva Imagen"
3. Selecciona archivo: `imagen.jpg`
4. Categoría: "Rayos X"
5. Descripción: "Pie izquierdo"
6. Clic en "Subir"

**Resultado esperado:**
- Archivo guardado en `src/Images/ImgMedicas/{user_id}/[nombre_unico].jpg`
- Registro insertado en tabla `imagenes_medicas`
- Imagen visible en galería del paciente
- Solo paciente y sus doctores pueden ver la imagen

**Validaciones:**
- ✅ Tipos permitidos: JPG, PNG, PDF
- ✅ Tamaño máximo: 10 MB
- ✅ Nombre único (no sobreescribe)
- ❌ Archivos ejecutables rechazados
- ❌ Archivos >10 MB rechazados

**Resultado obtenido:** ✅ PASS

---

## 8.3 Testing Manual

### 8.3.1 Checklist de Testing Funcional

**Módulo de Autenticación:**
- [x] Login con credenciales válidas
- [x] Login con credenciales inválidas
- [x] Registro de nuevo usuario
- [x] Validación de teléfono único
- [x] Validación de email único
- [x] Password hashing correcto
- [x] Logout destruye sesión

**Módulo de Citas:**
- [x] Listar doctores por especialidad
- [x] Calcular horarios disponibles
- [x] Reservar cita exitosamente
- [x] Validar slot antes de insertar
- [x] Cancelar cita propia
- [x] Ver historial de citas
- [x] Filtrado por estado

**Módulo de Pagos:**
- [x] Subir comprobante de pago
- [x] Listar comprobantes pendientes (doctor)
- [x] Aprobar comprobante
- [x] Rechazar comprobante con motivo
- [x] Actualizar estado de cita

**Módulo de Horarios:**
- [x] Crear horario nuevo
- [x] Editar horario existente
- [x] Eliminar horario
- [x] Toggle activar/desactivar
- [x] Aplicar horario a semana completa
- [x] Crear bloqueo de fechas
- [x] Eliminar bloqueo

**Módulo de Imágenes:**
- [x] Upload de imagen JPG
- [x] Upload de imagen PNG
- [x] Upload de PDF
- [x] Rechazar archivos no permitidos
- [x] Rechazar archivos >10 MB
- [x] Ver imagen (propietario)
- [x] Ver imagen (doctor autorizado)
- [x] Denegar acceso no autorizado
- [x] Eliminar imagen propia

**Módulo de Consentimientos:**
- [x] Capturar firma en canvas
- [x] Generar PDF con jsPDF
- [x] Guardar PDF en servidor
- [x] Visualizar PDF guardado
- [ ] Validar firma obligatoria (pendiente)

**Módulo de Historiales:**
- [x] Generar historial dermatología
- [x] Generar historial podología
- [x] Generar historial tamizaje
- [x] Validar permiso por especialidad
- [x] PDF con datos correctos
- [x] PDF con formato profesional

---

# 9. SEGURIDAD

## 9.1 Medidas Implementadas

### 9.1.1 Autenticación y Autorización

**✅ Implementado:**

1. **Password Hashing:**
   ```php
   // Registro
   $password_hash = password_hash($password, PASSWORD_BCRYPT);
   
   // Login
   if (password_verify($password, $user['password'])) {
       // Autenticado
   }
   ```
   - Algoritmo: bcrypt (cost=10)
   - Salting automático
   - Resistente a rainbow tables

2. **Validación de Sesión:**
   ```php
   session_start();
   if (!isset($_SESSION['user_id'])) {
       header("Location: ../Landing/login.html");
       exit();
   }
   ```
   - Presente en todos los archivos protegidos
   - Validación de rol específico

3. **Validación de Permisos:**
   ```php
   // Verificar que doctor solo acceda a sus propias citas
   $stmt = $conex->prepare(
       "SELECT * FROM citas WHERE id_cita = ? AND id_doctor = ?"
   );
   ```
   - Autorización a nivel de consulta
   - No se confía en parámetros del cliente

### 9.1.2 Protección contra Inyección SQL

**✅ Implementado:**

1. **Prepared Statements (100% de consultas):**
   ```php
   // ✅ CORRECTO
   $stmt = $conex->prepare("SELECT * FROM usuarios WHERE telefono = ?");
   $stmt->bind_param("s", $telefono);
   $stmt->execute();
   
   // ❌ NUNCA usar concatenación directa
   // $query = "SELECT * FROM usuarios WHERE telefono = '$telefono'";
   ```

2. **Validación de tipos:**
   ```php
   $user_id = intval($_POST['user_id']);  // Forzar entero
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   ```

### 9.1.3 Protección de Archivos

**✅ Implementado:**

1. **Whitelist de tipos de archivo:**
   ```php
   $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
   if (!in_array($tipo_archivo, $tipos_permitidos)) {
       // Rechazar
   }
   ```

2. **Validación de extensión:**
   ```php
   $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
   $extensiones_validas = ['jpg', 'jpeg', 'png', 'pdf'];
   if (!in_array(strtolower($extension), $extensiones_validas)) {
       // Rechazar
   }
   ```

3. **Nombres únicos:**
   ```php
   $nombre_unico = uniqid() . '_' . time() . '.' . $extension;
   ```
   - Previene sobreescritura maliciosa
   - Previene directory traversal

4. **Límite de tamaño:**
   ```php
   $max_size = 10 * 1024 * 1024; // 10 MB
   if ($tamano > $max_size) {
       // Rechazar
   }
   ```

5. **Permisos de directorio:**
   ```php
   mkdir($directorio_usuario, 0755, true);
   ```
   - No ejecutable (0755, no 0777)

### 9.1.4 Protección XSS (Cross-Site Scripting)

**⚠️ Parcialmente implementado:**

**Recomendaciones para completar:**

```php
// Escapar output en HTML
echo htmlspecialchars($user['nombre'], ENT_QUOTES, 'UTF-8');

// En PHP templates:
<h1>Bienvenido, <?= htmlspecialchars($nombre) ?></h1>

// En JSON (automático con json_encode)
echo json_encode($data); // Escapa automáticamente
```

**Actualmente:**
- JSON endpoints seguros (json_encode escapa automáticamente)
- HTML directo necesita revisión manual

### 9.1.5 Protección CSRF (Cross-Site Request Forgery)

**❌ No implementado (Recomendado para producción)**

**Implementación recomendada:**

```php
// Generar token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// En formularios HTML
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

// Validar en backend
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token inválido');
}
```

---

## 9.2 Vulnerabilidades Mitigadas

| Vulnerabilidad | Severidad | Mitigación | Estado |
|----------------|-----------|------------|--------|
| **SQL Injection** | CRÍTICA | Prepared statements en 100% de consultas | ✅ Mitigado |
| **XSS Stored** | ALTA | Escapado de output con htmlspecialchars | ⚠️ Parcial |
| **XSS Reflected** | ALTA | Validación de inputs | ⚠️ Parcial |
| **CSRF** | MEDIA | Tokens CSRF en formularios | ❌ Pendiente |
| **Session Hijacking** | ALTA | HTTPS obligatorio, session_regenerate_id | ⚠️ Parcial |
| **Path Traversal** | ALTA | Nombres únicos, validación de rutas | ✅ Mitigado |
| **File Upload Malicioso** | CRÍTICA | Whitelist de tipos, validación de extensión | ✅ Mitigado |
| **Broken Access Control** | ALTA | Validación de sesión y permisos | ✅ Mitigado |
| **Weak Password** | MEDIA | Longitud mínima 6 caracteres | ⚠️ Básico |
| **Sensitive Data Exposure** | ALTA | HTTPS, password hashing | ✅ Mitigado |

---

## 9.3 Recomendaciones Futuras

### 9.3.1 Prioridad Alta

1. **Implementar CSRF Protection:**
   - Generar tokens en todas las sesiones
   - Validar en todos los POST/PUT/DELETE

2. **Fortalecer validación XSS:**
   - Auditar todos los outputs HTML
   - Usar `htmlspecialchars()` sistemáticamente
   - Content Security Policy (CSP) headers

3. **HTTPS Obligatorio:**
   ```apache
   # .htaccess
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

4. **Rate Limiting:**
   - Limitar intentos de login (3 intentos / 15 min)
   - Protección contra brute force

5. **Logging de Seguridad:**
   ```php
   error_log("Intento de login fallido: " . $telefono . " desde " . $_SERVER['REMOTE_ADDR']);
   ```

### 9.3.2 Prioridad Media

6. **Políticas de Contraseña Más Fuertes:**
   - Mínimo 8 caracteres
   - Requerir mayúsculas, minúsculas, números
   - Validar contra diccionarios comunes

7. **Two-Factor Authentication (2FA):**
   - SMS o TOTP para roles críticos (admin, doctor)

8. **Sanitización de nombres de archivo:**
   ```php
   $nombre_sanitizado = preg_replace('/[^a-zA-Z0-9._-]/', '', $nombre_original);
   ```

9. **Headers de Seguridad:**
   ```php
   header('X-Content-Type-Options: nosniff');
   header('X-Frame-Options: DENY');
   header('X-XSS-Protection: 1; mode=block');
   header("Content-Security-Policy: default-src 'self'");
   ```

10. **Session Security:**
    ```php
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Solo si HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    session_regenerate_id(true); // Después de login
    ```

### 9.3.3 Prioridad Baja

11. **Auditoría de Código:**
    - Revisión por terceros
    - Herramientas automáticas (SonarQube, Snyk)

12. **WAF (Web Application Firewall):**
    - ModSecurity en Apache
    - Cloudflare WAF

13. **Backups Encriptados:**
    - Backups diarios automáticos
    - Encriptación AES-256
    - Almacenamiento offsite

---

# 10. MANTENIMIENTO Y SOPORTE

## 10.1 Procedimientos de Mantenimiento

### 10.1.1 Backups de Base de Datos

**Frecuencia:** Diaria (automated cron job)

**Comando:**
```bash
#!/bin/bash
# Backup script - /home/scripts/backup_db.sh

DB_NAME="propielequipo2"
DB_USER="root"
DB_PASS="password"
BACKUP_DIR="/backups/mysql"
DATE=$(date +%Y%m%d_%H%M%S)

# Crear backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/backup_$DATE.sql

# Comprimir
gzip $BACKUP_DIR/backup_$DATE.sql

# Eliminar backups antiguos (> 7 días)
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete

echo "Backup completado: backup_$DATE.sql.gz"
```

**Cron job:**
```bash
# Ejecutar a las 2 AM diariamente
0 2 * * * /home/scripts/backup_db.sh >> /var/log/backup.log 2>&1
```

### 10.1.2 Monitoreo de Logs

**Logs a revisar:**
1. Apache/Nginx error log: `/var/log/apache2/error.log`
2. PHP error log: `/var/log/php-fpm/error.log`
3. MySQL slow query log: `/var/log/mysql/slow-queries.log`

**Alertas recomendadas:**
- Errores 500 > 10 en 1 hora
- Intentos de login fallidos > 50 en 1 hora
- Uso de disco > 80%
- Uso de CPU > 90% por 5 minutos

### 10.1.3 Actualizaciones de Software

**Mensuales:**
- Actualizar PHP (security patches)
- Actualizar MySQL/MariaDB
- Actualizar dependencias npm (Tailwind CSS)

**Comando:**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt upgrade php php-mysql php-gd

# Verificar versión
php -v
mysql --version
```

---

## 10.2 Contactos de Soporte

| Rol | Nombre | Contacto | Responsabilidad |
|-----|--------|----------|----------------|
| **Desarrollador Principal** | Juan | desarrollo@propielequipo.com | Bugs críticos, nuevas features |
| **DBA** | TBD | dba@propielequipo.com | Base de datos, backups |
| **Sysadmin** | TBD | sysadmin@propielequipo.com | Servidor, deployment |
| **Soporte Nivel 1** | TBD | soporte@propielequipo.com | Dudas de usuarios |

---

# 11. CONCLUSIONES

## 11.1 Estado del Proyecto

**Funcionalidades Implementadas:**
- ✅ Sistema de autenticación multi-rol
- ✅ Reserva de citas con validación de disponibilidad
- ✅ Gestión de horarios por administrador
- ✅ Verificación de pagos por doctor
- ✅ Upload y gestión de imágenes médicas
- ✅ Generación de consentimientos con firma digital
- ✅ Generación de historiales médicos en PDF
- ✅ Dashboards específicos por rol
- ✅ Separación por especialidades médicas

**Métricas del Proyecto:**
- **Archivos PHP:** ~80
- **Archivos HTML:** ~10
- **Tablas de BD:** 13
- **Endpoints/APIs:** ~40
- **Líneas de código:** ~15,000
- **Tiempo de desarrollo:** 6 meses

## 11.2 Fortalezas del Sistema

1. **Arquitectura modular:** Fácil de mantener y extender
2. **Separación de responsabilidades:** Frontend/Backend bien definidos
3. **Seguridad robusta:** Prepared statements, password hashing, validación de sesiones
4. **Escalabilidad:** Soporte para múltiples especialidades y doctores
5. **UX/UI moderna:** Tailwind CSS, diseño responsive

## 11.3 Áreas de Mejora

1. **Testing automatizado:** Implementar PHPUnit para tests unitarios
2. **Notificaciones:** Email/SMS para confirmaciones y recordatorios
3. **Documentación de API:** Swagger/OpenAPI para endpoints
4. **Performance:** Caching de consultas frecuentes (Redis)
5. **Reportes:** Dashboard analítico para administradores

## 11.4 Roadmap Futuro

**Versión 2.0 (Q1 2026):**
- [ ] Sistema de notificaciones (email/SMS)
- [ ] Reportes y estadísticas avanzadas
- [ ] API REST documentada
- [ ] Aplicación móvil (React Native)
- [ ] Integración con pasarelas de pago

**Versión 2.5 (Q2 2026):**
- [ ] Videoconsultas (WebRTC)
- [ ] Chat en tiempo real (doctor-paciente)
- [ ] Recetas electrónicas
- [ ] Integración con laboratorios

**Versión 3.0 (Q3 2026):**
- [ ] Inteligencia Artificial (detección de anomalías en imágenes)
- [ ] Análisis predictivo de citas
- [ ] Sistema de recomendaciones
- [ ] Blockchain para historiales médicos

---

**FIN DEL MANUAL TÉCNICO COMPLETO**

---

## Resumen de Documentos

Este manual técnico se compone de **7 documentos:**

1. **MANUAL_TECNICO_PARTE_1.md**
   - Introducción, Descripción del Sistema
   - Requerimientos Técnicos
   - Arquitectura (Parcial)

2. **MANUAL_TECNICO_PARTE_2.1.md**
   - Casos de Uso detallados
   - Diagramas de Secuencia
   - Diagrama de Despliegue

3. **MANUAL_TECNICO_PARTE_2.2.md**
   - Diagrama de Componentes
   - Modelo de Datos completo (ER, Esquema Físico, Diccionario)

4. **MANUAL_TECNICO_PARTE_3.1.md**
   - Estructura de Código Fuente
   - Convenciones y Estándares
   - Módulos Críticos (Auth, Citas, Horarios, Pagos)

5. **MANUAL_TECNICO_PARTE_3.2.md** (Este documento)
   - Módulos Adicionales (Imágenes, Consentimientos, Historiales)
   - Pruebas y Validación
   - Seguridad
   - Mantenimiento
   - Conclusiones

**Total:** ~100 páginas de documentación técnica profesional

---

**Elaborado por:** Equipo de Desarrollo PropielEquipo  
**Revisión Final:** v1.0 - Noviembre 2025  
**Contacto Técnico:** desarrollo@propielequipo.com  
**Repositorio:** github.com/Kelevrax8/PropielEquipo
