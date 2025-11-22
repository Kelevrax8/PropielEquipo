-- Seed Development Database with Test Data
-- This creates fake patients, doctors, and appointments
-- Run: mysql -u root -p propielequipo < tests/seed_database.sql
-- 
-- CREDENTIALS FOR TESTING:
-- Username: Phone number (e.g., 0000000001)
-- Password: test123 (for all users)
-- 
-- ADMIN CREDENTIALS:
-- Username: admin
-- Password: admin123

-- Insert admin user (if not exists)
INSERT INTO usuarios (rol, nombre, apellido, edad, telefono, email, password, genero) VALUES
(4, 'Administrador', 'Sistema', 30, 'admin', 'admin@propielequipo.com', '$2y$10$6N.eGzyyWqPk9./weg4yCulaiMuNZ0N0ZsWy/JdJKZpFRj.ix9vNG', 1)
ON DUPLICATE KEY UPDATE telefono = telefono;

-- Insert test doctors (3 per specialty)
-- Password for all: test123
INSERT INTO usuarios (rol, nombre, apellido, edad, telefono, password, genero, cedula_profesional) VALUES
-- Dermatología doctors
(1, 'Carlos', 'Mendoza', 45, '0000000001', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 1, '1234567'),
(1, 'Ana', 'Rodriguez', 38, '0000000002', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 2, '2345678'),
(1, 'Roberto', 'Silva', 52, '0000000003', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 1, '3456789'),
-- Podología doctors
(1, 'Laura', 'Martinez', 41, '0000000004', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 2, '4567890'),
(1, 'Miguel', 'Gonzalez', 47, '0000000005', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 1, '5678901'),
(1, 'Sofia', 'Torres', 35, '0000000006', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 2, '6789012'),
-- Tamizaje doctors
(1, 'Jorge', 'Ramirez', 43, '0000000007', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 1, '7890123'),
(1, 'Patricia', 'Flores', 39, '0000000008', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 2, '8901234'),
(1, 'Fernando', 'Castro', 50, '0000000009', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 1, '9012345');

-- Insert test patients (10 patients)
-- Password for all: test123
INSERT INTO usuarios (rol, nombre, apellido, edad, telefono, password, genero) VALUES
(3, 'Juan', 'Perez', 28, '0000000010', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 1),
(3, 'Maria', 'Lopez', 34, '0000000011', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 2),
(3, 'Pedro', 'Garcia', 45, '0000000012', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 1),
(3, 'Carmen', 'Hernandez', 29, '0000000013', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 2),
(3, 'Luis', 'Morales', 52, '0000000014', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 1),
(3, 'Elena', 'Jimenez', 31, '0000000015', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 2),
(3, 'Ricardo', 'Vargas', 40, '0000000016', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 1),
(3, 'Diana', 'Ruiz', 26, '0000000017', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 2),
(3, 'Antonio', 'Ortiz', 38, '0000000018', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 1),
(3, 'Rosa', 'Sanchez', 33, '0000000019', '$2y$10$q10CzYLVdk0rNidg6fv4DuNdAoI6zmUrWkxiPKnrTFzTOBWhsmLL.', 2);

-- Link doctors to their specialties
INSERT INTO doctor_especialidades (id_doctor, id_especialidad) VALUES
-- Dermatología (especialidad 1)
((SELECT user_id FROM usuarios WHERE telefono = '0000000001'), 1),
((SELECT user_id FROM usuarios WHERE telefono = '0000000002'), 1),
((SELECT user_id FROM usuarios WHERE telefono = '0000000003'), 1),
-- Podología (especialidad 2)
((SELECT user_id FROM usuarios WHERE telefono = '0000000004'), 2),
((SELECT user_id FROM usuarios WHERE telefono = '0000000005'), 2),
((SELECT user_id FROM usuarios WHERE telefono = '0000000006'), 2),
-- Tamizaje (especialidad 3)
((SELECT user_id FROM usuarios WHERE telefono = '0000000007'), 3),
((SELECT user_id FROM usuarios WHERE telefono = '0000000008'), 3),
((SELECT user_id FROM usuarios WHERE telefono = '0000000009'), 3);
