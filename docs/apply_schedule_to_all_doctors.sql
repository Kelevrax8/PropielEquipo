-- ========================================
-- BULK OPERATION: Apply same schedule to all doctors
-- Use this if you want all doctors to share the same hours
-- But keep the flexibility to customize later
-- ========================================

-- Example: Set all doctors to Mon-Fri 9AM-6PM, 60 min appointments

-- First, clear existing schedules (optional)
-- DELETE FROM horarios_doctor;

-- Apply standard schedule to all doctors (rol = 1)
INSERT INTO horarios_doctor (id_doctor, dia_semana, hora_inicio, hora_fin, intervalo_minutos, activo)
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
WHERE u.rol = 1
ON DUPLICATE KEY UPDATE
    hora_inicio = VALUES(hora_inicio),
    hora_fin = VALUES(hora_fin),
    intervalo_minutos = VALUES(intervalo_minutos),
    activo = VALUES(activo);

-- Verify results
SELECT 
    u.nombre,
    u.apellido,
    h.dia_semana,
    h.hora_inicio,
    h.hora_fin,
    h.intervalo_minutos
FROM horarios_doctor h
JOIN usuarios u ON h.id_doctor = u.user_id
WHERE u.rol = 1
ORDER BY u.user_id, FIELD(h.dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes');

-- ========================================
-- CUSTOMIZATION EXAMPLES
-- ========================================

-- Change business hours for ALL doctors
UPDATE horarios_doctor
SET hora_inicio = '08:00:00',
    hora_fin = '20:00:00'
WHERE dia_semana IN ('lunes', 'martes', 'miercoles', 'jueves', 'viernes');

-- Add Saturday hours for ALL doctors
INSERT INTO horarios_doctor (id_doctor, dia_semana, hora_inicio, hora_fin, intervalo_minutos, activo)
SELECT 
    user_id,
    'sabado',
    '09:00:00',
    '14:00:00',
    60,
    1
FROM usuarios
WHERE rol = 1;

-- Change appointment duration for ALL doctors
UPDATE horarios_doctor
SET intervalo_minutos = 45
WHERE dia_semana IN ('lunes', 'martes', 'miercoles', 'jueves', 'viernes');

COMMIT;
