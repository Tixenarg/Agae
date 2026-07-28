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
     * Obtiene el padrón filtrado por estado e incluye los indicadores del semáforo (6/6)
     * y el nombre del medio de pago asignado.
     * @param int $estado (1 = Solicitud, 2 = Afiliado, 3 = Desafiliado)
     */
    /**
     * Obtiene el padrón filtrado por estado e incluye los indicadores del semáforo (6/6)
     * con validación estricta de contenido real en cada módulo.
     * @param int $estado (1 = Solicitud, 2 = Afiliado, 3 = Desafiliado)
     */
    /**
     * Obtiene el padrón filtrado por estado e incluye los indicadores del semáforo (6/6)
     * con validación estricta de contenido real en cada módulo.
     * @param int $estado (1 = Solicitud, 2 = Afiliado, 3 = Desafiliado)
     */
    public function obtenerPadronConSemaforo(int $estado = 2)
    {
        try {
            $sql = "SELECT 
                        m.id_afiliado, 
                        m.apellidos, 
                        m.nombres, 
                        m.dni, 
                        m.telefono,
                        m.email,
                        m.id_estado,
                        IFNULL(m.fecha_afiliacion, 'Sin fecha') AS fecha_afiliacion,
                        c.id_fpago,
                        IFNULL(fp.fpago_nombre, 'Sin asignar') AS forma_pago_nombre,
                        
                        -- Flags del semáforo con los nombres exactos de columnas reales (1 = Completo, 0 = Pendiente)
                        -- 1. Forma de Pago (Requiere id_fpago asignado y, si es CBU BNA, exige exactamente 14 dígitos)
                        IF(c.id_fpago IS NOT NULL AND (c.id_fpago != 1 OR (c.numero_cuenta IS NOT NULL AND CHAR_LENGTH(TRIM(c.numero_cuenta)) = 14)), 1, 0) AS mod_fpago,
                        
                        -- 2. Identidad (Exige CUIL, sexo, estado civil y fecha de nacimiento)
                        IF(p.cuil IS NOT NULL AND TRIM(p.cuil) != '' AND p.sexo IS NOT NULL AND TRIM(p.sexo) != '' AND p.estado_civil IS NOT NULL AND TRIM(p.estado_civil) != '' AND p.fecha_nacimiento IS NOT NULL, 1, 0) AS mod_identidad,
                        
                        -- 3. Domicilio (Exige direccion y localidad con texto real)
                        IF(d.direccion IS NOT NULL AND TRIM(d.direccion) != '' AND d.localidad IS NOT NULL AND TRIM(d.localidad) != '', 1, 0) AS mod_domicilio,
                        
                        -- 4. Educación (Exige nivel_estudio)
                        IF(e.nivel_estudio IS NOT NULL AND TRIM(e.nivel_estudio) != '', 1, 0) AS mod_educacion,
                        
                        -- 5. Laboral (Exige legajo y org_trabaja)
                        IF(l.legajo IS NOT NULL AND TRIM(l.legajo) != '' AND l.org_trabaja IS NOT NULL AND TRIM(l.org_trabaja) != '', 1, 0) AS mod_laboral

                    FROM afiliados_maestra m
                    LEFT JOIN afiliados_datos_cobro c ON m.id_afiliado = c.id_afiliado
                    LEFT JOIN afiliado_forma_de_pago fp ON c.id_fpago = fp.id_fpago
                    LEFT JOIN afiliados_datos_personales p ON m.id_afiliado = p.id_afiliado
                    LEFT JOIN afiliados_domicilios d ON m.id_afiliado = d.id_afiliado
                    LEFT JOIN afiliados_educacion e ON m.id_afiliado = e.id_afiliado
                    LEFT JOIN afiliados_laborales l ON m.id_afiliado = l.id_afiliado
                    WHERE m.id_estado = :estado
                    ORDER BY m.apellidos ASC, m.nombres ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AfiliadoModelo::obtenerPadronConSemaforo -> " . $e->getMessage());
            throw new Exception("Error MySQL: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el conteo general para las Tarjetas del Dashboard (KPIs)
     */
    public function obtenerEstadisticasPadron()
    {
        try {
            $sql = "SELECT 
                        COUNT(*) AS total_padron,
                        SUM(IF(id_estado = 1, 1, 0)) AS total_solicitudes,
                        SUM(IF(id_estado = 2, 1, 0)) AS total_activos,
                        SUM(IF(id_estado = 3, 1, 0)) AS total_desafiliados
                    FROM afiliados_maestra";

            $stmt = $this->db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AfiliadoModelo::obtenerEstadisticasPadron -> " . $e->getMessage());
            return [
                "total_padron" => 0,
                "total_solicitudes" => 0,
                "total_activos" => 0,
                "total_desafiliados" => 0
            ];
        }
    }

    /**
     * Obtiene el catálogo de formas de pago para alimentar el filtro en la vista
     */
    public function obtenerFormasPago()
    {
        try {
            $sql = "SELECT id_fpago, fpago_nombre FROM afiliado_forma_de_pago ORDER BY fpago_nombre ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AfiliadoModelo::obtenerFormasPago -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todos los datos del legajo para un afiliado específico
     */
    // BUSCAR LA CONSULTA SQL DEL LEGAJO Y REEMPLAZAR EL SELECT POR:
    public function obtenerLegajoCompleto(int $id_afiliado)
    {
        try {
            $sql = "SELECT 
                    m.id_afiliado, 
                    m.apellidos, 
                    m.nombres, 
                    m.dni, 
                    m.telefono, 
                    m.email, 
                    m.id_estado, 
                    m.fecha_afiliacion,
                    e_est.estado_nombre, -- <--- Traemos el nombre real del estado
                    
                    -- Datos de Cobro
                    c.id_dato_cobro, c.id_fpago, c.numero_cuenta, c.acepto_pago,
                    
                    -- Datos Personales / Identidad
                    p.cuil, p.nacionalidad, p.fecha_nacimiento, p.sexo, p.estado_civil,
                    
                    -- Domicilio
                    d.calle, d.numero, d.piso, d.depto, d.localidad, d.codigo_postal,
                    
                    -- Educación
                    e.nivel_estudio, e.titulo,
                    
                    -- Laboral
                    l.numero_legajo, l.organismo_liquidador, l.organismo_trabajo
                FROM afiliados_maestra m
                LEFT JOIN afiliado_estados e_est ON m.id_estado = e_est.id_estado
                LEFT JOIN afiliados_datos_cobro c ON m.id_afiliado = c.id_afiliado
                LEFT JOIN afiliados_datos_personales p ON m.id_afiliado = p.id_afiliado
                LEFT JOIN afiliados_domicilios d ON m.id_afiliado = d.id_afiliado
                LEFT JOIN afiliados_educacion e ON m.id_afiliado = e.id_afiliado
                LEFT JOIN afiliados_laborales l ON m.id_afiliado = l.id_afiliado
                WHERE m.id_afiliado = :id_afiliado";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $id_afiliado, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AfiliadoModelo::obtenerLegajoCompleto -> " . $e->getMessage());
            return null;
        }
    }

    // ==========================================================
    // MÓDULOS DE ACTUALIZACIÓN DE LEGAJO
    // ==========================================================

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
     * Da de baja a un afiliado (Cambia su estado a 3 = Desafiliado)
     */
    public function desafiliarAfiliado(int $id_afiliado, string $motivo, int $id_usuario)
    {
        try {
            $sql = "UPDATE afiliados_maestra 
                    SET id_estado = 3 
                    WHERE id_afiliado = :id_afiliado";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $id_afiliado, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AfiliadoModelo::desafiliarAfiliado -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reactiva un afiliado (Cambia su estado a 2 = Afiliado Activo)
     */
    public function reafiliarAfiliado(int $id_afiliado, int $id_usuario)
    {
        try {
            $sql = "UPDATE afiliados_maestra 
                    SET id_estado = 2 
                    WHERE id_afiliado = :id_afiliado";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $id_afiliado, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AfiliadoModelo::reafiliarAfiliado -> " . $e->getMessage());
            return false;
        }
    }
}
