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
     * Obtiene el padrón de afiliados calculando el semáforo de integridad
     */
  /**
     * Obtiene todos los afiliados y calcula si los módulos están cargados (1) o no (0)
     */
    public function obtenerPadronConSemaforo() {
        // Usamos las comillas invertidas estandarizadas para evitar que el motor SQL se maree con el acento en 'padrón'
        $sql = "SELECT 
                    m.id_afiliado, 
                    m.apellidos, 
                    m.nombres, 
                    m.dni, 
                    m.`fecha_alta_padrón` AS fecha_alta,
                    (CASE WHEN m.cuil IS NOT NULL AND m.cuil != '' THEN 1 ELSE 0 END) AS mod_identidad,
                    (CASE WHEN d.domicilio IS NOT NULL AND d.domicilio != '' THEN 1 ELSE 0 END) AS mod_domicilio,
                    (CASE WHEN e.nivel_estudio IS NOT NULL AND e.nivel_estudio != '' THEN 1 ELSE 0 END) AS mod_educacion,
                    (CASE WHEN l.legajo IS NOT NULL AND l.legajo != '' THEN 1 ELSE 0 END) AS mod_laboral
                FROM afiliados_maestra m
                LEFT JOIN afiliados_domicilios d ON m.id_afiliado = d.id_afiliado
                LEFT JOIN afiliados_educacion e ON m.id_afiliado = e.id_afiliado
                LEFT JOIN afiliados_laborales l ON m.id_afiliado = l.id_afiliado";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
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
}
