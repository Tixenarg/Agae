-- 1. Desactivar temporalmente la validación de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- 2. Borrar todos los registros de las 6 tablas relacionales
DELETE FROM afiliados_laborales;
DELETE FROM afiliados_educacion;
DELETE FROM afiliados_datos_cobro;
DELETE FROM afiliados_domicilios;
DELETE FROM afiliados_datos_personales;
DELETE FROM afiliados_maestra;

-- 3. Reiniciar el contador AUTO_INCREMENT a 1 en todas las tablas
ALTER TABLE afiliados_laborales AUTO_INCREMENT = 1;
ALTER TABLE afiliados_educacion AUTO_INCREMENT = 1;
ALTER TABLE afiliados_datos_cobro AUTO_INCREMENT = 1;
ALTER TABLE afiliados_domicilios AUTO_INCREMENT = 1;
ALTER TABLE afiliados_datos_personales AUTO_INCREMENT = 1;
ALTER TABLE afiliados_maestra AUTO_INCREMENT = 1;

-- 4. Volver a activar la validación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. TABLAS PARAMÉTRICAS (Catálogos)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `afiliado_estados` (
  `id_estado` INT(11) NOT NULL AUTO_INCREMENT,
  `estado_nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `afiliado_estados` (`id_estado`, `estado_nombre`) VALUES
(1, 'Solicitud de Afiliacion'),
(2, 'Afiliado'),
(3, 'Desafiliado');

CREATE TABLE IF NOT EXISTS `afiliado_forma_de_pago` (
  `id_fpago` INT(11) NOT NULL AUTO_INCREMENT,
  `fpago_nombre` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_fpago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `afiliado_forma_de_pago` (`id_fpago`, `fpago_nombre`) VALUES
(1, 'Débito automatico Bco Nación'),
(2, 'Mercado Pago'),
(3, 'Otros');

-- --------------------------------------------------------
-- 2. TABLAS NÚCLEO NORMALIZADAS
-- --------------------------------------------------------
CREATE TABLE `afiliados_maestra` (
  `id_afiliado` INT(11) NOT NULL AUTO_INCREMENT,
  `apellidos` VARCHAR(100) NOT NULL,
  `nombres` VARCHAR(100) NOT NULL,
  `dni` VARCHAR(20) NOT NULL,
  `telefono` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `id_estado` INT(11) NOT NULL DEFAULT 1,
  `fecha_solicitud` DATE DEFAULT NULL,
  PRIMARY KEY (`id_afiliado`),
  UNIQUE KEY `uk_dni` (`dni`),
  CONSTRAINT `fk_afiliado_estado` FOREIGN KEY (`id_estado`) REFERENCES `afiliado_estados` (`id_estado`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `afiliados_datos_personales` (
  `id_personales` INT(11) NOT NULL AUTO_INCREMENT,
  `id_afiliado` INT(11) NOT NULL,
  `cuil` VARCHAR(20) DEFAULT NULL,
  `nacionalidad` VARCHAR(50) DEFAULT NULL,
  `sexo` VARCHAR(20) DEFAULT NULL,
  `estado_civil` VARCHAR(50) DEFAULT NULL,
  `fecha_nacimiento` DATE DEFAULT NULL,
  PRIMARY KEY (`id_personales`),
  CONSTRAINT `fk_personales_afiliado` FOREIGN KEY (`id_afiliado`) REFERENCES `afiliados_maestra` (`id_afiliado`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `afiliados_domicilios` (
  `id_domicilio` INT(11) NOT NULL AUTO_INCREMENT,
  `id_afiliado` INT(11) NOT NULL,
  `direccion` VARCHAR(255) DEFAULT NULL,
  `localidad` VARCHAR(100) DEFAULT NULL,
  `provincia` VARCHAR(100) DEFAULT NULL,
  `codigo_postal` VARCHAR(20) DEFAULT NULL,
  `es_principal` TINYINT(1) DEFAULT 1 COMMENT '1 = Domicilio activo/principal',
  PRIMARY KEY (`id_domicilio`),
  CONSTRAINT `fk_domicilio_afiliado` FOREIGN KEY (`id_afiliado`) REFERENCES `afiliados_maestra` (`id_afiliado`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `afiliados_datos_cobro` (
  `id_dato_cobro` INT(11) NOT NULL AUTO_INCREMENT,
  `id_afiliado` INT(11) NOT NULL,
  `id_fpago` INT(11) DEFAULT NULL,
  `numero_cuenta` VARCHAR(100) DEFAULT NULL,
  `acepto_pago` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id_dato_cobro`),
  CONSTRAINT `fk_cobro_afiliado` FOREIGN KEY (`id_afiliado`) REFERENCES `afiliados_maestra` (`id_afiliado`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cobro_fpago` FOREIGN KEY (`id_fpago`) REFERENCES `afiliado_forma_de_pago` (`id_fpago`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `afiliados_educacion` (
  `id_educacion` INT(11) NOT NULL AUTO_INCREMENT,
  `id_afiliado` INT(11) NOT NULL,
  `nivel_estudio` VARCHAR(100) DEFAULT NULL,
  `titulo` VARCHAR(150) DEFAULT NULL,
  PRIMARY KEY (`id_educacion`),
  CONSTRAINT `fk_educacion_afiliado` FOREIGN KEY (`id_afiliado`) REFERENCES `afiliados_maestra` (`id_afiliado`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `afiliados_laborales` (
  `id_laboral` INT(11) NOT NULL AUTO_INCREMENT,
  `id_afiliado` INT(11) NOT NULL,
  `legajo` VARCHAR(50) DEFAULT NULL,
  `org_liquida_haber` VARCHAR(150) DEFAULT NULL,
  `org_trabaja` VARCHAR(150) DEFAULT NULL,
  `domicilio_trabajo` VARCHAR(255) DEFAULT NULL,
  `localidad_trabajo` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id_laboral`),
  CONSTRAINT `fk_laboral_afiliado` FOREIGN KEY (`id_afiliado`) REFERENCES `afiliados_maestra` (`id_afiliado`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Normalizar SEXO a 'M' y 'F'
UPDATE afiliados_datos_personales 
SET sexo = 'F' 
WHERE LOWER(sexo) IN ('femenino', 'f', 'fem');

UPDATE afiliados_datos_personales 
SET sexo = 'M' 
WHERE LOWER(sexo) IN ('masculino', 'm', 'masc');

-- 2. Normalizar ESTADO CIVIL a los 4 formatos estándar
UPDATE afiliados_datos_personales 
SET estado_civil = 'Casado/a' 
WHERE LOWER(estado_civil) LIKE '%casad%';

UPDATE afiliados_datos_personales 
SET estado_civil = 'Soltero/a' 
WHERE LOWER(estado_civil) LIKE '%solter%';

UPDATE afiliados_datos_personales 
SET estado_civil = 'Divorciado/a' 
WHERE LOWER(estado_civil) LIKE '%divorci%';

UPDATE afiliados_datos_personales 
SET estado_civil = 'Viudo/a' 
WHERE LOWER(estado_civil) LIKE '%viud%';

-- 3. Normalizar NIVEL ACADÉMICO
UPDATE afiliados_educacion 
SET nivel_estudio = 'Universitario' 
WHERE LOWER(nivel_estudio) LIKE '%univ%';

UPDATE afiliados_educacion 
SET nivel_estudio = 'Posgrado' 
WHERE LOWER(nivel_estudio) LIKE '%posg%' OR LOWER(nivel_estudio) LIKE '%postg%';

DELETE FROM afiliados_datos_cobro WHERE `id_afiliado`=480;
DELETE FROM afiliados_laborales WHERE `id_afiliado`=480;
DELETE FROM afiliados_educacion WHERE `id_afiliado`=480;
DELETE FROM afiliados_domicilios WHERE `id_afiliado`=480;
DELETE FROM afiliados_datos_personales WHERE `id_afiliado`=480;
DELETE FROM afiliados_datos_cobro WHERE `id_afiliado`=480;
DELETE FROM afiliados_maestra WHERE `id_afiliado`=480;