-- Seed Development Database with Test Data
-- This creates fake patients, doctors, and appointments
-- Run: mysql -u root -p propielequipo < tests/seed_database.sql

-- Insert test patients
INSERT INTO usuarios (rol, nombre, apellido, edad, telefono, password, genero) VALUES
(3, 'Test', 'Patient1', 30, '0000000001', '$2y$10$test.hash.here', 1),
(3, 'Test', 'Patient2', 25, '0000000002', '$2y$10$test.hash.here', 2),
(3, 'Test', 'Patient3', 40, '0000000003', '$2y$10$test.hash.here', 1),
(3, 'Test', 'Patient4', 35, '0000000004', '$2y$10$test.hash.here', 2),
(3, 'Test', 'Patient5', 28, '0000000005', '$2y$10$test.hash.here', 1);

-- Insert test doctors (if not exists)
-- These should match your existing doctors or create new test ones

-- Insert test appointments
INSERT INTO citas (id_usuario, id_doctor, fecha, horario, servicio, estado, notas) VALUES
((SELECT user_id FROM usuarios WHERE telefono = '0000000001'), 19, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00', 'dermatología', 'pendiente', 'Test appointment 1'),
((SELECT user_id FROM usuarios WHERE telefono = '0000000002'), 21, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00', 'podología', 'pendiente', 'Test appointment 2'),
((SELECT user_id FROM usuarios WHERE telefono = '0000000003'), 22, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00', 'tamiz', 'pendiente', 'Test appointment 3');

-- Insert test medical images references
INSERT INTO imagenes_medicas (id_paciente, nombre_archivo, descripcion, tipo_imagen) VALUES
((SELECT user_id FROM usuarios WHERE telefono = '0000000001'), 'test_image_1.jpg', 'Test image for development', 'dermatologia'),
((SELECT user_id FROM usuarios WHERE telefono = '0000000002'), 'test_image_2.jpg', 'Test image for development', 'podologia'),
((SELECT user_id FROM usuarios WHERE telefono = '0000000003'), 'test_image_3.jpg', 'Test image for development', 'tamiz');

-- Note: Actual image/PDF files must be created separately
-- Run: php tests/generate_test_data.php
