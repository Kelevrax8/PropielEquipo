-- ========================================
-- SCHEDULE MANAGEMENT SYSTEM
-- Add dynamic schedule support for doctors
-- ========================================

-- Table: horarios_doctor
-- Stores customizable schedules for each doctor
CREATE TABLE `horarios_doctor` (
  `id_horario_doctor` int(10) NOT NULL AUTO_INCREMENT,
  `id_doctor` int(255) NOT NULL COMMENT 'ID del doctor',
  `dia_semana` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL COMMENT 'Día de la semana',
  `hora_inicio` time NOT NULL COMMENT 'Hora de inicio del turno',
  `hora_fin` time NOT NULL COMMENT 'Hora de fin del turno',
  `intervalo_minutos` int(3) DEFAULT 60 COMMENT 'Duración de cada cita en minutos',
  `activo` tinyint(1) DEFAULT 1 COMMENT 'Si el horario está activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_horario_doctor`),
  KEY `idx_doctor_dia` (`id_doctor`, `dia_semana`),
  KEY `idx_activo` (`activo`),
  CONSTRAINT `fk_horarios_doctor` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: bloqueos_horarios
-- Allows doctors/admin to block specific dates or time slots
CREATE TABLE `bloqueos_horarios` (
  `id_bloqueo` int(10) NOT NULL AUTO_INCREMENT,
  `id_doctor` int(255) NOT NULL COMMENT 'ID del doctor',
  `fecha_inicio` date NOT NULL COMMENT 'Fecha de inicio del bloqueo',
  `fecha_fin` date NOT NULL COMMENT 'Fecha de fin del bloqueo',
  `hora_inicio` time DEFAULT NULL COMMENT 'Hora de inicio (NULL = todo el día)',
  `hora_fin` time DEFAULT NULL COMMENT 'Hora de fin (NULL = todo el día)',
  `motivo` varchar(255) DEFAULT NULL COMMENT 'Razón del bloqueo',
  `tipo_bloqueo` enum('vacaciones','conferencia','urgencia','personal','otro') DEFAULT 'otro',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_bloqueo`),
  KEY `idx_doctor_fecha` (`id_doctor`, `fecha_inicio`, `fecha_fin`),
  KEY `idx_activo` (`activo`),
  CONSTRAINT `fk_bloqueos_doctor` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ========================================
-- INSERT DEFAULT SCHEDULES
-- Standard business hours for all doctors
-- ========================================

-- Insert default schedules for existing doctors (if any)
-- This will create Monday-Friday, 9:00-18:00 schedules
-- Adjust as needed for your clinic

-- Example: Insert default schedule for all doctors with rol=1
INSERT INTO `horarios_doctor` (`id_doctor`, `dia_semana`, `hora_inicio`, `hora_fin`, `intervalo_minutos`, `activo`)
SELECT 
    user_id,
    dia,
    '09:00:00',
    '18:00:00',
    60,
    1
FROM usuarios
CROSS JOIN (
    SELECT 'lunes' as dia UNION ALL
    SELECT 'martes' UNION ALL
    SELECT 'miercoles' UNION ALL
    SELECT 'jueves' UNION ALL
    SELECT 'viernes'
) AS dias
WHERE rol = 1;

-- ========================================
-- EXAMPLE DATA
-- ========================================

-- Example: Block specific dates for vacation
-- INSERT INTO `bloqueos_horarios` (`id_doctor`, `fecha_inicio`, `fecha_fin`, `motivo`, `tipo_bloqueo`)
-- VALUES (1, '2025-12-20', '2025-12-31', 'Vacaciones de fin de año', 'vacaciones');

-- Example: Block specific hours for a conference
-- INSERT INTO `bloqueos_horarios` (`id_doctor`, `fecha_inicio`, `fecha_fin`, `hora_inicio`, `hora_fin`, `motivo`, `tipo_bloqueo`)
-- VALUES (1, '2025-11-25', '2025-11-25', '14:00:00', '18:00:00', 'Conferencia médica', 'conferencia');

COMMIT;
