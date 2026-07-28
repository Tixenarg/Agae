<?php
require_once '../config/conexion.php';

class LegajoModelo
{
    private $db;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
    }

    /**
     * Función auxiliar para limpiar datos: 
     * Si Vue o el cliente nos manda un campo vacío o undefined, lo convertimos a NULL explícito para MySQL.
     */
    private function aNull($valor)
    {
        if (!isset($valor)) return null;
        $val = trim((string)$valor);
        return ($val === '') ? null : $val;
    }

    // ====================================================================
    // 1. LECTURA (Obtener Legajo Completo con JOINs a Relaciones)
    // ====================================================================
    public function obtenerLegajoCompleto(int $id_afiliado)
    {
        try {
            $sql = "SELECT 
                        m.id_afiliado, 
                        m.id_estado, 
                        est.estado_nombre,
                        m.dni, 
                        m.apellidos, 
                        m.nombres, 
                        m.email, 
                        m.telefono,
                        p.cuil, 
                        p.nacionalidad, 
                        p.sexo, 
                        p.estado_civil, 
                        p.fecha_nacimiento,
                        c.id_fpago, 
                        c.numero_cuenta,
                        d.direccion AS domicilio, 
                        d.localidad, 
                        d.codigo_postal, 
                        d.provincia, 
                        d.es_principal,
                        e.nivel_estudio, 
                        e.titulo,
                        l.legajo, 
                        l.org_liquida_haber, 
                        l.org_trabaja, 
                        l.domicilio_trabajo, 
                        l.localidad_trabajo
                    FROM afiliados_maestra m
                    LEFT JOIN afiliado_estados est ON m.id_estado = est.id_estado
                    LEFT JOIN afiliados_datos_personales p ON m.id_afiliado = p.id_afiliado
                    LEFT JOIN afiliados_datos_cobro c ON m.id_afiliado = c.id_afiliado
                    LEFT JOIN afiliados_domicilios d ON m.id_afiliado = d.id_afiliado
                    LEFT JOIN afiliados_educacion e ON m.id_afiliado = e.id_afiliado
                    LEFT JOIN afiliados_laborales l ON m.id_afiliado = l.id_afiliado
                    WHERE m.id_afiliado = :id_afiliado LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $id_afiliado, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                return ["error_debug" => "No existe ningún afiliado con id_afiliado = " . $id_afiliado];
            }

            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::obtenerLegajoCompleto -> " . $e->getMessage());
            return ["error_debug" => "Error de MySQL: " . $e->getMessage()];
        }
    }

    // ====================================================================
    // 2. ESCRITURA: Módulo Forma de Pago
    // ====================================================================
    public function actualizarFormaPago(array $datos)
    {
        try {
            $idFpago = isset($datos['id_fpago']) ? (int)$datos['id_fpago'] : null;
            $numeroCuenta = ($idFpago === 1) ? $this->aNull($datos['numero_cuenta'] ?? null) : null;

            $sql = "INSERT INTO afiliados_datos_cobro (id_afiliado, id_fpago, numero_cuenta)
                    VALUES (:id_afiliado, :id_fpago, :numero_cuenta)
                    ON DUPLICATE KEY UPDATE 
                    id_fpago = VALUES(id_fpago), 
                    numero_cuenta = VALUES(numero_cuenta)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $datos['id_afiliado'], PDO::PARAM_INT);
            $stmt->bindValue(':id_fpago', $idFpago, PDO::PARAM_INT);
            $stmt->bindValue(':numero_cuenta', $numeroCuenta);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::actualizarFormaPago -> " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // 3. ESCRITURA: Módulo Identidad
    // ====================================================================
    public function actualizarIdentidad(array $datos)
    {
        try {
            $sql = "INSERT INTO afiliados_datos_personales 
                    (id_afiliado, cuil, nacionalidad, sexo, estado_civil, fecha_nacimiento)
                    VALUES (:id_afiliado, :cuil, :nacionalidad, :sexo, :estado_civil, :fecha_nacimiento)
                    ON DUPLICATE KEY UPDATE 
                    cuil = VALUES(cuil), 
                    nacionalidad = VALUES(nacionalidad), 
                    sexo = VALUES(sexo), 
                    estado_civil = VALUES(estado_civil), 
                    fecha_nacimiento = VALUES(fecha_nacimiento)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $datos['id_afiliado'], PDO::PARAM_INT);
            $stmt->bindValue(':cuil', $this->aNull($datos['cuil'] ?? null));
            $stmt->bindValue(':nacionalidad', $this->aNull($datos['nacionalidad'] ?? null));
            $stmt->bindValue(':sexo', $this->aNull($datos['sexo'] ?? null));
            $stmt->bindValue(':estado_civil', $this->aNull($datos['estado_civil'] ?? null));
            $stmt->bindValue(':fecha_nacimiento', $this->aNull($datos['fecha_nacimiento'] ?? null));

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::actualizarIdentidad -> " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // 4. ESCRITURA: Módulo Domicilio
    // ====================================================================
    public function actualizarDomicilio(array $datos)
    {
        try {
            $sql = "INSERT INTO afiliados_domicilios (id_afiliado, direccion, localidad, codigo_postal, provincia)
                    VALUES (:id_afiliado, :direccion, :localidad, :codigo_postal, :provincia)
                    ON DUPLICATE KEY UPDATE 
                    direccion = VALUES(direccion), 
                    localidad = VALUES(localidad), 
                    codigo_postal = VALUES(codigo_postal), 
                    provincia = VALUES(provincia)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $datos['id_afiliado'], PDO::PARAM_INT);
            $stmt->bindValue(':direccion', $this->aNull($datos['domicilio'] ?? null));
            $stmt->bindValue(':localidad', $this->aNull($datos['localidad'] ?? null));
            $stmt->bindValue(':codigo_postal', $this->aNull($datos['codigo_postal'] ?? null));
            $stmt->bindValue(':provincia', $this->aNull($datos['provincia'] ?? null));

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::actualizarDomicilio -> " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // 5. ESCRITURA: Módulo Educación
    // ====================================================================
    public function actualizarEducacion(array $datos)
    {
        try {
            $sql = "INSERT INTO afiliados_educacion (id_afiliado, nivel_estudio, titulo)
                    VALUES (:id_afiliado, :nivel_estudio, :titulo)
                    ON DUPLICATE KEY UPDATE 
                    nivel_estudio = VALUES(nivel_estudio), 
                    titulo = VALUES(titulo)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $datos['id_afiliado'], PDO::PARAM_INT);
            $stmt->bindValue(':nivel_estudio', $this->aNull($datos['nivel_estudio'] ?? null));
            $stmt->bindValue(':titulo', $this->aNull($datos['titulo'] ?? null));

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::actualizarEducacion -> " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // 6. ESCRITURA: Módulo Laboral
    // ====================================================================
    public function actualizarLaboral(array $datos)
    {
        try {
            $sql = "INSERT INTO afiliados_laborales (id_afiliado, legajo, org_liquida_haber, org_trabaja, domicilio_trabajo, localidad_trabajo)
                    VALUES (:id_afiliado, :legajo, :org_liquida_haber, :org_trabaja, :domicilio_trabajo, :localidad_trabajo)
                    ON DUPLICATE KEY UPDATE 
                    legajo = VALUES(legajo), 
                    org_liquida_haber = VALUES(org_liquida_haber), 
                    org_trabaja = VALUES(org_trabaja), 
                    domicilio_trabajo = VALUES(domicilio_trabajo), 
                    localidad_trabajo = VALUES(localidad_trabajo)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $datos['id_afiliado'], PDO::PARAM_INT);
            $stmt->bindValue(':legajo', $this->aNull($datos['legajo'] ?? null));
            $stmt->bindValue(':org_liquida_haber', $this->aNull($datos['org_liquida_haber'] ?? null));
            $stmt->bindValue(':org_trabaja', $this->aNull($datos['org_trabaja'] ?? null));
            $stmt->bindValue(':domicilio_trabajo', $this->aNull($datos['domicilio_trabajo'] ?? null));
            $stmt->bindValue(':localidad_trabajo', $this->aNull($datos['localidad_trabajo'] ?? null));

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::actualizarLaboral -> " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // 7. ESCRITURA: Módulo Datos Principales (Tabla Maestra)
    // ====================================================================
    public function actualizarMaestra(array $datos)
    {
        try {
            $actualizaEstado = isset($datos['id_estado']);

            $sql = "UPDATE afiliados_maestra 
                    SET dni = :dni, 
                        apellidos = :apellidos, 
                        nombres = :nombres, 
                        email = :email, 
                        telefono = :telefono" . 
                        ($actualizaEstado ? ", id_estado = :id_estado" : "") . "
                    WHERE id_afiliado = :id_afiliado";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $datos['id_afiliado'], PDO::PARAM_INT);
            $stmt->bindValue(':dni', $this->aNull($datos['dni'] ?? null));
            $stmt->bindValue(':apellidos', $this->aNull($datos['apellidos'] ?? null));
            $stmt->bindValue(':nombres', $this->aNull($datos['nombres'] ?? null));
            $stmt->bindValue(':email', $this->aNull($datos['email'] ?? null));
            $stmt->bindValue(':telefono', $this->aNull($datos['telefono'] ?? null));

            if ($actualizaEstado) {
                $stmt->bindValue(':id_estado', (int)$datos['id_estado'], PDO::PARAM_INT);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::actualizarMaestra -> " . $e->getMessage());
            return false;
        }
    }
}