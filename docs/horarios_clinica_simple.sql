-- ========================================
-- SIMPLIFIED SCHEDULE SYSTEM
-- For clinics where ALL doctors share the same hours
-- ========================================

-- Table: horarios_clinica (Global clinic hours)
CREATE TABLE `horarios_clinica` (
  `id_horario` int(10) NOT NULL AUTO_INCREMENT,
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `intervalo_minutos` int(3) DEFAULT 60 COMMENT 'Duración de cada cita',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_horario`),
  UNIQUE KEY `unique_dia` (`dia_semana`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: bloqueos_horarios (Per-doctor blocks - vacations, etc.)
CREATE TABLE `bloqueos_horarios` (
  `id_bloqueo` int(10) NOT NULL AUTO_INCREMENT,
  `id_doctor` int(255) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `hora_inicio` time DEFAULT NULL COMMENT 'NULL = todo el día',
  `hora_fin` time DEFAULT NULL COMMENT 'NULL = todo el día',
  `motivo` varchar(255) DEFAULT NULL,
  `tipo_bloqueo` enum('vacaciones','conferencia','urgencia','personal','otro') DEFAULT 'otro',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_bloqueo`),
  KEY `idx_doctor_fecha` (`id_doctor`, `fecha_inicio`, `fecha_fin`),
  CONSTRAINT `fk_bloqueos_doctor` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default clinic hours (Monday-Friday, 9AM-6PM)
INSERT INTO `horarios_clinica` (`dia_semana`, `hora_inicio`, `hora_fin`, `intervalo_minutos`) VALUES
('lunes', '09:00:00', '18:00:00', 60),
('martes', '09:00:00', '18:00:00', 60),
('miercoles', '09:00:00', '18:00:00', 60),
('jueves', '09:00:00', '18:00:00', 60),
('viernes', '09:00:00', '18:00:00', 60);

COMMIT;
