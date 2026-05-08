-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-05-2026 a las 17:41:57
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
-- Base de datos: `registronoticias`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `estado_id` int(11) NOT NULL,
  `estado_nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados`
--

INSERT INTO `estados` (`estado_id`, `estado_nombre`) VALUES
(6, 'Anulada'),
(1, 'Borrador'),
(5, 'Expirada'),
(2, 'Lista para validación'),
(3, 'Para corrección'),
(4, 'Publicada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_noticias`
--

CREATE TABLE `historial_noticias` (
  `historial_id` int(11) NOT NULL,
  `historial_noticia` int(11) NOT NULL,
  `historial_usuario` int(11) DEFAULT NULL,
  `historial_estadoAnterior` int(11) DEFAULT NULL,
  `historial_estadoPosterior` int(11) NOT NULL,
  `historial_fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `historial_observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias`
--

CREATE TABLE `noticias` (
  `noticia_id` int(11) NOT NULL,
  `noticia_titulo` varchar(100) NOT NULL,
  `noticia_descripcion` text NOT NULL,
  `noticia_autor` int(11) NOT NULL,
  `noticia_estado` int(11) NOT NULL,
  `noticia_fechaCreado` timestamp NOT NULL DEFAULT current_timestamp(),
  `noticia_fechaPublicado` date DEFAULT NULL,
  `noticia_imagen` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parametros`
--

CREATE TABLE `parametros` (
  `parametro_id` int(11) NOT NULL,
  `parametro_clave` varchar(100) NOT NULL,
  `parametro_valor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `parametros`
--

INSERT INTO `parametros` (`parametro_id`, `parametro_clave`, `parametro_valor`) VALUES
(3, 'dias_expiracion', 5),
(4, 'imagen_max_mb', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(3, 'Admin'),
(1, 'Editor'),
(2, 'Validador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_usuarios`
--

CREATE TABLE `rol_usuarios` (
  `usuario_rol_usuario` int(11) NOT NULL,
  `usuario_rol_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `contraseña_usuario` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`estado_id`),
  ADD UNIQUE KEY `estado_nombre` (`estado_nombre`);

--
-- Indices de la tabla `historial_noticias`
--
ALTER TABLE `historial_noticias`
  ADD PRIMARY KEY (`historial_id`),
  ADD KEY `historial_noticia` (`historial_noticia`,`historial_usuario`,`historial_estadoAnterior`,`historial_estadoPosterior`),
  ADD KEY `historial_usuario` (`historial_usuario`),
  ADD KEY `historial_estadoAnterior` (`historial_estadoAnterior`),
  ADD KEY `historial_estadoPosterior` (`historial_estadoPosterior`);

--
-- Indices de la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`noticia_id`),
  ADD KEY `noticia_autor` (`noticia_autor`,`noticia_estado`),
  ADD KEY `noticia_estado` (`noticia_estado`);

--
-- Indices de la tabla `parametros`
--
ALTER TABLE `parametros`
  ADD PRIMARY KEY (`parametro_id`),
  ADD UNIQUE KEY `parametro_clave` (`parametro_clave`),
  ADD KEY `parametro_valor` (`parametro_valor`) USING BTREE;

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `rol_usuarios`
--
ALTER TABLE `rol_usuarios`
  ADD PRIMARY KEY (`usuario_rol_usuario`,`usuario_rol_rol`),
  ADD KEY `rol_usuario_ibfk_2` (`usuario_rol_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `estados`
--
ALTER TABLE `estados`
  MODIFY `estado_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `historial_noticias`
--
ALTER TABLE `historial_noticias`
  MODIFY `historial_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `noticias`
--
ALTER TABLE `noticias`
  MODIFY `noticia_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `parametros`
--
ALTER TABLE `parametros`
  MODIFY `parametro_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `historial_noticias`
--
ALTER TABLE `historial_noticias`
  ADD CONSTRAINT `historial_noticias_ibfk_1` FOREIGN KEY (`historial_noticia`) REFERENCES `noticias` (`noticia_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `historial_noticias_ibfk_2` FOREIGN KEY (`historial_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `historial_noticias_ibfk_3` FOREIGN KEY (`historial_estadoAnterior`) REFERENCES `estados` (`estado_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `historial_noticias_ibfk_4` FOREIGN KEY (`historial_estadoPosterior`) REFERENCES `estados` (`estado_id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `noticias_ibfk_1` FOREIGN KEY (`noticia_autor`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `noticias_ibfk_2` FOREIGN KEY (`noticia_estado`) REFERENCES `estados` (`estado_id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_usuarios`
--
ALTER TABLE `rol_usuarios`
  ADD CONSTRAINT `rol_usuarios_ibfk_1` FOREIGN KEY (`usuario_rol_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rol_usuarios_ibfk_2` FOREIGN KEY (`usuario_rol_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
