# MANUAL TÉCNICO - SISTEMA PROPIELEQUIPO
## PARTE 2.2: Diagrama de Componentes y Modelo de Datos

**Versión:** 1.0  
**Fecha:** Noviembre 2025  
**Continuación de:** MANUAL_TECNICO_PARTE_2.1.md

---

# ÍNDICE DE CONTENIDOS - PARTE 2.2

4. [Arquitectura del Sistema (Continuación)](#4-arquitectura-del-sistema-continuación)
   - 4.6 Diagrama de Componentes
5. [Modelo de Datos](#5-modelo-de-datos)
   - 5.1 Diagrama Entidad-Relación
   - 5.2 Esquema Físico
   - 5.3 Diccionario de Datos

---

# 4. ARQUITECTURA DEL SISTEMA (Continuación)

## 4.6 Diagrama de Componentes

### 4.6.1 Vista de Componentes del Sistema

```
┌──────────────────────────────────────────────────────────────────────┐
│                   PROPIELEQUIPO - COMPONENTES                        │
│                    Arquitectura por Capas                            │
└──────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                      CAPA DE PRESENTACIÓN                           │
│                       (Frontend Layer)                              │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────────┐    ┌──────────────────────┐    ┌────────────────┐
│   Landing Pages      │    │   Paciente UI        │    │  Doctor UI     │
│                      │    │                      │    │                │
│  • login.html        │    │  • dashboardpaciente │    │  • Citas_*.php │
│  • registrar.html    │    │  • reservar.php      │    │  • pacientes_* │
│  • contacto.html     │    │  • Citas.php         │    │  • verificar_  │
│  • servicios.html    │    │  • subir_comprobante │    │    pagos_*.php │
│                      │    │  • consentimiento    │    │  • dashboard   │
│  [Tailwind CSS]      │    │  • imagenes_medicas  │    │                │
│  [Ionicons]          │    │  • reservas.php      │    │  [Especialidad │
└──────────┬───────────┘    └──────────┬───────────┘    │   específica]  │
           │                           │                 └────────┬───────┘
           │                           │                          │
           │                           │                          │
           └───────────────────────────┼──────────────────────────┘
                                       │
                                       │
                                       │ AJAX/POST/GET
                                       ↓
┌─────────────────────────────────────────────────────────────────────┐
│                      CAPA DE LÓGICA DE NEGOCIO                      │
│                       (Business Logic Layer)                        │
└─────────────────────────────────────────────────────────────────────┘

┌───────────────────┐  ┌───────────────────┐  ┌───────────────────┐
│ Auth Module       │  │ Appointment Module│  │ Payment Module    │
│                   │  │                   │  │                   │
│ • login.php       │  │ • reservar.php    │  │ • verificar_pago  │
│ • registrar.php   │  │ • check_          │  │ • upload_compro-  │
│ • registrarroot   │  │   availability    │  │   bante.php       │
│ • logout.php      │  │ • cancel_         │  │ • subir_compro-   │
│                   │  │   appointment     │  │   bante.php       │
│ [Session mgmt]    │  │ • get_available_  │  │                   │
│ [Password hash]   │  │   hours           │  │ [Imagen upload]   │
└─────────┬─────────┘  │                   │  │ [Validación]      │
          │            │ [Validación slot] │  └─────────┬─────────┘
          │            │ [Race condition]  │            │
          │            └─────────┬─────────┘            │
          │                      │                      │
          └──────────────────────┼──────────────────────┘
                                 │
                                 ↓
┌───────────────────┐  ┌───────────────────┐  ┌───────────────────┐
│ Schedule Module   │  │ Consent Module    │  │ Medical Images    │
│                   │  │                   │  │ Module            │
│ Admin:            │  │ • save_consent    │  │                   │
│ • get_doctor_     │  │ • view_pdf.php    │  │ • upload_medical_ │
│   schedule        │  │                   │  │   image.php       │
│ • save_schedule   │  │ [Canvas firma]    │  │ • delete_medical_ │
│ • toggle_schedule │  │ [jsPDF genera]    │  │   image.php       │
│ • delete_schedule │  │                   │  │ • secure_image_   │
│ • save_block      │  │                   │  │   viewer.php      │
│ • get_doctor_     │  │                   │  │                   │
│   blocks          │  │                   │  │ [Categorización]  │
│ • delete_block    │  │                   │  │ [Seguridad]       │
└─────────┬─────────┘  └─────────┬─────────┘  └─────────┬─────────┘
          │                      │                      │
          └──────────────────────┼──────────────────────┘
                                 │
                                 ↓
┌───────────────────┐  ┌───────────────────┐  ┌───────────────────┐
│ History Module    │  │ Specialty Module  │  │ Config Module     │
│                   │  │                   │  │                   │
│ • get_patient_    │  │ • switch_         │  │ • actualizar_datos│
│   history.php     │  │   specialty.php   │  │   _bancarios.php  │
│ • get_patient_    │  │                   │  │                   │
│   history_        │  │ [Session switch]  │  │ [Admin only]      │
│   podologia.php   │  │                   │  │                   │
│ • get_patient_    │  │                   │  │                   │
│   history_        │  │                   │  │                   │
│   tamizaje.php    │  │                   │  │                   │
│                   │  │                   │  │                   │
│ [jsPDF genera]    │  │                   │  │                   │
└─────────┬─────────┘  └─────────┬─────────┘  └─────────┬─────────┘
          │                      │                      │
          └──────────────────────┼──────────────────────┘
                                 │
                                 ↓
┌───────────────────┐  ┌───────────────────┐
│ Observation Module│  │ Dashboard Module  │
│                   │  │                   │
│ • update_         │  │ Dashboard views:  │
│   observations    │  │ • dashboardpaciente│
│   .php            │  │ • dashboard_admin │
│                   │  │                   │
│ [Doctor only]     │  │ [Estadísticas]    │
│                   │  │ [Resúmenes]       │
└─────────┬─────────┘  └─────────┬─────────┘
          │                      │
          └──────────────────────┘
                                 │
                                 │ Database Queries
                                 ↓
┌─────────────────────────────────────────────────────────────────────┐
│                      CAPA DE ACCESO A DATOS                         │
│                     (Data Access Layer)                             │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│  database_connection.php                                         │
│                                                                  │
│  • Singleton pattern para conexión MySQL                        │
│  • Fallback localhost → 127.0.0.1                                │
│  • Credenciales desde template                                   │
│                                                                  │
│  $conex = mysqli_connect($db_host, $db_user, $db_pass, $db_name)│
└────────────────────────────┬─────────────────────────────────────┘
                             │
                             ↓
┌──────────────────────────────────────────────────────────────────┐
│  database_queries.php                                            │
│                                                                  │
│  Clase DatabaseQueries:                                          │
│  • getAllUsers()                                                 │
│  • getUserById($id)                                              │
│  • getDoctorsBySpecialty($specialty_id)                          │
│  • getAppointmentsByUser($user_id)                               │
│  • getAppointmentsByDoctor($doctor_id)                           │
│  • getPendingPayments($doctor_id)                                │
│                                                                  │
│  [Prepared statements PDO]                                       │
│  [Sanitización inputs]                                           │
└────────────────────────────┬─────────────────────────────────────┘
                             │
                             │ SQL Queries
                             ↓
┌─────────────────────────────────────────────────────────────────────┐
│                         CAPA DE DATOS                               │
│                         (Data Layer)                                │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│  MySQL Database: propielequipo2                                  │
│                                                                  │
│  Tablas (13):                                                    │
│  ┌────────────┐  ┌────────────┐  ┌──────────────┐              │
│  │ usuarios   │  │ citas      │  │ horarios     │              │
│  └────────────┘  └────────────┘  └──────────────┘              │
│                                                                  │
│  ┌──────────────┐  ┌─────────────┐  ┌───────────────┐          │
│  │ bloqueos_    │  │ especialida-│  │ doctor_       │          │
│  │ horarios     │  │ des         │  │ especialidades│          │
│  └──────────────┘  └─────────────┘  └───────────────┘          │
│                                                                  │
│  ┌──────────────┐  ┌─────────────┐  ┌───────────────┐          │
│  │ servicios    │  │ imagenes_   │  │ configuracion_│          │
│  │              │  │ medicas     │  │ pagos         │          │
│  └──────────────┘  └─────────────┘  └───────────────┘          │
│                                                                  │
│  ┌──────────────┐  ┌─────────────┐                              │
│  │ genero       │  │ user_type   │                              │
│  └──────────────┘  └─────────────┘                              │
│                                                                  │
│  Storage: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_general│
└──────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────┐
│                    COMPONENTES EXTERNOS (CDN)                       │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌────────────┐
│  Tailwind    │  │  Ionicons    │  │   jsPDF      │  │ html2canvas│
│  CSS         │  │  (Icons)     │  │  (PDF gen)   │  │ (Screenshot│
│              │  │              │  │              │  │  to image) │
│  [Styles]    │  │  [UI icons]  │  │  [Documents] │  │  [Canvas]  │
└──────────────┘  └──────────────┘  └──────────────┘  └────────────┘
```

### 4.6.2 Dependencias entre Componentes

**Flujo de Autenticación:**
```
Login.html → login.php → database_connection.php → usuarios (tabla)
           → Session vars → Dashboard correspondiente
```

**Flujo de Reserva:**
```
reservar.php → get_available_hours.php → horarios + citas + bloqueos_horarios
             → reservar.php → INSERT citas
             → subir_comprobante.php → UPDATE citas.comprobante_pago
```

**Flujo de Verificación:**
```
verificar_pagos_*.php → verificar_pago.php → UPDATE citas.estado
                      → Notificación (futuro)
```

**Flujo de Gestión de Horarios:**
```
gestionar_horarios.php → get_doctor_schedule.php → SELECT horarios
                       → save_schedule.php → INSERT/UPDATE horarios
                       → toggle_schedule.php → UPDATE horarios.activo
                       → delete_schedule.php → DELETE horarios
```

---

# 5. MODELO DE DATOS

## 5.1 Diagrama Entidad-Relación (ER)

```
┌──────────────────────────────────────────────────────────────────────┐
│           DIAGRAMA ENTIDAD-RELACIÓN - PROPIELEQUIPO                  │
│                      (Notación Chen)                                 │
└──────────────────────────────────────────────────────────────────────┘


┌─────────────┐                                      ┌─────────────┐
│   GENERO    │                                      │  USER_TYPE  │
│─────────────│                                      │─────────────│
│ PK id_genero│                                      │ PK id_tipo  │
│    nombre   │                                      │    tipo     │
└──────┬──────┘                                      └──────┬──────┘
       │                                                    │
       │ 1                                                  │ 1
       │                                                    │
       │ tiene                                              │ tiene
       │                                                    │
       │ N                                                  │ N
       │                                                    │
┌──────┴──────────────────────────────────────────────────┴──────┐
│                        USUARIOS                                 │
│─────────────────────────────────────────────────────────────────│
│ PK user_id (INT, AUTO_INCREMENT)                                │
│    nombre (VARCHAR 100)                                         │
│    apellido (VARCHAR 100)                                       │
│    telefono (VARCHAR 15, UNIQUE) ← usado como username         │
│    email (VARCHAR 100, UNIQUE)                                  │
│    password (VARCHAR 255) ← bcrypt hash                         │
│    edad (INT)                                                   │
│ FK id_genero → GENERO                                           │
│ FK rol → USER_TYPE                                              │
│    fecha_registro (DATETIME)                                    │
│    ultimo_acceso (DATETIME)                                     │
└──────┬───────────────────────┬──────────────────────────┬───────┘
       │                       │                          │
       │ 1                     │ 1                        │ 1
       │                       │                          │
       │ realiza               │ atiende                  │ subió
       │                       │                          │
       │ N                     │ N                        │ N
       │                       │                          │
┌──────┴───────────┐    ┌──────┴──────────┐    ┌────────┴────────────┐
│      CITAS       │    │    HORARIOS     │    │  IMAGENES_MEDICAS   │
│──────────────────│    │─────────────────│    │─────────────────────│
│ PK id_cita       │    │ PK id_horario   │    │ PK id_imagen        │
│ FK id_usuario → │    │ FK id_doctor → │    │ FK id_usuario →    │
│    USUARIOS      │    │    USUARIOS     │    │    USUARIOS         │
│ FK id_doctor →  │    │    dia_semana   │    │    ruta_imagen      │
│    USUARIOS      │    │    hora_inicio  │    │    categoria        │
│    fecha (DATE)  │    │    hora_fin     │    │    nombre_archivo   │
│    horario (TIME)│    │    intervalo_   │    │    fecha_subida     │
│    servicio      │    │    minutos      │    │    descripcion      │
│    estado        │    │    activo (BOOL)│    │    tipo_archivo     │
│    observaciones │    │    fecha_creac. │    └─────────────────────┘
│    monto (DEC)   │    │    actualizado  │
│    requiere_pago │    └─────────────────┘
│    comprobante_  │
│    pago (ruta)   │    ┌──────────────────┐
│    fecha_pago    │    │   BLOQUEOS_      │
│    verificado_por│    │   HORARIOS       │
│    fecha_verif.  │    │──────────────────│
│    notas_pago    │    │ PK id_bloqueo    │
│    fecha_creac.  │    │ FK id_doctor →  │
│    actualizado   │    │    USUARIOS      │
└──────────────────┘    │    fecha_inicio  │
                        │    fecha_fin     │
                        │    motivo        │
       ┌────────────────┤    tipo_bloqueo  │
       │                │    fecha_creac.  │
       │                │    actualizado   │
       │                └──────────────────┘
       │
       │ pertenece a
       │
       │ N
       │
       │ 1
       │
┌──────┴────────────────────┐
│    ESPECIALIDADES         │
│───────────────────────────│
│ PK id_especialidad        │
│    nombre_especialidad    │
│    descripcion            │
└──────┬────────────────────┘
       │
       │ tiene
       │
       │ N:M ← Relación muchos a muchos
       │
       │ 1
       │
┌──────┴─────────────────────────────────┐
│      DOCTOR_ESPECIALIDADES             │
│────────────────────────────────────────│
│ PK id (AUTO_INCREMENT)                 │
│ FK id_doctor → USUARIOS.user_id        │
│ FK id_especialidad → ESPECIALIDADES    │
│                                        │
│ [Tabla intermedia para N:M]           │
└────────────────────────────────────────┘


┌────────────────────────────┐         ┌──────────────────────────┐
│      SERVICIOS             │         │   CONFIGURACION_PAGOS    │
│────────────────────────────│         │──────────────────────────│
│ PK id_servicio             │         │ PK id_config             │
│    nombre_servicio         │         │    nombre_banco          │
│    descripcion             │         │    numero_cuenta         │
│    precio (DECIMAL)        │         │    tipo_cuenta           │
│    duracion_estimada (MIN) │         │    titular_cuenta        │
│    activo (BOOLEAN)        │         │    cedula_titular        │
│    fecha_creacion          │         │    email_notificaciones  │
└────────────────────────────┘         │    telefono_contacto     │
                                       │    activo (BOOLEAN)      │
                                       │    fecha_actualizacion   │
                                       └──────────────────────────┘


LEYENDA:
────────
PK = Primary Key (Clave Primaria)
FK = Foreign Key (Clave Foránea)
─── = Relación
1   = Cardinalidad uno
N   = Cardinalidad muchos
N:M = Relación muchos a muchos
```

### 5.1.1 Relaciones Principales

| Relación | Tipo | Cardinalidad | Descripción |
|----------|------|--------------|-------------|
| **USUARIOS - GENERO** | Obligatoria | N:1 | Un usuario tiene un género |
| **USUARIOS - USER_TYPE** | Obligatoria | N:1 | Un usuario tiene un rol |
| **USUARIOS - CITAS (como paciente)** | Opcional | 1:N | Un usuario puede tener muchas citas |
| **USUARIOS - CITAS (como doctor)** | Opcional | 1:N | Un doctor atiende muchas citas |
| **USUARIOS - HORARIOS** | Opcional | 1:N | Un doctor tiene muchos horarios |
| **USUARIOS - BLOQUEOS_HORARIOS** | Opcional | 1:N | Un doctor puede bloquear varias fechas |
| **USUARIOS - IMAGENES_MEDICAS** | Opcional | 1:N | Un usuario puede subir varias imágenes |
| **USUARIOS - ESPECIALIDADES** | Muchos a Muchos | N:M | Un doctor puede tener varias especialidades, una especialidad puede tener varios doctores |
| **DOCTOR_ESPECIALIDADES** | Intermedia | - | Tabla intermedia para relación N:M entre usuarios(doctores) y especialidades |

---

## 5.2 Esquema Físico de la Base de Datos

### 5.2.1 Estructura de Tablas (DDL)

#### Tabla: `usuarios`

```sql
CREATE TABLE usuarios (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    edad INT NOT NULL,
    id_genero INT NOT NULL,
    rol INT NOT NULL DEFAULT 3,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME DEFAULT NULL,
    
    CONSTRAINT fk_usuario_genero FOREIGN KEY (id_genero) 
        REFERENCES genero(id_genero) ON DELETE RESTRICT,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (rol) 
        REFERENCES user_type(id_tipo) ON DELETE RESTRICT,
    
    INDEX idx_telefono (telefono),
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Tabla: `citas`

```sql
CREATE TABLE citas (
    id_cita INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_doctor INT NOT NULL,
    fecha DATE NOT NULL,
    horario TIME NOT NULL,
    servicio VARCHAR(100) NOT NULL,
    estado VARCHAR(50) DEFAULT 'pendiente_pago',
    observaciones TEXT,
    monto DECIMAL(10,2) DEFAULT 0.00,
    requiere_pago BOOLEAN DEFAULT 1,
    comprobante_pago VARCHAR(255),
    fecha_pago DATETIME,
    verificado_por INT,
    fecha_verificacion DATETIME,
    notas_pago TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_cita_usuario FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_cita_doctor FOREIGN KEY (id_doctor) 
        REFERENCES usuarios(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_cita_verificador FOREIGN KEY (verificado_por) 
        REFERENCES usuarios(user_id) ON DELETE SET NULL,
    
    INDEX idx_fecha (fecha),
    INDEX idx_usuario (id_usuario),
    INDEX idx_doctor (id_doctor),
    INDEX idx_estado (estado),
    INDEX idx_servicio (servicio),
    INDEX idx_fecha_horario (fecha, horario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Tabla: `horarios`

```sql
CREATE TABLE horarios (
    id_horario INT PRIMARY KEY AUTO_INCREMENT,
    id_doctor INT NOT NULL,
    dia_semana ENUM('lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    intervalo_minutos INT NOT NULL DEFAULT 60,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_horarios_doctor FOREIGN KEY (id_doctor) 
        REFERENCES usuarios(user_id) ON DELETE CASCADE,
    CONSTRAINT unique_doctor_dia UNIQUE (id_doctor, dia_semana),
    
    INDEX idx_doctor (id_doctor),
    INDEX idx_dia (dia_semana),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Tabla: `bloqueos_horarios`

```sql
CREATE TABLE bloqueos_horarios (
    id_bloqueo INT PRIMARY KEY AUTO_INCREMENT,
    id_doctor INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    motivo VARCHAR(255),
    tipo_bloqueo ENUM('vacaciones', 'conferencia', 'personal', 'otro') DEFAULT 'otro',
    notas TEXT,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    actualizado DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_bloqueos_doctor FOREIGN KEY (id_doctor) 
        REFERENCES usuarios(user_id) ON DELETE CASCADE,
    
    INDEX idx_doctor (id_doctor),
    INDEX idx_fecha_inicio (fecha_inicio),
    INDEX idx_fecha_fin (fecha_fin),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Tabla: `especialidades`

```sql
CREATE TABLE especialidades (
    id_especialidad INT PRIMARY KEY AUTO_INCREMENT,
    nombre_especialidad VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos iniciales
INSERT INTO especialidades (id_especialidad, nombre_especialidad, descripcion) VALUES
(1, 'Dermatología', 'Especialidad médica enfocada en el diagnóstico y tratamiento de enfermedades de la piel'),
(2, 'Podología', 'Especialidad dedicada al cuidado y tratamiento de los pies'),
(3, 'Tamizaje', 'Evaluación y detección temprana de condiciones médicas');
```

#### Tabla: `doctor_especialidades`

```sql
CREATE TABLE doctor_especialidades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_doctor INT NOT NULL,
    id_especialidad INT NOT NULL,
    
    CONSTRAINT fk_doctor_esp_doctor FOREIGN KEY (id_doctor) 
        REFERENCES usuarios(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_doctor_esp_especialidad FOREIGN KEY (id_especialidad) 
        REFERENCES especialidades(id_especialidad) ON DELETE CASCADE,
    CONSTRAINT unique_doctor_especialidad UNIQUE (id_doctor, id_especialidad),
    
    INDEX idx_doctor (id_doctor),
    INDEX idx_especialidad (id_especialidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Tabla: `imagenes_medicas`

```sql
CREATE TABLE imagenes_medicas (
    id_imagen INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    ruta_imagen VARCHAR(255) NOT NULL,
    categoria VARCHAR(100),
    nombre_archivo VARCHAR(255) NOT NULL,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    descripcion TEXT,
    tipo_archivo VARCHAR(50),
    
    CONSTRAINT fk_imagen_usuario FOREIGN KEY (id_usuario) 
        REFERENCES usuarios(user_id) ON DELETE CASCADE,
    
    INDEX idx_usuario (id_usuario),
    INDEX idx_categoria (categoria),
    INDEX idx_fecha (fecha_subida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Tabla: `servicios`

```sql
CREATE TABLE servicios (
    id_servicio INT PRIMARY KEY AUTO_INCREMENT,
    nombre_servicio VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    precio DECIMAL(10,2) DEFAULT 0.00,
    duracion_estimada INT DEFAULT 60,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Tabla: `configuracion_pagos`

```sql
CREATE TABLE configuracion_pagos (
    id_config INT PRIMARY KEY AUTO_INCREMENT,
    nombre_banco VARCHAR(100) NOT NULL,
    numero_cuenta VARCHAR(50) NOT NULL,
    tipo_cuenta VARCHAR(50) NOT NULL,
    titular_cuenta VARCHAR(200) NOT NULL,
    cedula_titular VARCHAR(20) NOT NULL,
    email_notificaciones VARCHAR(100),
    telefono_contacto VARCHAR(15),
    activo BOOLEAN DEFAULT 1,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### Tabla: `genero`

```sql
CREATE TABLE genero (
    id_genero INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos iniciales
INSERT INTO genero (id_genero, nombre) VALUES
(1, 'Masculino'),
(2, 'Femenino');
```

#### Tabla: `user_type`

```sql
CREATE TABLE user_type (
    id_tipo INT PRIMARY KEY AUTO_INCREMENT,
    tipo VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Datos iniciales
INSERT INTO user_type (id_tipo, tipo) VALUES
(1, 'Doctor'),
(2, 'Enfermera'),
(3, 'Paciente'),
(4, 'Administrador');
```

---

## 5.3 Diccionario de Datos Completo

### Tabla: `usuarios`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **user_id** | INT | NO | PK | AUTO_INCREMENT | Identificador único del usuario |
| **nombre** | VARCHAR(100) | NO | - | - | Nombre(s) del usuario |
| **apellido** | VARCHAR(100) | NO | - | - | Apellido(s) del usuario |
| **telefono** | VARCHAR(15) | NO | UNIQUE | - | Número telefónico único, usado como username |
| **email** | VARCHAR(100) | YES | UNIQUE | NULL | Correo electrónico único (opcional) |
| **password** | VARCHAR(255) | NO | - | - | Contraseña hasheada con bcrypt |
| **edad** | INT | NO | - | - | Edad del usuario (validado: 18-120) |
| **id_genero** | INT | NO | FK | - | Referencia a tabla `genero` |
| **rol** | INT | NO | FK | 3 | Referencia a tabla `user_type` (1=Doctor, 2=Enfermera, 3=Paciente, 4=Admin) |
| **fecha_registro** | DATETIME | NO | - | CURRENT_TIMESTAMP | Fecha y hora de registro en el sistema |
| **ultimo_acceso** | DATETIME | YES | - | NULL | Última vez que el usuario inició sesión |

**Índices:**
- `PRIMARY KEY` en `user_id`
- `UNIQUE KEY` en `telefono`
- `UNIQUE KEY` en `email`
- `INDEX` en `rol`
- `FOREIGN KEY` `id_genero` → `genero(id_genero)`
- `FOREIGN KEY` `rol` → `user_type(id_tipo)`

**Reglas de negocio:**
- El teléfono debe ser único en toda la tabla
- El email debe ser único si se proporciona
- La contraseña debe ser hasheada con `password_hash()` antes de insertar
- `rol=3` (Paciente) es el valor por defecto para nuevos registros
- Solo usuarios con `rol=1` (Doctor) pueden tener especialidades asociadas

---

### Tabla: `citas`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **id_cita** | INT | NO | PK | AUTO_INCREMENT | Identificador único de la cita |
| **id_usuario** | INT | NO | FK | - | ID del paciente que reservó la cita |
| **id_doctor** | INT | NO | FK | - | ID del doctor que atenderá |
| **fecha** | DATE | NO | - | - | Fecha de la cita (formato: YYYY-MM-DD) |
| **horario** | TIME | NO | - | - | Hora de la cita (formato: HH:MM:SS) |
| **servicio** | VARCHAR(100) | NO | - | - | Nombre del servicio (dermatología, podología, tamiz) |
| **estado** | VARCHAR(50) | NO | - | 'pendiente_pago' | Estado actual: pendiente_pago, pendiente, completada, cancelada, rechazada |
| **observaciones** | TEXT | YES | - | NULL | Notas médicas sobre la cita (solo doctor) |
| **monto** | DECIMAL(10,2) | NO | - | 0.00 | Costo de la cita en Bs. |
| **requiere_pago** | BOOLEAN | NO | - | 1 | Si la cita requiere pago previo |
| **comprobante_pago** | VARCHAR(255) | YES | - | NULL | Ruta al archivo del comprobante |
| **fecha_pago** | DATETIME | YES | - | NULL | Fecha y hora cuando se subió el comprobante |
| **verificado_por** | INT | YES | FK | NULL | ID del doctor que verificó el pago |
| **fecha_verificacion** | DATETIME | YES | - | NULL | Fecha y hora de verificación del pago |
| **notas_pago** | TEXT | YES | - | NULL | Notas del doctor sobre el comprobante |
| **fecha_creacion** | DATETIME | NO | - | CURRENT_TIMESTAMP | Fecha de creación del registro |
| **actualizado** | DATETIME | NO | - | CURRENT_TIMESTAMP | Última actualización |

**Índices:**
- `PRIMARY KEY` en `id_cita`
- `INDEX` en `fecha`
- `INDEX` en `id_usuario`
- `INDEX` en `id_doctor`
- `INDEX` en `estado`
- `INDEX` en `servicio`
- `INDEX` compuesto en `(fecha, horario)`
- `FOREIGN KEY` `id_usuario` → `usuarios(user_id)` ON DELETE CASCADE
- `FOREIGN KEY` `id_doctor` → `usuarios(user_id)` ON DELETE CASCADE
- `FOREIGN KEY` `verificado_por` → `usuarios(user_id)` ON DELETE SET NULL

**Reglas de negocio:**
- Una cita solo puede existir si tanto el paciente como el doctor existen
- El horario debe coincidir con un slot disponible del doctor en tabla `horarios`
- El servicio debe coincidir con una de las especialidades del doctor
- Estados válidos: `pendiente_pago` (inicial), `pendiente` (pago verificado), `completada`, `cancelada`, `rechazada`
- Solo se puede cancelar una cita con al menos 24 horas de anticipación
- El campo `observaciones` solo es editable por el doctor

---

### Tabla: `horarios`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **id_horario** | INT | NO | PK | AUTO_INCREMENT | Identificador único del horario |
| **id_doctor** | INT | NO | FK | - | ID del doctor dueño del horario |
| **dia_semana** | ENUM | NO | UNIQUE | - | Día de la semana (lunes-domingo) |
| **hora_inicio** | TIME | NO | - | - | Hora de inicio de atención (HH:MM:SS) |
| **hora_fin** | TIME | NO | - | - | Hora de fin de atención (HH:MM:SS) |
| **intervalo_minutos** | INT | NO | - | 60 | Duración de cada slot en minutos (30/45/60/90/120) |
| **activo** | BOOLEAN | NO | - | 1 | Si el horario está activo (1) o deshabilitado (0) |
| **fecha_creacion** | DATETIME | NO | - | CURRENT_TIMESTAMP | Fecha de creación del horario |
| **actualizado** | DATETIME | NO | - | CURRENT_TIMESTAMP | Última actualización |

**Índices:**
- `PRIMARY KEY` en `id_horario`
- `UNIQUE KEY` en `(id_doctor, dia_semana)` (un doctor solo puede tener un horario por día)
- `INDEX` en `id_doctor`
- `INDEX` en `dia_semana`
- `INDEX` en `activo`
- `FOREIGN KEY` `id_doctor` → `usuarios(user_id)` ON DELETE CASCADE

**Reglas de negocio:**
- `hora_fin` debe ser mayor que `hora_inicio`
- `intervalo_minutos` debe ser un valor positivo y divisor razonable de la jornada
- Un doctor solo puede tener un horario por día de la semana (constraint UNIQUE)
- Si `activo=0`, el horario no se mostrará en el sistema de reservas pero permanece en BD
- El sistema calcula slots disponibles dividiendo (hora_fin - hora_inicio) / intervalo_minutos

---

### Tabla: `bloqueos_horarios`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **id_bloqueo** | INT | NO | PK | AUTO_INCREMENT | Identificador único del bloqueo |
| **id_doctor** | INT | NO | FK | - | ID del doctor que bloquea fechas |
| **fecha_inicio** | DATE | NO | - | - | Fecha de inicio del bloqueo |
| **fecha_fin** | DATE | NO | - | - | Fecha de fin del bloqueo |
| **motivo** | VARCHAR(255) | YES | - | NULL | Razón del bloqueo |
| **tipo_bloqueo** | ENUM | NO | - | 'otro' | Tipo: vacaciones, conferencia, personal, otro |
| **notas** | TEXT | YES | - | NULL | Notas adicionales |
| **activo** | BOOLEAN | NO | - | 1 | Si el bloqueo está activo |
| **fecha_creacion** | DATETIME | NO | - | CURRENT_TIMESTAMP | Fecha de creación |
| **actualizado** | DATETIME | NO | - | CURRENT_TIMESTAMP | Última actualización |

**Índices:**
- `PRIMARY KEY` en `id_bloqueo`
- `INDEX` en `id_doctor`
- `INDEX` en `fecha_inicio`
- `INDEX` en `fecha_fin`
- `INDEX` en `activo`
- `FOREIGN KEY` `id_doctor` → `usuarios(user_id)` ON DELETE CASCADE

**Reglas de negocio:**
- `fecha_fin` debe ser mayor o igual a `fecha_inicio`
- Los bloqueos se aplican a TODO el día, no a horas específicas
- Al consultar disponibilidad, el sistema excluye fechas que caigan dentro de un bloqueo activo
- Puede haber múltiples bloqueos para un mismo doctor sin superposición

---

### Tabla: `especialidades`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **id_especialidad** | INT | NO | PK | AUTO_INCREMENT | Identificador único de la especialidad |
| **nombre_especialidad** | VARCHAR(100) | NO | UNIQUE | - | Nombre de la especialidad |
| **descripcion** | TEXT | YES | - | NULL | Descripción detallada de la especialidad |

**Índices:**
- `PRIMARY KEY` en `id_especialidad`
- `UNIQUE KEY` en `nombre_especialidad`

**Valores actuales:**
- `1` - Dermatología
- `2` - Podología
- `3` - Tamizaje

**Reglas de negocio:**
- Los nombres de especialidades deben ser únicos
- Esta tabla es catálogo fijo, no modificable por usuarios

---

### Tabla: `doctor_especialidades`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **id** | INT | NO | PK | AUTO_INCREMENT | Identificador único del registro |
| **id_doctor** | INT | NO | FK | - | ID del usuario con rol doctor |
| **id_especialidad** | INT | NO | FK | - | ID de la especialidad que tiene el doctor |

**Índices:**
- `PRIMARY KEY` en `id`
- `UNIQUE KEY` en `(id_doctor, id_especialidad)` (un doctor no puede tener duplicada una especialidad)
- `INDEX` en `id_doctor`
- `INDEX` en `id_especialidad`
- `FOREIGN KEY` `id_doctor` → `usuarios(user_id)` ON DELETE CASCADE
- `FOREIGN KEY` `id_especialidad` → `especialidades(id_especialidad)` ON DELETE CASCADE

**Reglas de negocio:**
- Solo usuarios con `rol=1` (Doctor) pueden tener registros aquí
- Un doctor puede tener múltiples especialidades
- Una especialidad puede estar asignada a múltiples doctores
- Al eliminar un doctor, se eliminan sus especialidades (CASCADE)
- Esta tabla implementa relación muchos-a-muchos entre `usuarios` y `especialidades`

---

### Tabla: `imagenes_medicas`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **id_imagen** | INT | NO | PK | AUTO_INCREMENT | Identificador único de la imagen |
| **id_usuario** | INT | NO | FK | - | ID del paciente dueño de la imagen |
| **ruta_imagen** | VARCHAR(255) | NO | - | - | Ruta relativa al archivo en servidor |
| **categoria** | VARCHAR(100) | YES | - | NULL | Categoría de la imagen (ej: "Rayos X", "Ecografía") |
| **nombre_archivo** | VARCHAR(255) | NO | - | - | Nombre original del archivo |
| **fecha_subida** | DATETIME | NO | - | CURRENT_TIMESTAMP | Fecha y hora de carga |
| **descripcion** | TEXT | YES | - | NULL | Descripción opcional de la imagen |
| **tipo_archivo** | VARCHAR(50) | YES | - | NULL | Tipo MIME (image/jpeg, image/png, etc.) |

**Índices:**
- `PRIMARY KEY` en `id_imagen`
- `INDEX` en `id_usuario`
- `INDEX` en `categoria`
- `INDEX` en `fecha_subida`
- `FOREIGN KEY` `id_usuario` → `usuarios(user_id)` ON DELETE CASCADE

**Reglas de negocio:**
- Solo pacientes (`rol=3`) y doctores (`rol=1`) pueden subir imágenes
- El archivo físico se guarda en `src/Images/ImgMedicas/{user_id}/`
- Formatos permitidos: JPG, JPEG, PNG, PDF
- Tamaño máximo: 10 MB por archivo
- Al eliminar un usuario, sus imágenes se eliminan de BD y del filesystem

---

### Tabla: `servicios`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **id_servicio** | INT | NO | PK | AUTO_INCREMENT | Identificador único del servicio |
| **nombre_servicio** | VARCHAR(100) | NO | UNIQUE | - | Nombre del servicio médico |
| **descripcion** | TEXT | YES | - | NULL | Descripción del servicio |
| **precio** | DECIMAL(10,2) | NO | - | 0.00 | Precio del servicio en Bs. |
| **duracion_estimada** | INT | NO | - | 60 | Duración estimada en minutos |
| **activo** | BOOLEAN | NO | - | 1 | Si el servicio está activo |
| **fecha_creacion** | DATETIME | NO | - | CURRENT_TIMESTAMP | Fecha de creación |

**Índices:**
- `PRIMARY KEY` en `id_servicio`
- `UNIQUE KEY` en `nombre_servicio`

**Reglas de negocio:**
- Los nombres de servicios deben ser únicos
- `duracion_estimada` se usa para calcular disponibilidad pero puede variar en práctica
- Tabla configurable por administradores

---

### Tabla: `configuracion_pagos`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **id_config** | INT | NO | PK | AUTO_INCREMENT | Identificador único de configuración |
| **nombre_banco** | VARCHAR(100) | NO | - | - | Nombre del banco |
| **numero_cuenta** | VARCHAR(50) | NO | - | - | Número de cuenta bancaria |
| **tipo_cuenta** | VARCHAR(50) | NO | - | - | Tipo de cuenta (Corriente, Ahorro) |
| **titular_cuenta** | VARCHAR(200) | NO | - | - | Nombre completo del titular |
| **cedula_titular** | VARCHAR(20) | NO | - | - | Cédula o RIF del titular |
| **email_notificaciones** | VARCHAR(100) | YES | - | NULL | Email para notificaciones de pago |
| **telefono_contacto** | VARCHAR(15) | YES | - | NULL | Teléfono de contacto |
| **activo** | BOOLEAN | NO | - | 1 | Si esta configuración está activa |
| **fecha_actualizacion** | DATETIME | NO | - | CURRENT_TIMESTAMP | Última actualización |

**Índices:**
- `PRIMARY KEY` en `id_config`

**Reglas de negocio:**
- Solo debe haber un registro activo (`activo=1`) a la vez
- Esta información se muestra a los pacientes al momento de pagar
- Solo administradores pueden modificar esta tabla
- Los cambios se registran en `fecha_actualizacion` automáticamente

---

### Tabla: `genero`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **id_genero** | INT | NO | PK | AUTO_INCREMENT | Identificador único del género |
| **nombre** | VARCHAR(50) | NO | UNIQUE | - | Nombre del género |

**Índices:**
- `PRIMARY KEY` en `id_genero`
- `UNIQUE KEY` en `nombre`

**Valores actuales:**
- `1` - Masculino
- `2` - Femenino

**Reglas de negocio:**
- Tabla de catálogo fija
- Se puede extender para incluir más opciones si es requerido

---

### Tabla: `user_type`

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|-------|------|------|-------|---------|-------------|
| **id_tipo** | INT | NO | PK | AUTO_INCREMENT | Identificador único del tipo de usuario |
| **tipo** | VARCHAR(50) | NO | UNIQUE | - | Nombre del tipo/rol |

**Índices:**
- `PRIMARY KEY` en `id_tipo`
- `UNIQUE KEY` en `tipo`

**Valores actuales:**
- `1` - Doctor
- `2` - Enfermera
- `3` - Paciente
- `4` - Administrador

**Reglas de negocio:**
- Tabla de catálogo fija
- El rol determina los permisos y accesos en el sistema
- No se debe eliminar ni modificar roles existentes

---

**FIN DE PARTE 2.2**

---

*Este documento continúa en:*
- **MANUAL_TECNICO_PARTE_3.md** (Estructura de Código Fuente, Módulos Críticos, Pruebas y Validación)

---

**Elaborado por:** Equipo de Desarrollo PropielEquipo  
**Revisión:** v1.0 - Noviembre 2025  
**Contacto Técnico:** desarrollo@propielequipo.com
