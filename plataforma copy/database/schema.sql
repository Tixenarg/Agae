-- ============================================================
-- AGAE · Sistema Integral de Gestión de Eventos
-- Esquema inicial de base de datos
-- Versión: 1.0.0
-- Motor recomendado: MySQL 8+
-- Codificación: utf8mb4
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- EVENTOS
-- Permite que el backend sea multi-evento.
-- ============================================================

CREATE TABLE eventos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    codigo_prefijo VARCHAR(20) NOT NULL,

    descripcion TEXT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NULL,

    lugar VARCHAR(180) NULL,
    direccion VARCHAR(255) NULL,

    cupo_total INT UNSIGNED NOT NULL,
    minutos_reserva SMALLINT UNSIGNED NOT NULL DEFAULT 15,

    estado VARCHAR(30) NOT NULL DEFAULT 'borrador',

    venta_desde DATETIME NULL,
    venta_hasta DATETIME NULL,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_eventos_slug (slug),
    INDEX idx_eventos_estado (estado),
    INDEX idx_eventos_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ÁMBITOS PROFESIONALES
-- Opciones visibles en el formulario de compra.
-- ============================================================

CREATE TABLE ambitos_profesionales (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(120) NOT NULL,
    orden_visual SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_ambitos_nombre (nombre),
    INDEX idx_ambitos_activo_orden (activo, orden_visual)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- PLANTILLAS PDF
-- Permite utilizar diseños distintos para general, protocolo,
-- sponsor, staff, prensa, etc.
-- ============================================================

CREATE TABLE plantillas_pdf (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(120) NOT NULL,
    codigo VARCHAR(80) NOT NULL,

    titulo VARCHAR(180) NULL,
    mensaje TEXT NULL,
    archivo_plantilla VARCHAR(255) NULL,

    activo TINYINT(1) NOT NULL DEFAULT 1,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_plantillas_codigo (codigo)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TIPOS DE ACCESO
-- General, protocolo, sponsor, staff, prensa, cortesía, etc.
-- ============================================================

CREATE TABLE tipos_acceso (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    evento_id BIGINT UNSIGNED NOT NULL,
    plantilla_pdf_id BIGINT UNSIGNED NULL,

    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(50) NOT NULL,

    precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    venta_web TINYINT(1) NOT NULL DEFAULT 0,
    requiere_pago TINYINT(1) NOT NULL DEFAULT 0,
    computa_cupo TINYINT(1) NOT NULL DEFAULT 1,
    permite_descarga_admin TINYINT(1) NOT NULL DEFAULT 1,

    activo TINYINT(1) NOT NULL DEFAULT 1,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_tipos_acceso_evento
        FOREIGN KEY (evento_id)
        REFERENCES eventos(id),

    CONSTRAINT fk_tipos_acceso_plantilla
        FOREIGN KEY (plantilla_pdf_id)
        REFERENCES plantillas_pdf(id)
        ON DELETE SET NULL,

    UNIQUE KEY uk_tipo_evento_codigo (evento_id, codigo),
    INDEX idx_tipos_evento_activo (evento_id, activo),
    INDEX idx_tipos_venta_web (evento_id, venta_web, activo)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- COMPRADORES
-- Persona responsable de una orden.
-- El DNI se guarda sin puntos ni espacios.
-- ============================================================

CREATE TABLE compradores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    ambito_profesional_id SMALLINT UNSIGNED NULL,

    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL,

    email VARCHAR(180) NOT NULL,
    telefono VARCHAR(40) NOT NULL,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_compradores_ambito
        FOREIGN KEY (ambito_profesional_id)
        REFERENCES ambitos_profesionales(id)
        ON DELETE SET NULL,

    INDEX idx_compradores_dni (dni),
    INDEX idx_compradores_email (email),
    INDEX idx_compradores_apellido_nombre (apellido, nombre)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ÓRDENES
-- Una orden representa una compra o una emisión administrativa.
--
-- Código visible:
-- 6N26-XXXX-XXXX
--
-- Estados previstos:
-- pendiente_pago
-- pagada
-- expirada
-- cancelada
-- pago_en_revision
-- reembolsada
-- ============================================================

CREATE TABLE ordenes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    evento_id BIGINT UNSIGNED NOT NULL,
    comprador_id BIGINT UNSIGNED NULL,

    codigo VARCHAR(30) NOT NULL,
    checkout_token CHAR(64) NOT NULL,

    origen VARCHAR(40) NOT NULL DEFAULT 'web',
    estado VARCHAR(40) NOT NULL DEFAULT 'pendiente_pago',

    cantidad_accesos INT UNSIGNED NOT NULL,

    precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    moneda CHAR(3) NOT NULL DEFAULT 'ARS',

    reserva_hasta DATETIME NULL,

    acepto_terminos TINYINT(1) NOT NULL DEFAULT 0,
    acepto_privacidad TINYINT(1) NOT NULL DEFAULT 0,
    aceptaciones_en DATETIME NULL,

    pagada_en DATETIME NULL,
    expirada_en DATETIME NULL,
    cancelada_en DATETIME NULL,

    motivo_cancelacion VARCHAR(255) NULL,
    observaciones TEXT NULL,

    creada_por_usuario_id BIGINT UNSIGNED NULL,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_ordenes_evento
        FOREIGN KEY (evento_id)
        REFERENCES eventos(id),

    CONSTRAINT fk_ordenes_comprador
        FOREIGN KEY (comprador_id)
        REFERENCES compradores(id)
        ON DELETE SET NULL,

    UNIQUE KEY uk_ordenes_codigo (codigo),
    UNIQUE KEY uk_ordenes_checkout_token (checkout_token),

    INDEX idx_ordenes_evento_estado (evento_id, estado),
    INDEX idx_ordenes_comprador (comprador_id),
    INDEX idx_ordenes_reserva (estado, reserva_hasta),
    INDEX idx_ordenes_creado_en (creado_en)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ACCESOS
-- Cada acceso genera un PDF y un QR independiente.
--
-- Ejemplo:
-- Orden:  6N26-K7PR-2M9X
-- Acceso: 6N26-K7PR-2M9X-A1
--
-- Estados previstos:
-- pendiente_emision
-- emitido
-- utilizado
-- anulado
-- reembolsado
-- ============================================================

CREATE TABLE accesos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    orden_id BIGINT UNSIGNED NOT NULL,
    tipo_acceso_id BIGINT UNSIGNED NOT NULL,

    codigo VARCHAR(40) NOT NULL,
    numero_en_orden SMALLINT UNSIGNED NOT NULL,

    nombre VARCHAR(100) NULL,
    apellido VARCHAR(100) NULL,
    sin_nombre TINYINT(1) NOT NULL DEFAULT 0,

    email_individual VARCHAR(180) NULL,
    dni_individual VARCHAR(20) NULL,

    qr_token CHAR(64) NOT NULL,

    estado VARCHAR(40) NOT NULL DEFAULT 'pendiente_emision',

    pdf_generado_en DATETIME NULL,
    email_enviado_en DATETIME NULL,
    utilizado_en DATETIME NULL,
    anulado_en DATETIME NULL,

    motivo_anulacion VARCHAR(255) NULL,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_accesos_orden
        FOREIGN KEY (orden_id)
        REFERENCES ordenes(id),

    CONSTRAINT fk_accesos_tipo
        FOREIGN KEY (tipo_acceso_id)
        REFERENCES tipos_acceso(id),

    UNIQUE KEY uk_accesos_codigo (codigo),
    UNIQUE KEY uk_accesos_qr_token (qr_token),
    UNIQUE KEY uk_acceso_numero_orden (orden_id, numero_en_orden),

    INDEX idx_accesos_orden (orden_id),
    INDEX idx_accesos_tipo_estado (tipo_acceso_id, estado),
    INDEX idx_accesos_nombre (apellido, nombre)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- PAGOS
-- Guarda las operaciones propias y las recibidas desde
-- Mercado Pago.
--
-- No se debe asumir que una orden tiene un único intento.
-- ============================================================

CREATE TABLE pagos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    orden_id BIGINT UNSIGNED NOT NULL,

    proveedor VARCHAR(50) NOT NULL DEFAULT 'mercadopago',
    proveedor_pago_id VARCHAR(120) NULL,
    proveedor_preferencia_id VARCHAR(120) NULL,

    estado VARCHAR(50) NOT NULL DEFAULT 'pending',
    estado_detalle VARCHAR(120) NULL,

    importe DECIMAL(12,2) NOT NULL,
    moneda CHAR(3) NOT NULL DEFAULT 'ARS',

    metodo_pago VARCHAR(80) NULL,
    tipo_pago VARCHAR(80) NULL,
    cuotas SMALLINT UNSIGNED NULL,

    aprobado_en DATETIME NULL,
    rechazado_en DATETIME NULL,

    datos_respuesta JSON NULL,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pagos_orden
        FOREIGN KEY (orden_id)
        REFERENCES ordenes(id),

    UNIQUE KEY uk_pagos_proveedor_id (
        proveedor,
        proveedor_pago_id
    ),

    INDEX idx_pagos_orden (orden_id),
    INDEX idx_pagos_estado (estado),
    INDEX idx_pagos_preferencia (proveedor_preferencia_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- NOTIFICACIONES DE PAGO
-- Guarda Webhooks y evita procesarlos de manera duplicada.
-- ============================================================

CREATE TABLE notificaciones_pago (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    proveedor VARCHAR(50) NOT NULL,
    identificador_externo VARCHAR(150) NULL,
    tipo VARCHAR(100) NULL,

    payload JSON NOT NULL,

    recibida_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    procesada_en DATETIME NULL,

    estado_procesamiento VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    intentos SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ultimo_error TEXT NULL,

    INDEX idx_notificaciones_estado (
        estado_procesamiento,
        recibida_en
    ),

    INDEX idx_notificaciones_externo (
        proveedor,
        identificador_externo
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ROLES ADMINISTRATIVOS
-- ============================================================

CREATE TABLE roles (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(80) NOT NULL,
    codigo VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NULL,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_roles_codigo (codigo)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- USUARIOS ADMINISTRATIVOS
-- ============================================================

CREATE TABLE usuarios_admin (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    rol_id SMALLINT UNSIGNED NOT NULL,

    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL,

    password_hash VARCHAR(255) NOT NULL,

    activo TINYINT(1) NOT NULL DEFAULT 1,

    ultimo_acceso_en DATETIME NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuarios_admin_rol
        FOREIGN KEY (rol_id)
        REFERENCES roles(id),

    UNIQUE KEY uk_usuarios_admin_email (email),
    INDEX idx_usuarios_admin_activo (activo)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- Se agrega después porque ordenes referencia usuarios_admin.
ALTER TABLE ordenes
    ADD CONSTRAINT fk_ordenes_usuario_creador
        FOREIGN KEY (creada_por_usuario_id)
        REFERENCES usuarios_admin(id)
        ON DELETE SET NULL;


-- ============================================================
-- COLA DE EMAILS
--
-- Estados previstos:
-- pendiente
-- procesando
-- enviado
-- fallido
-- reintentando
-- ============================================================

CREATE TABLE emails (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    orden_id BIGINT UNSIGNED NULL,
    acceso_id BIGINT UNSIGNED NULL,

    tipo VARCHAR(80) NOT NULL,

    destinatario VARCHAR(180) NOT NULL,
    asunto VARCHAR(255) NOT NULL,

    plantilla VARCHAR(120) NULL,
    datos_plantilla JSON NULL,

    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',

    intentos SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    max_intentos SMALLINT UNSIGNED NOT NULL DEFAULT 5,

    ultimo_error TEXT NULL,

    programado_para DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    enviado_en DATETIME NULL,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_emails_orden
        FOREIGN KEY (orden_id)
        REFERENCES ordenes(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_emails_acceso
        FOREIGN KEY (acceso_id)
        REFERENCES accesos(id)
        ON DELETE SET NULL,

    INDEX idx_emails_cola (estado, programado_para),
    INDEX idx_emails_orden (orden_id),
    INDEX idx_emails_destinatario (destinatario)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- CHECK-INS
-- Cada acceso puede tener un único ingreso válido.
-- ============================================================

CREATE TABLE checkins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    acceso_id BIGINT UNSIGNED NOT NULL,
    usuario_admin_id BIGINT UNSIGNED NULL,

    metodo VARCHAR(40) NOT NULL DEFAULT 'qr',
    dispositivo VARCHAR(255) NULL,
    ip VARCHAR(45) NULL,

    observaciones VARCHAR(255) NULL,

    registrado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_checkins_acceso
        FOREIGN KEY (acceso_id)
        REFERENCES accesos(id),

    CONSTRAINT fk_checkins_usuario
        FOREIGN KEY (usuario_admin_id)
        REFERENCES usuarios_admin(id)
        ON DELETE SET NULL,

    UNIQUE KEY uk_checkin_acceso (acceso_id),
    INDEX idx_checkins_fecha (registrado_en)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ANULACIONES ADMINISTRATIVAS
-- No ejecuta devoluciones monetarias.
-- Solo registra una decisión tomada por la organización.
-- ============================================================

CREATE TABLE anulaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    orden_id BIGINT UNSIGNED NULL,
    acceso_id BIGINT UNSIGNED NULL,
    usuario_admin_id BIGINT UNSIGNED NOT NULL,

    tipo VARCHAR(50) NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    observaciones TEXT NULL,

    referencia_externa VARCHAR(180) NULL,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_anulaciones_orden
        FOREIGN KEY (orden_id)
        REFERENCES ordenes(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_anulaciones_acceso
        FOREIGN KEY (acceso_id)
        REFERENCES accesos(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_anulaciones_usuario
        FOREIGN KEY (usuario_admin_id)
        REFERENCES usuarios_admin(id),

    INDEX idx_anulaciones_orden (orden_id),
    INDEX idx_anulaciones_acceso (acceso_id),
    INDEX idx_anulaciones_fecha (creado_en)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- AUDITORÍA
-- Registra acciones sensibles realizadas en administración.
-- ============================================================

CREATE TABLE auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_admin_id BIGINT UNSIGNED NULL,

    accion VARCHAR(120) NOT NULL,
    entidad VARCHAR(80) NOT NULL,
    entidad_id BIGINT UNSIGNED NULL,

    datos_anteriores JSON NULL,
    datos_nuevos JSON NULL,

    ip VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,

    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (usuario_admin_id)
        REFERENCES usuarios_admin(id)
        ON DELETE SET NULL,

    INDEX idx_auditoria_entidad (entidad, entidad_id),
    INDEX idx_auditoria_usuario_fecha (
        usuario_admin_id,
        creado_en
    ),
    INDEX idx_auditoria_accion (accion)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;