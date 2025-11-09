-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-11-2025 a las 22:53:40
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `propielequipo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(10) NOT NULL,
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
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_usuario`, `id_doctor`, `fecha`, `horario`, `id_horario`, `servicio`, `id_servicio`, `estado`, `notas`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(22, 18, NULL, '2025-07-30', '13:00', NULL, 'dermatología', NULL, 'pendiente', 'Pene grande', '2025-07-29 06:51:16', '2025-07-30 23:41:56'),
(23, 20, NULL, '2025-07-29', '13:00', NULL, 'dermatología', NULL, 'pendiente', 'Tonoto', '2025-07-29 07:06:56', '2025-07-31 00:06:08'),
(24, 18, NULL, '2025-08-07', '12:00', NULL, 'tamiz', NULL, 'pendiente', NULL, '2025-07-30 05:43:43', '2025-07-30 05:43:43'),
(25, 18, NULL, '2025-07-31', '14:00', NULL, 'podología', NULL, 'pendiente', 'gfdvsgf', '2025-07-30 05:47:00', '2025-07-31 02:47:41'),
(26, 18, NULL, '2025-08-08', '11:00', NULL, 'dermatología', NULL, 'pendiente', 'coito', '2025-07-30 23:42:48', '2025-07-30 23:43:09'),
(27, 20, NULL, '2025-08-08', '12:00', NULL, 'podología', NULL, 'pendiente', 'fjdiskfajññadlks', '2025-07-31 00:07:19', '2025-07-31 02:47:36'),
(28, 18, NULL, '2025-08-01', '14:00', NULL, 'dermatología', NULL, 'pendiente', NULL, '2025-07-31 00:21:39', '2025-07-31 00:21:39'),
(30, 25, NULL, '2025-08-01', '15:00', NULL, 'podología', NULL, 'pendiente', NULL, '2025-07-31 06:20:51', '2025-07-31 06:20:51'),
(32, 26, NULL, '2025-08-20', '11:00', NULL, 'dermatología', NULL, 'pendiente', NULL, '2025-07-31 07:08:32', '2025-07-31 07:08:32'),
(34, 24, NULL, '2025-08-01', '17:00', NULL, 'dermatología', NULL, 'pendiente', NULL, '2025-07-31 08:31:54', '2025-07-31 08:31:54'),
(36, 18, NULL, '2025-11-14', '16:00', NULL, 'dermatología', NULL, 'pendiente', NULL, '2025-11-01 23:29:35', '2025-11-01 23:29:35'),
(37, 18, NULL, '2025-11-20', '17:00', NULL, 'tamiz', NULL, 'pendiente', NULL, '2025-11-01 23:32:07', '2025-11-01 23:32:07'),
(38, 18, 23, '2025-11-21', '15:00', NULL, 'tamiz', NULL, 'pendiente', NULL, '2025-11-02 20:34:01', '2025-11-02 20:34:01'),
(39, 18, 19, '2025-11-28', '11:00', NULL, 'dermatología', NULL, 'pendiente', NULL, '2025-11-02 20:39:58', '2025-11-02 20:39:58'),
(40, 18, 21, '2025-11-25', '17:00', NULL, 'podología', NULL, 'pendiente', NULL, '2025-11-02 20:40:45', '2025-11-02 20:40:45'),
(41, 18, 22, '2025-11-07', '12:00', NULL, 'tamiz', NULL, 'pendiente', NULL, '2025-11-03 00:53:37', '2025-11-03 00:53:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doctor_especialidades`
--

CREATE TABLE `doctor_especialidades` (
  `id` int(10) NOT NULL,
  `id_doctor` int(255) NOT NULL,
  `id_especialidad` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `doctor_especialidades`
--

INSERT INTO `doctor_especialidades` (`id`, `id_doctor`, `id_especialidad`) VALUES
(1, 19, 1),
(2, 21, 2),
(3, 22, 3),
(4, 23, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `id_especialidad` int(10) NOT NULL,
  `nombre_especialidad` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`id_especialidad`, `nombre_especialidad`, `descripcion`) VALUES
(1, 'Dermatología', 'Especialista en piel y enfermedades cutáneas'),
(2, 'Podología', 'Especialista en cuidado y tratamiento de pies'),
(3, 'Tamizaje', 'Tamizajes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero`
--

CREATE TABLE `genero` (
  `id_genero` int(1) NOT NULL,
  `genero` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `genero`
--

INSERT INTO `genero` (`id_genero`, `genero`) VALUES
(1, 'Hombre'),
(2, 'Mujer'),
(3, 'Otro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id_horario` int(10) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `disponible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_horario`, `hora_inicio`, `hora_fin`, `disponible`) VALUES
(1, '09:00:00', '09:30:00', 1),
(2, '10:00:00', '10:30:00', 1),
(3, '11:00:00', '11:30:00', 1),
(4, '12:00:00', '12:30:00', 1),
(5, '13:00:00', '13:30:00', 1),
(6, '14:00:00', '14:30:00', 1),
(7, '15:00:00', '15:30:00', 1),
(8, '16:00:00', '16:30:00', 1),
(9, '17:00:00', '17:30:00', 1),
(10, '09:00:00', '09:30:00', 1),
(11, '10:00:00', '10:30:00', 1),
(12, '11:00:00', '11:30:00', 1),
(13, '12:00:00', '12:30:00', 1),
(14, '13:00:00', '13:30:00', 1),
(15, '14:00:00', '14:30:00', 1),
(16, '15:00:00', '15:30:00', 1),
(17, '16:00:00', '16:30:00', 1),
(18, '17:00:00', '17:30:00', 1),
(19, '09:00:00', '09:30:00', 1),
(20, '10:00:00', '10:30:00', 1),
(21, '11:00:00', '11:30:00', 1),
(22, '12:00:00', '12:30:00', 1),
(23, '13:00:00', '13:30:00', 1),
(24, '14:00:00', '14:30:00', 1),
(25, '15:00:00', '15:30:00', 1),
(26, '16:00:00', '16:30:00', 1),
(27, '17:00:00', '17:30:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_medicas`
--

CREATE TABLE `imagenes_medicas` (
  `id_imagen` int(10) NOT NULL,
  `id_paciente` int(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_imagen` enum('dermatologia','podologia','tamiz','rayos_x','laboratorio','general') DEFAULT 'general',
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes_medicas`
--

INSERT INTO `imagenes_medicas` (`id_imagen`, `id_paciente`, `nombre_archivo`, `descripcion`, `tipo_imagen`, `fecha_subida`, `activo`) VALUES
(2, 18, 'paciente_18_1753770075_6888685b42bb1.jpg', 'Ronchas', 'dermatologia', '2025-07-29 06:21:15', 1),
(3, 18, 'paciente_18_1753770120_68886888a5237.jpg', 'Salpullido', 'dermatologia', '2025-07-29 06:22:00', 1),
(4, 18, 'paciente_18_1753770175_688868bfac781.webp', 'Caspa\r\n', 'dermatologia', '2025-07-29 06:22:55', 1),
(5, 18, 'paciente_18_1753770209_688868e111096.jpg', 'Keratosis', 'dermatologia', '2025-07-29 06:23:29', 1),
(6, 18, 'paciente_18_1753770236_688868fc970a3.jpeg', 'acne', 'dermatologia', '2025-07-29 06:23:56', 1),
(7, 18, 'paciente_18_1753770255_6888690f36c4b.jpeg', 'no se', 'dermatologia', '2025-07-29 06:24:15', 1),
(8, 18, 'paciente_18_1753770277_68886925317c4.jpg', 'Comezón', 'dermatologia', '2025-07-29 06:24:37', 1),
(9, 18, 'paciente_18_1753929234_688ad612b6ef2.webp', 'Piesito', 'podologia', '2025-07-31 02:33:54', 1),
(10, 18, 'paciente_18_1753929260_688ad62c56025.jpg', 'otro pie', 'podologia', '2025-07-31 02:34:20', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id_servicio` int(10) NOT NULL,
  `nombre_servicio` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion_minutos` int(3) DEFAULT 30,
  `precio` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id_servicio`, `nombre_servicio`, `descripcion`, `duracion_minutos`, `precio`, `activo`) VALUES
(1, 'dermatología', 'Consulta dermatológica general', 45, NULL, 1),
(2, 'podología', 'Tratamiento y cuidado de pies', 30, NULL, 1),
(3, 'tamiz', 'Tamizaje y evaluación médica', 20, NULL, 1),
(4, 'dermatología', 'Consulta dermatológica general', 45, NULL, 1),
(5, 'podología', 'Tratamiento y cuidado de pies', 30, NULL, 1),
(6, 'tamiz', 'Tamizaje y evaluación médica', 20, NULL, 1),
(7, 'dermatología', 'Consulta dermatológica general', 45, NULL, 1),
(8, 'podología', 'Tratamiento y cuidado de pies', 30, NULL, 1),
(9, 'tamiz', 'Tamizaje y evaluación médica', 20, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_type`
--

CREATE TABLE `user_type` (
  `user_id` int(1) NOT NULL,
  `nivel` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_type`
--

INSERT INTO `user_type` (`user_id`, `nivel`) VALUES
(1, 'Doctor'),
(2, 'Enfermera'),
(3, 'Paciente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

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
  `cedula_profesional` varchar(20) DEFAULT NULL COMMENT 'Cédula profesional para médicos únicamente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`user_id`, `rol`, `nombre`, `apellido`, `edad`, `telefono`, `email`, `password`, `genero`, `cedula_profesional`) VALUES
(18, 3, 'Juan', 'Tellez', 26, '1231231234', NULL, '$2y$10$UFxQe8jWj7IqwQQwsatd8uVA0Xl5lanUX0uK3xaptiBu1DyoKmNfK', 1, NULL),
(19, 1, 'Hugo', 'Alarcón', 50, '7581000101', NULL, '$2y$10$1TfhJt2iTTrVQAaJXsnOne7eYZRws4Nz9ShNwJ/pZdozIlKDf02I.', 1, '6519843'),
(20, 3, 'Rey', 'Davic', 22, '1234567890', NULL, '$2y$10$D32a6ySWOCxZruxo7A6Nmu.wawM4RzPjFrFmCwO3QRGlUOkaqbNUC', 1, NULL),
(21, 1, 'Maria', 'Castro', 35, '7581000202', NULL, '$2y$10$uW.6YtNCfkGB29cOp6eyiek4pPoiPxi0ioPEbedqa3zUESqW72Y/.', 2, '4950379'),
(22, 1, 'Mario', 'Alarcon', 30, '7581000303', NULL, '$2y$10$GEdfdmW3xsIBuaNRLyQmDOUBPGJS9WOnxMCmUbU12VQQbn57yERiq', 1, '8561973'),
(23, 1, 'Pablo', 'Cortez', 35, '7581000404', NULL, '$2y$10$102EO.pg3NqhugD4qg/qCuu02zhUYnjvjboqX4kQPjM7g1SFKdLvS', 1, '6438792'),
(24, 3, 'Marco', 'Padilla', 30, '7581000505', NULL, '$2y$10$xp2DedoJdu.aDefH92X//.e/K2ax6Jm4NW3mMeuWFP/Yi4gjVj9va', 1, NULL),
(25, 3, 'adasd', 'fsafas', 45, '7581000606', NULL, '$2y$10$dOkB42BHJw661vImsPCXLeROA83KbnBUYUPNrYU3G.sBlK6KaCPwK', 2, NULL),
(26, 3, 'Juan', 'Tellez', 26, '7581000707', NULL, '$2y$10$5SFeAYELyx3bm0oSf0XA7O1PkQJicZbniciLcOkk.SNtIBy9/CqPS', 1, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_citas_servicio` (`id_servicio`),
  ADD KEY `fk_citas_horario` (`id_horario`),
  ADD KEY `idx_citas_fecha` (`fecha`),
  ADD KEY `idx_citas_estado` (`estado`),
  ADD KEY `idx_citas_doctor` (`id_doctor`);

--
-- Indices de la tabla `doctor_especialidades`
--
ALTER TABLE `doctor_especialidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_doctor` (`id_doctor`),
  ADD KEY `id_especialidad` (`id_especialidad`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`id_especialidad`);

--
-- Indices de la tabla `genero`
--
ALTER TABLE `genero`
  ADD PRIMARY KEY (`id_genero`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id_horario`);

--
-- Indices de la tabla `imagenes_medicas`
--
ALTER TABLE `imagenes_medicas`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `idx_paciente_fecha` (`id_paciente`,`fecha_subida`),
  ADD KEY `idx_tipo_imagen` (`tipo_imagen`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id_servicio`);

--
-- Indices de la tabla `user_type`
--
ALTER TABLE `user_type`
  ADD PRIMARY KEY (`user_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `numero` (`telefono`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `rol` (`rol`),
  ADD KEY `sexo` (`genero`),
  ADD KEY `idx_usuarios_rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `doctor_especialidades`
--
ALTER TABLE `doctor_especialidades`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `id_especialidad` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `genero`
--
ALTER TABLE `genero`
  MODIFY `id_genero` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id_horario` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `imagenes_medicas`
--
ALTER TABLE `imagenes_medicas`
  MODIFY `id_imagen` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id_servicio` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `user_type`
--
ALTER TABLE `user_type`
  MODIFY `user_id` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `user_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_doctor` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_horario` FOREIGN KEY (`id_horario`) REFERENCES `horarios` (`id_horario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_citas_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `doctor_especialidades`
--
ALTER TABLE `doctor_especialidades`
  ADD CONSTRAINT `doctor_especialidades_ibfk_1` FOREIGN KEY (`id_doctor`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_especialidades_ibfk_2` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades` (`id_especialidad`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imagenes_medicas`
--
ALTER TABLE `imagenes_medicas`
  ADD CONSTRAINT `imagenes_medicas_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`genero`) REFERENCES `genero` (`id_genero`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`rol`) REFERENCES `user_type` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
