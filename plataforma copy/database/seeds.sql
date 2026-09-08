-- ============================================================
-- Plataforma Integral de Gestión de Eventos
-- Datos iniciales
-- Versión: 1.0.0
-- ============================================================

SET NAMES utf8mb4;

START TRANSACTION;

-- ============================================================
-- ÁMBITOS PROFESIONALES
-- ============================================================

INSERT INTO ambitos_profesionales
    (nombre, orden_visual, activo)
VALUES
    ('Administración pública', 1, 1),
    ('Poder Judicial', 2, 1),
    ('Ejercicio independiente / estudio jurídico', 3, 1),
    ('Empresa privada', 4, 1),
    ('Ámbito académico', 5, 1),
    ('Estudiante', 6, 1),
    ('Otro', 7, 1);


-- ============================================================
-- PLANTILLAS PDF INICIALES
-- El diseño definitivo se realizará más adelante.
-- ============================================================

INSERT INTO plantillas_pdf
    (nombre, codigo, titulo, mensaje, activo)
VALUES
    (
        'Acceso general',
        'general',
        'Tu acceso',
        'Presentá este código QR al ingresar al evento.',
        1
    ),
    (
        'Invitación de protocolo',
        'protocolo',
        'Invitación especial',
        'Tenemos el agrado de invitarte a participar del evento.',
        1
    ),
    (
        'Acceso sponsor',
        'sponsor',
        'Acceso sponsor',
        'Te esperamos para compartir el evento más grande de la abogacía.',
        1
    ),
    (
        'Acceso staff',
        'staff',
        'Acceso staff',
        'Acceso correspondiente al equipo de organización.',
        1
    ),
    (
        'Acceso de prensa',
        'prensa',
        'Acceso de prensa',
        'Acreditación destinada a medios y prensa.',
        1
    ),
    (
        'Acceso de cortesía',
        'cortesia',
        'Invitación',
        'Te esperamos para compartir una noche especial.',
        1
    ),
    (
        'Acceso invitado',
        'invitado',
        'Invitación',
        'Te esperamos para compartir el evento.',
        1
    ),
    (
        'Acceso organización',
        'organizacion',
        'Acceso organización',
        'Acceso destinado a integrantes de la organización.',
        1
    );


-- ============================================================
-- ROLES ADMINISTRATIVOS
-- ============================================================

INSERT INTO roles
    (nombre, codigo, descripcion)
VALUES
    (
        'Administrador general',
        'administrador_general',
        'Acceso completo a configuración, pagos, órdenes, accesos y usuarios.'
    ),
    (
        'Administración',
        'administracion',
        'Gestión de órdenes, accesos administrativos, emails y acreditaciones.'
    ),
    (
        'Acreditación',
        'acreditacion',
        'Consulta y validación de accesos durante el evento.'
    );


-- ============================================================
-- EVENTO INICIAL
-- ============================================================

INSERT INTO eventos (
    nombre,
    slug,
    codigo_prefijo,
    descripcion,
    fecha_inicio,
    fecha_fin,
    lugar,
    direccion,
    cupo_total,
    minutos_reserva,
    estado,
    venta_desde,
    venta_hasta
)
VALUES (
    'Evento 6N 2026',
    'evento-6n-2026',
    '6N26',
    'El evento más grande de la abogacía.',
    '2026-11-06 20:30:00',
    NULL,
    'Palacio Alsina',
    'Adolfo Alsina 934, Ciudad Autónoma de Buenos Aires',
    1700,
    15,
    'borrador',
    NULL,
    NULL
);


-- ============================================================
-- TIPOS DE ACCESO DEL EVENTO
-- ============================================================

SET @evento_id = LAST_INSERT_ID();

SET @plantilla_general = (
    SELECT id
    FROM plantillas_pdf
    WHERE codigo = 'general'
    LIMIT 1
);

SET @plantilla_protocolo = (
    SELECT id
    FROM plantillas_pdf
    WHERE codigo = 'protocolo'
    LIMIT 1
);

SET @plantilla_sponsor = (
    SELECT id
    FROM plantillas_pdf
    WHERE codigo = 'sponsor'
    LIMIT 1
);

SET @plantilla_staff = (
    SELECT id
    FROM plantillas_pdf
    WHERE codigo = 'staff'
    LIMIT 1
);

SET @plantilla_prensa = (
    SELECT id
    FROM plantillas_pdf
    WHERE codigo = 'prensa'
    LIMIT 1
);

SET @plantilla_cortesia = (
    SELECT id
    FROM plantillas_pdf
    WHERE codigo = 'cortesia'
    LIMIT 1
);

SET @plantilla_invitado = (
    SELECT id
    FROM plantillas_pdf
    WHERE codigo = 'invitado'
    LIMIT 1
);

SET @plantilla_organizacion = (
    SELECT id
    FROM plantillas_pdf
    WHERE codigo = 'organizacion'
    LIMIT 1
);


INSERT INTO tipos_acceso (
    evento_id,
    plantilla_pdf_id,
    nombre,
    codigo,
    precio,
    venta_web,
    requiere_pago,
    computa_cupo,
    permite_descarga_admin,
    activo
)
VALUES
    (
        @evento_id,
        @plantilla_general,
        'General',
        'general',
        65000.00,
        1,
        1,
        1,
        0,
        1
    ),
    (
        @evento_id,
        @plantilla_protocolo,
        'Protocolo',
        'protocolo',
        0.00,
        0,
        0,
        1,
        1,
        1
    ),
    (
        @evento_id,
        @plantilla_sponsor,
        'Sponsor',
        'sponsor',
        0.00,
        0,
        0,
        1,
        1,
        1
    ),
    (
        @evento_id,
        @plantilla_staff,
        'Staff',
        'staff',
        0.00,
        0,
        0,
        1,
        1,
        1
    ),
    (
        @evento_id,
        @plantilla_prensa,
        'Prensa',
        'prensa',
        0.00,
        0,
        0,
        1,
        1,
        1
    ),
    (
        @evento_id,
        @plantilla_cortesia,
        'Cortesía',
        'cortesia',
        0.00,
        0,
        0,
        1,
        1,
        1
    ),
    (
        @evento_id,
        @plantilla_invitado,
        'Invitado',
        'invitado',
        0.00,
        0,
        0,
        1,
        1,
        1
    ),
    (
        @evento_id,
        @plantilla_organizacion,
        'Organización',
        'organizacion',
        0.00,
        0,
        0,
        1,
        1,
        1
    );

COMMIT;