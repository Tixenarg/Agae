<?php
require_once '../config/conexion.php';

class AfiliadoModelo
{
    private $db;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
    }


    /**
     * Obtiene el padrón de afiliados filtrado por estado, calculando el semáforo de integridad.
     * @param int $estado Por defecto 1 (Activos). Pasar 2 para obtener los dados de baja.
     */
    public function obtenerPadronConSemaforo($estado = 1)
    {
        $sql = "SELECT 
                    m.id_afiliado, 
                    m.apellidos, 
                    m.nombres, 
                    m.dni, 
                    m.`fecha_alta_padrón` AS fecha_alta,
                    
                    -- Módulo 1: Forma de Pago
                    (CASE 
                        WHEN m.id_fpago = 1 AND m.numero_cuenta IS NOT NULL AND m.numero_cuenta != '' THEN 1 
                        WHEN m.id_fpago IN (2, 3) THEN 1 
                        ELSE 0 
                    END) AS mod_fpago,

                    -- Módulo 2: Identidad
                    (CASE WHEN m.cuil IS NOT NULL AND m.cuil != '' 
                           AND m.nacionalidad IS NOT NULL AND m.nacionalidad != '' 
                           AND m.fecha_nacimiento IS NOT NULL THEN 1 ELSE 0 END) AS mod_identidad,
                           
                    -- Módulo 3: Domicilio
                    (CASE WHEN d.domicilio IS NOT NULL AND d.domicilio != '' 
                           AND d.localidad IS NOT NULL AND d.localidad != '' 
                           AND d.telefono IS NOT NULL AND d.telefono != '' THEN 1 ELSE 0 END) AS mod_domicilio,
                           
                    -- Módulo 4: Educación
                    (CASE WHEN e.nivel_estudio IS NOT NULL AND e.nivel_estudio != '' THEN 1 ELSE 0 END) AS mod_educacion,
                    
                    -- Módulo 5: Información Laboral (Campos 100% correctos de afiliados_laborales)
                    (CASE 
                        WHEN l.legajo IS NOT NULL AND l.legajo != '' 
                             AND l.org_liquida_haber IS NOT NULL AND l.org_liquida_haber != ''
                             AND l.org_trabaja IS NOT NULL AND l.org_trabaja != ''
                             AND l.domicilio_trabajo IS NOT NULL AND l.domicilio_trabajo != ''
                             AND l.localidad_trabajo IS NOT NULL AND l.localidad_trabajo != '' THEN 1 
                        ELSE 0 
                    END) AS mod_laboral

                FROM afiliados_maestra m
                LEFT JOIN afiliados_domicilios d ON m.id_afiliado = d.id_afiliado
                LEFT JOIN afiliados_educacion e ON m.id_afiliado = e.id_afiliado
                LEFT JOIN afiliados_laborales l ON m.id_afiliado = l.id_afiliado
                WHERE m.estado = :estado"; // Usamos un marcador de posición para inyectar el estado dinámicamente

        $stmt = $this->db->prepare($sql);

        // Vinculamos el parámetro en el execute asegurando la limpieza del dato contra SQL Injection
        $stmt->execute([':estado' => $estado]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Obtiene todos los datos del legajo (las 4 tablas) para un afiliado específico
     */
    public function obtenerLegajoCompleto($id_afiliado)
    {
        $sql = "SELECT 
                    m.id_afiliado, m.dni, m.cuil, m.apellidos, m.nombres, m.nacionalidad, m.sexo, m.estado_civil, m.fecha_nacimiento,
                    d.domicilio, d.localidad, d.codigo_postal, d.provincia, d.telefono, d.email,
                    e.nivel_estudio, e.titulo,
                    l.legajo, l.org_liquida_haber, l.org_trabaja, l.domicilio_trabajo, l.localidad_trabajo
                FROM afiliados_maestra m
                LEFT JOIN afiliados_domicilios d ON m.id_afiliado = d.id_afiliado
                LEFT JOIN afiliados_educacion e ON m.id_afiliado = e.id_afiliado
                LEFT JOIN afiliados_laborales l ON m.id_afiliado = l.id_afiliado
                WHERE m.id_afiliado = :id_afiliado";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_afiliado', $id_afiliado, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // ==========================================================
    // MÓDULOS DE ACTUALIZACIÓN DE LEGAJO
    // ==========================================================

    /**
     * Módulo 1: Identidad (Actualiza tabla maestra)
     */
    public function actualizarIdentidad($datos)
    {
        $sql = "UPDATE afiliados_maestra 
                SET cuil = :cuil, 
                    nacionalidad = :nacionalidad, 
                    sexo = :sexo, 
                    estado_civil = :estado_civil, 
                    fecha_nacimiento = :fecha_nacimiento 
                WHERE id_afiliado = :id_afiliado";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':cuil' => $datos['cuil'] ?? null,
            ':nacionalidad' => $datos['nacionalidad'] ?? null,
            ':sexo' => $datos['sexo'] ?? null,
            ':estado_civil' => $datos['estado_civil'] ?? null,
            ':fecha_nacimiento' => !empty($datos['fecha_nacimiento']) ? $datos['fecha_nacimiento'] : null,
            ':id_afiliado' => $datos['id_afiliado']
        ]);
    }

    /**
     * Módulo 2: Domicilio (Inserta o Actualiza)
     */
    public function actualizarDomicilio($datos)
    {
        $sql = "INSERT INTO afiliados_domicilios 
                    (id_afiliado, domicilio, localidad, provincia, codigo_postal, telefono, email) 
                VALUES 
                    (:id_afiliado, :domicilio, :localidad, :provincia, :codigo_postal, :telefono, :email)
                ON DUPLICATE KEY UPDATE 
                    domicilio = VALUES(domicilio), 
                    localidad = VALUES(localidad), 
                    provincia = VALUES(provincia), 
                    codigo_postal = VALUES(codigo_postal), 
                    telefono = VALUES(telefono), 
                    email = VALUES(email)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_afiliado' => $datos['id_afiliado'],
            ':domicilio' => $datos['domicilio'] ?? null,
            ':localidad' => $datos['localidad'] ?? null,
            ':provincia' => $datos['provincia'] ?? null,
            ':codigo_postal' => $datos['codigo_postal'] ?? null,
            ':telefono' => $datos['telefono'] ?? null,
            ':email' => $datos['email'] ?? null
        ]);
    }

    /**
     * Módulo 3: Educación (Inserta o Actualiza)
     */
    public function actualizarEducacion($datos)
    {
        $sql = "INSERT INTO afiliados_educacion 
                    (id_afiliado, nivel_estudio, titulo) 
                VALUES 
                    (:id_afiliado, :nivel_estudio, :titulo)
                ON DUPLICATE KEY UPDATE 
                    nivel_estudio = VALUES(nivel_estudio), 
                    titulo = VALUES(titulo)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_afiliado' => $datos['id_afiliado'],
            ':nivel_estudio' => $datos['nivel_estudio'] ?? null,
            ':titulo' => $datos['titulo'] ?? null
        ]);
    }

    /**
     * Módulo 4: Laboral (Inserta o Actualiza)
     */
    public function actualizarLaboral($datos)
    {
        $sql = "INSERT INTO afiliados_laborales 
                    (id_afiliado, legajo, org_liquida_haber, org_trabaja, domicilio_trabajo, localidad_trabajo) 
                VALUES 
                    (:id_afiliado, :legajo, :org_liquida_haber, :org_trabaja, :domicilio_trabajo, :localidad_trabajo)
                ON DUPLICATE KEY UPDATE 
                    legajo = VALUES(legajo), 
                    org_liquida_haber = VALUES(org_liquida_haber), 
                    org_trabaja = VALUES(org_trabaja), 
                    domicilio_trabajo = VALUES(domicilio_trabajo), 
                    localidad_trabajo = VALUES(localidad_trabajo)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_afiliado' => $datos['id_afiliado'],
            ':legajo' => $datos['legajo'] ?? null,
            ':org_liquida_haber' => $datos['org_liquida_haber'] ?? null,
            ':org_trabaja' => $datos['org_trabaja'] ?? null,
            ':domicilio_trabajo' => $datos['domicilio_trabajo'] ?? null,
            ':localidad_trabajo' => $datos['localidad_trabajo'] ?? null
        ]);
    }

    /**
     * Da de baja a un afiliado (Cambia su estado y guarda el motivo en auditoría)
     */
    public function desafiliarAfiliado($id_afiliado, $motivo, $id_usuario_admin)
    {
        try {
            // Iniciamos una transacción: o se hace todo junto, o no se hace nada
            $this->db->beginTransaction();

            // 1. Cambiamos el estado en la tabla maestra 
            // ⚠️ ATENCIÓN: Revisá que tu columna se llame 'id_estado' y que '2' sea el ID de Baja
            $sql1 = "UPDATE afiliados_maestra SET estado = 2 WHERE id_afiliado = :id_afiliado";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([':id_afiliado' => $id_afiliado]);

            // 2. Guardamos el motivo en nuestra nueva tabla de auditoría
            $sql2 = "INSERT INTO afiliados_bajas (id_afiliado, id_usuario_admin, motivo) 
                     VALUES (:id_afiliado, :id_usuario_admin, :motivo)";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([
                ':id_afiliado' => $id_afiliado,
                ':id_usuario_admin' => $id_usuario_admin,
                ':motivo' => $motivo
            ]);

            // Si todo salió bien, confirmamos los cambios en la base de datos
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Si algo falló (ej: la tabla afiliados_bajas no existe), revertimos todo
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Vuelve a afiliar a un usuario dado de baja, registrando la auditoría
     */
    public function reafiliarAfiliado($id_afiliado, $id_usuario_admin)
    {
        try {
            $this->db->beginTransaction();

            // 1. Restauramos el estado a Activo (1)
            $sql1 = "UPDATE afiliados_maestra SET estado = 1 WHERE id_afiliado = :id_afiliado";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([':id_afiliado' => $id_afiliado]);

            // 2. Guardamos registro en la tabla de auditoría de re-altas
            $sql2 = "INSERT INTO afiliados_reafiliaciones (id_afiliado, id_usuario_admin) 
                     VALUES (:id_afiliado, :id_usuario_admin)";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([
                ':id_afiliado' => $id_afiliado,
                ':id_usuario_admin' => $id_usuario_admin
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
