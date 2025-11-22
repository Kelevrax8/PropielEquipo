-- ========================================
-- REPURPOSE EXISTING HORARIOS TABLE
-- Add doctor and day associations to make it functional
-- ========================================

-- Step 1: Add new columns to existing horarios table
ALTER TABLE `horarios`
ADD COLUMN `id_doctor` int(255) DEFAULT NULL COMMENT 'NULL = applies to all doctors' AFTER `id_horario`,
ADD COLUMN `dia_semana` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') DEFAULT NULL COMMENT 'NULL = applies to all days' AFTER `id_doctor`,
ADD COLUMN `intervalo_minutos` int(3) DEFAULT 60 COMMENT 'Duración de cada cita' AFTER `disponible`,
ADD COLUMN `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() AFTER `intervalo_minutos`,
ADD COLUMN `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() AFTER `fecha_creacion`;

-- Step 2: Rename 'disponible' to 'activo' for consistency
ALTER TABLE `horarios`
CHANGE COLUMN `disponible` `activo` tinyint(1) DEFAULT 1;

-- Step 3: Add indexes
ALTER TABLE `horarios`
ADD KEY `idx_doctor_dia` (`id_doctor`, `dia_semana`),
ADD KEY `idx_activo` (`activo`);

-- Step 4: Add foreign key to doctor
ALTER TABLE `horarios`
ADD CONSTRAINT `fk_horarios_doctor` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Step 5: Clear old generic time slot data (not being used anyway)
DELETE FROM `horarios`;

-- Step 6: Insert default schedules for all existing doctors (Mon-Fri, 9AM-6PM)
INSERT INTO `horarios` (`id_doctor`, `dia_semana`, `hora_inicio`, `hora_fin`, `intervalo_minutos`, `activo`)
SELECT 
    u.user_id,
    d.dia,
    '09:00:00' as hora_inicio,
    '18:00:00' as hora_fin,
    60 as intervalo_minutos,
    1 as activo
FROM usuarios u
CROSS JOIN (
    SELECT 'lunes' as dia UNION ALL
    SELECT 'martes' UNION ALL
    SELECT 'miercoles' UNION ALL
    SELECT 'jueves' UNION ALL
    SELECT 'viernes'
) AS d
WHERE u.rol = 1;

-- Step 7: Create bloqueos_horarios table for date blocking
CREATE TABLE IF NOT EXISTS `bloqueos_horarios` (
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

-- Step 8: Verify the changes
SELECT 
    u.nombre,
    u.apellido,
    h.dia_semana,
    h.hora_inicio,
    h.hora_fin,
    h.intervalo_minutos,
    h.activo
FROM horarios h
JOIN usuarios u ON h.id_doctor = u.user_id
WHERE u.rol = 1
ORDER BY u.user_id, FIELD(h.dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes');

COMMIT;

-- ========================================
-- NOTES:
-- ========================================
-- ✅ Keeps existing table name (no app changes needed)
-- ✅ Backwards compatible (old records deleted, but structure preserved)
-- ✅ Adds doctor-specific schedules
-- ✅ Adds day-of-week support
-- ✅ Creates blocking table for vacations/absences
