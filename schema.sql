-- phpMyAdmin SQL Dump
-- Schema ONLY - No User Data
-- Use this for setting up development environments

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: propielequipo

-- Table: citas
CREATE TABLE `citas` (
  `id_cita` int(10) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(10) NOT NULL,
  `id_doctor` int(10) DEFAULT NULL,
  `fecha` date NOT NULL,
  `horario` varchar(255) NOT NULL,
  `id_horario` int(10) DEFAULT NULL,
  `servicio` varchar(255) NOT NULL,
  `id_servicio` int(10) DEFAULT NULL,
  `estado` enum('pendiente','confirmada','completada','cancelada') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_cita`),
  KEY `id_usuario` (`id_usuario`),
  KEY `fk_citas_servicio` (`id_servicio`),
  KEY `fk_citas_horario` (`id_horario`),
  KEY `idx_citas_fecha` (`fecha`),
  KEY `idx_citas_estado` (`estado`),
  KEY `idx_citas_doctor` (`id_doctor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: doctor_especialidades
CREATE TABLE `doctor_especialidades` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_doctor` int(255) NOT NULL,
  `id_especialidad` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_doctor` (`id_doctor`),
  KEY `id_especialidad` (`id_especialidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: especialidades
CREATE TABLE `especialidades` (
  `id_especialidad` int(10) NOT NULL AUTO_INCREMENT,
  `nombre_especialidad` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_especialidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Reference data for especialidades (these are config, not user data)
INSERT INTO `especialidades` (`id_especialidad`, `nombre_especialidad`, `descripcion`) VALUES
(1, 'Dermatología', 'Especialista en piel y enfermedades cutáneas'),
(2, 'Podología', 'Especialista en cuidado y tratamiento de pies'),
(3, 'Tamizaje', 'Tamizajes');

-- Table: genero
CREATE TABLE `genero` (
  `id_genero` int(1) NOT NULL AUTO_INCREMENT,
  `genero` varchar(20) NOT NULL,
  PRIMARY KEY (`id_genero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Reference data for genero (config data)
INSERT INTO `genero` (`id_genero`, `genero`) VALUES
(1, 'Hombre'),
(2, 'Mujer'),
(3, 'Otro');

-- Table: horarios
CREATE TABLE `horarios` (
  `id_horario` int(10) NOT NULL AUTO_INCREMENT,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_horario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Reference data for horarios (config data)
INSERT INTO `horarios` (`id_horario`, `hora_inicio`, `hora_fin`, `disponible`) VALUES
(1, '09:00:00', '09:30:00', 1),
(2, '10:00:00', '10:30:00', 1),
(3, '11:00:00', '11:30:00', 1),
(4, '12:00:00', '12:30:00', 1),
(5, '13:00:00', '13:30:00', 1),
(6, '14:00:00', '14:30:00', 1),
(7, '15:00:00', '15:30:00', 1),
(8, '16:00:00', '16:30:00', 1),
(9, '17:00:00', '17:30:00', 1);

-- Table: imagenes_medicas
CREATE TABLE `imagenes_medicas` (
  `id_imagen` int(10) NOT NULL AUTO_INCREMENT,
  `id_paciente` int(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_imagen` enum('dermatologia','podologia','tamiz','rayos_x','laboratorio','general') DEFAULT 'general',
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_imagen`),
  KEY `idx_paciente_fecha` (`id_paciente`,`fecha_subida`),
  KEY `idx_tipo_imagen` (`tipo_imagen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: servicios
CREATE TABLE `servicios` (
  `id_servicio` int(10) NOT NULL AUTO_INCREMENT,
  `nombre_servicio` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion_minutos` int(3) DEFAULT 30,
  `precio` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: user_type
CREATE TABLE `user_type` (
  `user_id` int(1) NOT NULL AUTO_INCREMENT,
  `nivel` varchar(20) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Reference data for user_type (config data)
INSERT INTO `user_type` (`user_id`, `nivel`) VALUES
(1, 'Doctor'),
(2, 'Enfermera'),
(3, 'Paciente');

-- Table: usuarios
CREATE TABLE `usuarios` (
  `user_id` int(255) NOT NULL AUTO_INCREMENT,
  `rol` int(1) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `edad` int(3) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `genero` int(1) NOT NULL,
  `cedula_profesional` varchar(20) DEFAULT NULL COMMENT 'Cédula profesional para médicos únicamente',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `numero` (`telefono`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `rol` (`rol`),
  KEY `sexo` (`genero`),
  KEY `idx_usuarios_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Foreign Keys
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_doctor` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_horario` FOREIGN KEY (`id_horario`) REFERENCES `horarios` (`id_horario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `doctor_especialidades`
  ADD CONSTRAINT `doctor_especialidades_ibfk_1` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_especialidades_ibfk_2` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`) ON DELETE CASCADE;

ALTER TABLE `imagenes_medicas`
  ADD CONSTRAINT `imagenes_medicas_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`genero`) REFERENCES `genero` (`id_genero`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`rol`) REFERENCES `user_type` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

-- Note: This file contains ONLY the database structure
-- No real user data is included
-- Use tests/seed_database.sql to populate with test data
