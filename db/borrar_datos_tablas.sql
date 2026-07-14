-- 1. Apagamos temporalmente el chequeo de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- 2. Borramos el contenido de todas las tablas
DELETE FROM afiliados_domicilios;
DELETE FROM afiliados_educacion;
DELETE FROM afiliados_laborales;
DELETE FROM afiliados_maestra;
DELETE FROM solicitudes_afiliacion;

-- 3. Reiniciamos los contadores (Auto_Increment) a 1 para que arranquen de cero
ALTER TABLE afiliados_domicilios AUTO_INCREMENT = 1;
ALTER TABLE afiliados_educacion AUTO_INCREMENT = 1;
ALTER TABLE afiliados_laborales AUTO_INCREMENT = 1;
ALTER TABLE afiliados_maestra AUTO_INCREMENT = 1;
ALTER TABLE solicitudes_afiliacion AUTO_INCREMENT = 1;

-- 4. Volvemos a encender la seguridad
SET FOREIGN_KEY_CHECKS = 1;