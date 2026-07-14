-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-08-2022 a las 00:45:32
-- Versión del servidor: 10.4.24-MariaDB
-- Versión de PHP: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `agaeweb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `afiliados_auditoria`
--

CREATE TABLE `afiliados_auditoria` (
  `id` int(11) NOT NULL,
  `id_afiliado` int(11) NOT NULL,
  `tipo_auditoria` text NOT NULL,
  `usuario` int(11) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `afiliados_auditoria`
--

INSERT INTO `afiliados_auditoria` (`id`, `id_afiliado`, `tipo_auditoria`, `usuario`, `fecha`) VALUES
(1, 31, 'A', 0, '2022-08-17'),
(2, 33, 'A', 0, '2022-08-17');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `afiliados_auditoria`
--
ALTER TABLE `afiliados_auditoria`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `afiliados_auditoria`
--
ALTER TABLE `afiliados_auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
