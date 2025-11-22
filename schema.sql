-- phpMyAdmin SQL Dump
-- Schema ONLY - No User Data
-- Use this for setting up development environments

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: propielequipo

-- ========================================
-- STEP 1: CREATE ALL TABLES (WITHOUT FOREIGN KEYS)
-- ========================================

-- Table: genero
CREATE TABLE `genero` (
  `id_genero` int(1) NOT NULL,
  `genero` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: user_type
CREATE TABLE `user_type` (
  `user_id` int(1) NOT NULL,
  `nivel` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: especialidades
CREATE TABLE `especialidades` (
  `id_especialidad` int(10) NOT NULL,
  `nombre_especialidad` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: horarios (doctor schedules)
CREATE TABLE `horarios` (
  `id_horario` int(10) NOT NULL,
  `id_doctor` int(255) DEFAULT NULL COMMENT 'ID del doctor al que pertenece este horario',
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL COMMENT 'Día de la semana',
  `hora_inicio` time NOT NULL COMMENT 'Hora de inicio de atención',
  `hora_fin` time NOT NULL COMMENT 'Hora de fin de atención',
  `intervalo_minutos` int(3) DEFAULT 60 COMMENT 'Duración de cada cita en minutos',
  `activo` tinyint(1) DEFAULT 1 COMMENT 'Si el horario está activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: servicios
CREATE TABLE `servicios` (
  `id_servicio` int(10) NOT NULL,
  `nombre_servicio` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion_minutos` int(3) DEFAULT 30,
  `precio` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: usuarios
CREATE TABLE `usuarios` (
  `user_id` int(255) NOT NULL,
  `rol` int(1) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `edad` int(3) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `genero` int(1) NOT NULL,
  `cedula_profesional` varchar(20) DEFAULT NULL COMMENT 'Cédula profesional para médicos únicamente',
  `ultimo_acceso` timestamp NULL DEFAULT NULL COMMENT 'Último acceso al sistema'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: citas
CREATE TABLE `citas` (
  `id_cita` int(10) NOT NULL,
  `id_usuario` int(10) NOT NULL,
  `id_doctor` int(10) DEFAULT NULL,
  `fecha` date NOT NULL,
  `horario` varchar(255) NOT NULL,
  `id_horario` int(10) DEFAULT NULL,
  `servicio` varchar(255) NOT NULL,
  `id_servicio` int(10) DEFAULT NULL,
  `estado` enum('pendiente_pago','pendiente','confirmada','completada','cancelada','rechazada') DEFAULT 'pendiente_pago',
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `requiere_pago` tinyint(1) DEFAULT 1 COMMENT 'Si la cita requiere pago previo',
  `monto` decimal(10,2) DEFAULT 500.00 COMMENT 'Monto a pagar en MXN',
  `comprobante_pago` varchar(255) DEFAULT NULL COMMENT 'Ruta del archivo del comprobante',
  `fecha_pago` timestamp NULL DEFAULT NULL COMMENT 'Fecha cuando se subió el comprobante',
  `verificado_por` int(10) DEFAULT NULL COMMENT 'ID del doctor que verificó el pago',
  `fecha_verificacion` timestamp NULL DEFAULT NULL COMMENT 'Fecha de verificación del pago',
  `notas_pago` text DEFAULT NULL COMMENT 'Notas del doctor sobre el pago'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: doctor_especialidades
CREATE TABLE `doctor_especialidades` (
  `id` int(10) NOT NULL,
  `id_doctor` int(255) NOT NULL,
  `id_especialidad` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: imagenes_medicas
CREATE TABLE `imagenes_medicas` (
  `id_imagen` int(10) NOT NULL,
  `id_paciente` int(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_imagen` enum('dermatologia','podologia','tamiz','rayos_x','laboratorio','general') DEFAULT 'general',
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: configuracion_pagos
CREATE TABLE `configuracion_pagos` (
  `id` int(10) NOT NULL,
  `banco` varchar(100) NOT NULL COMMENT 'Nombre del banco',
  `titular` varchar(255) NOT NULL COMMENT 'Nombre del titular de la cuenta',
  `clabe` varchar(18) NOT NULL COMMENT 'CLABE interbancaria',
  `numero_cuenta` varchar(50) DEFAULT NULL COMMENT 'Número de cuenta',
  `referencia_info` text DEFAULT NULL COMMENT 'Instrucciones adicionales para la transferencia',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: bloqueos_horarios (schedule blocks for vacations, conferences, etc.)
CREATE TABLE `bloqueos_horarios` (
  `id_bloqueo` int(10) NOT NULL,
  `id_doctor` int(255) NOT NULL COMMENT 'ID del doctor',
  `tipo_bloqueo` enum('vacaciones','conferencia','personal','emergencia','otro') DEFAULT 'personal' COMMENT 'Tipo de bloqueo',
  `fecha_inicio` date NOT NULL COMMENT 'Fecha de inicio del bloqueo',
  `fecha_fin` date NOT NULL COMMENT 'Fecha de fin del bloqueo',
  `hora_inicio` time DEFAULT NULL COMMENT 'Hora de inicio (NULL = todo el día)',
  `hora_fin` time DEFAULT NULL COMMENT 'Hora de fin (NULL = todo el día)',
  `motivo` text DEFAULT NULL COMMENT 'Razón del bloqueo',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- STEP 2: INSERT CONFIGURATION DATA
-- ========================================

-- Reference data for genero (config data)
INSERT INTO `genero` (`id_genero`, `genero`) VALUES
(1, 'Hombre'),
(2, 'Mujer'),
(3, 'Otro');

-- Reference data for user_type (config data)
INSERT INTO `user_type` (`user_id`, `nivel`) VALUES
(1, 'Doctor'),
(2, 'Enfermera'),
(3, 'Paciente'),
(4, 'Admin');

-- Reference data for especialidades (these are config, not user data)
INSERT INTO `especialidades` (`id_especialidad`, `nombre_especialidad`, `descripcion`) VALUES
(1, 'Dermatología', 'Especialista en piel y enfermedades cutáneas'),
(2, 'Podología', 'Especialista en cuidado y tratamiento de pies'),
(3, 'Tamizaje', 'Tamizajes');

-- Default schedules for doctors (Monday-Friday, 9 AM - 6 PM, 60 min appointments)
-- This will create schedules for any existing doctors
INSERT INTO `horarios` (`id_doctor`, `dia_semana`, `hora_inicio`, `hora_fin`, `intervalo_minutos`, `activo`)
SELECT 
    user_id,
    dia_semana,
    '09:00:00',
    '18:00:00',
    60,
    1
FROM usuarios
CROSS JOIN (
    SELECT 'lunes' as dia_semana UNION ALL
    SELECT 'martes' UNION ALL
    SELECT 'miercoles' UNION ALL
    SELECT 'jueves' UNION ALL
    SELECT 'viernes'
) dias
WHERE rol = 1;

-- Reference data for configuracion_pagos (CHANGE THESE VALUES!)
INSERT INTO `configuracion_pagos` (`banco`, `titular`, `clabe`, `numero_cuenta`, `referencia_info`) VALUES
(
    'BBVA México', 
    'PropielEquipo S.A. de C.V.', 
    '012180001234567890',
    '0123456789',
    'Por favor incluye tu nombre completo y número de teléfono en el concepto de la transferencia.'
);

-- Usuario administrador por defecto (CAMBIAR CONTRASEÑA EN PRODUCCIÓN)
-- Usuario: admin | Contraseña: admin123
INSERT INTO `usuarios` (`rol`, `nombre`, `apellido`, `edad`, `telefono`, `email`, `password`, `genero`) VALUES
(4, 'Administrador', 'Sistema', 30, 'admin', 'admin@propielequipo.com', '$2y$10$6N.eGzyyWqPk9./weg4yCulaiMuNZ0N0ZsWy/JdJKZpFRj.ix9vNG', 1);
-- Nota: Contraseña hasheada para 'admin123'

-- ========================================
-- STEP 3: ADD PRIMARY KEYS AND INDICES
-- ========================================

-- Indices de la tabla `citas`
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_citas_servicio` (`id_servicio`),
  ADD KEY `fk_citas_horario` (`id_horario`),
  ADD KEY `idx_citas_fecha` (`fecha`),
  ADD KEY `idx_citas_estado` (`estado`),
  ADD KEY `idx_citas_doctor` (`id_doctor`),
  ADD KEY `idx_citas_pago` (`requiere_pago`, `estado`),
  ADD KEY `idx_citas_comprobante` (`comprobante_pago`);

-- Indices de la tabla `doctor_especialidades`
ALTER TABLE `doctor_especialidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_doctor` (`id_doctor`),
  ADD KEY `id_especialidad` (`id_especialidad`);

-- Indices de la tabla `especialidades`
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id_especialidad`);

-- Indices de la tabla `genero`
ALTER TABLE `genero`
  ADD PRIMARY KEY (`id_genero`);

-- Indices de la tabla `horarios`
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id_horario`),
  ADD KEY `idx_horarios_doctor_dia` (`id_doctor`,`dia_semana`),
  ADD KEY `idx_horarios_activo` (`activo`);

-- Indices de la tabla `bloqueos_horarios`
ALTER TABLE `bloqueos_horarios`
  ADD PRIMARY KEY (`id_bloqueo`),
  ADD KEY `idx_bloqueos_doctor` (`id_doctor`),
  ADD KEY `idx_bloqueos_fechas` (`fecha_inicio`,`fecha_fin`),
  ADD KEY `idx_bloqueos_activo` (`activo`);

-- Indices de la tabla `imagenes_medicas`
ALTER TABLE `imagenes_medicas`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `idx_paciente_fecha` (`id_paciente`,`fecha_subida`),
  ADD KEY `idx_tipo_imagen` (`tipo_imagen`);

-- Indices de la tabla `servicios`
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id_servicio`);

-- Indices de la tabla `configuracion_pagos`
ALTER TABLE `configuracion_pagos`
  ADD PRIMARY KEY (`id`);

-- Indices de la tabla `user_type`
ALTER TABLE `user_type`
  ADD PRIMARY KEY (`user_id`);

-- Indices de la tabla `usuarios`
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `numero` (`telefono`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `rol` (`rol`),
  ADD KEY `sexo` (`genero`),
  ADD KEY `idx_usuarios_rol` (`rol`);

-- ========================================
-- STEP 4: SET AUTO_INCREMENT VALUES
-- ========================================

ALTER TABLE `citas`
  MODIFY `id_cita` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `doctor_especialidades`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `especialidades`
  MODIFY `id_especialidad` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `genero`
  MODIFY `id_genero` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `horarios`
  MODIFY `id_horario` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bloqueos_horarios`
  MODIFY `id_bloqueo` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `imagenes_medicas`
  MODIFY `id_imagen` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `servicios`
  MODIFY `id_servicio` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `configuracion_pagos`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `user_type`
  MODIFY `user_id` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `usuarios`
  MODIFY `user_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- ========================================
-- STEP 5: ADD FOREIGN KEY CONSTRAINTS
-- ========================================

-- Filtros para la tabla `usuarios`
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`genero`) REFERENCES `genero` (`id_genero`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`rol`) REFERENCES `user_type` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Filtros para la tabla `citas`
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_doctor` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_horario` FOREIGN KEY (`id_horario`) REFERENCES `horarios` (`id_horario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_verificado_por` FOREIGN KEY (`verificado_por`) REFERENCES `usuarios` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Filtros para la tabla `doctor_especialidades`
ALTER TABLE `doctor_especialidades`
  ADD CONSTRAINT `doctor_especialidades_ibfk_1` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_especialidades_ibfk_2` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`) ON DELETE CASCADE;

-- Filtros para la tabla `imagenes_medicas`
ALTER TABLE `imagenes_medicas`
  ADD CONSTRAINT `imagenes_medicas_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Filtros para la tabla `horarios`
ALTER TABLE `horarios`
  ADD CONSTRAINT `fk_horarios_doctor` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Filtros para la tabla `bloqueos_horarios`
ALTER TABLE `bloqueos_horarios`
  ADD CONSTRAINT `fk_bloqueos_doctor` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
