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
     * Si Vue nos manda un campo vacío, lo convertimos a NULL para la base de datos.
     */
    private function aNull($valor)
    {
        // Verificamos que el valor exista y no sea una cadena vacía
        if (!isset($valor)) return null;
        $val = trim((string)$valor);
        return ($val === '') ? null : $val;
    }

    // ====================================================================
    // 1. LECTURA (Traer todo para llenar el acordeón en Vue)
    // ====================================================================
    public function obtenerLegajoCompleto(int $id_afiliado)
    {
        try {
            $sql = "SELECT 
                        m.id_afiliado, m.dni, m.cuil, m.apellidos, m.nombres, m.nacionalidad, m.sexo, m.estado_civil, m.fecha_nacimiento, m.id_fpago, m.numero_cuenta,
                        d.domicilio, d.localidad, d.codigo_postal, d.provincia, d.telefono, d.email,
                        e.nivel_estudio, e.titulo,
                        l.legajo, l.org_liquida_haber, l.org_trabaja, l.domicilio_trabajo, l.localidad_trabajo
                    FROM afiliados_maestra m
                    LEFT JOIN afiliados_domicilios d ON m.id_afiliado = d.id_afiliado
                    LEFT JOIN afiliados_educacion e ON m.id_afiliado = e.id_afiliado
                    LEFT JOIN afiliados_laborales l ON m.id_afiliado = l.id_afiliado
                    WHERE m.id_afiliado = :id_afiliado LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $id_afiliado, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::obtenerLegajoCompleto -> " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // 2. ESCRITURA: Módulo Identidad
    // ====================================================================
    public function actualizarIdentidad(array $datos)
    {
        try {
            // Actualizamos la maestra. Apellido, Nombre y DNI no se tocan acá.
            $sql = "UPDATE afiliados_maestra 
                    SET cuil = :cuil, nacionalidad = :nacionalidad, sexo = :sexo, 
                        estado_civil = :estado_civil, fecha_nacimiento = :fecha_nacimiento
                    WHERE id_afiliado = :id_afiliado";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cuil', $this->aNull($datos['cuil']));
            $stmt->bindValue(':nacionalidad', $this->aNull($datos['nacionalidad']));
            $stmt->bindValue(':sexo', $this->aNull($datos['sexo']));
            $stmt->bindValue(':estado_civil', $this->aNull($datos['estado_civil']));
            $stmt->bindValue(':fecha_nacimiento', $this->aNull($datos['fecha_nacimiento']));
            $stmt->bindValue(':id_afiliado', $datos['id_afiliado'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::actualizarIdentidad -> " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // 3. ESCRITURA: Módulo Domicilio
    // ====================================================================
    public function actualizarDomicilio(array $datos)
    {
        try {
            $sql = "INSERT INTO afiliados_domicilios (id_afiliado, domicilio, localidad, codigo_postal, provincia, telefono, email)
                    VALUES (:id_afiliado, :domicilio, :localidad, :codigo_postal, :provincia, :telefono, :email)
                    ON DUPLICATE KEY UPDATE 
                    domicilio = VALUES(domicilio), localidad = VALUES(localidad), 
                    codigo_postal = VALUES(codigo_postal), provincia = VALUES(provincia), 
                    telefono = VALUES(telefono), email = VALUES(email)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $datos['id_afiliado'], PDO::PARAM_INT);
            $stmt->bindValue(':domicilio', $this->aNull($datos['domicilio']));
            $stmt->bindValue(':localidad', $this->aNull($datos['localidad']));
            $stmt->bindValue(':codigo_postal', $this->aNull($datos['codigo_postal']));
            $stmt->bindValue(':provincia', $this->aNull($datos['provincia']));
            $stmt->bindValue(':telefono', $this->aNull($datos['telefono']));
            $stmt->bindValue(':email', $this->aNull($datos['email']));

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::actualizarDomicilio -> " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // 4. ESCRITURA: Módulo Educación
    // ====================================================================
    public function actualizarEducacion(array $datos)
    {
        try {
            $sql = "INSERT INTO afiliados_educacion (id_afiliado, nivel_estudio, titulo)
                    VALUES (:id_afiliado, :nivel_estudio, :titulo)
                    ON DUPLICATE KEY UPDATE 
                    nivel_estudio = VALUES(nivel_estudio), titulo = VALUES(titulo)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $datos['id_afiliado'], PDO::PARAM_INT);
            $stmt->bindValue(':nivel_estudio', $this->aNull($datos['nivel_estudio']));
            $stmt->bindValue(':titulo', $this->aNull($datos['titulo']));

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::actualizarEducacion -> " . $e->getMessage());
            return false;
        }
    }

    // ====================================================================
    // 5. ESCRITURA: Módulo Laboral
    // ====================================================================
    public function actualizarLaboral(array $datos)
    {
        try {
            $sql = "INSERT INTO afiliados_laborales (id_afiliado, legajo, org_liquida_haber, org_trabaja, domicilio_trabajo, localidad_trabajo)
                    VALUES (:id_afiliado, :legajo, :org_liquida_haber, :org_trabaja, :domicilio_trabajo, :localidad_trabajo)
                    ON DUPLICATE KEY UPDATE 
                    legajo = VALUES(legajo), org_liquida_haber = VALUES(org_liquida_haber), 
                    org_trabaja = VALUES(org_trabaja), domicilio_trabajo = VALUES(domicilio_trabajo), 
                    localidad_trabajo = VALUES(localidad_trabajo)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_afiliado', $datos['id_afiliado'], PDO::PARAM_INT);
            $stmt->bindValue(':legajo', $this->aNull($datos['legajo']));
            $stmt->bindValue(':org_liquida_haber', $this->aNull($datos['org_liquida_haber']));
            $stmt->bindValue(':org_trabaja', $this->aNull($datos['org_trabaja']));
            $stmt->bindValue(':domicilio_trabajo', $this->aNull($datos['domicilio_trabajo']));
            $stmt->bindValue(':localidad_trabajo', $this->aNull($datos['localidad_trabajo']));

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en LegajoModelo::actualizarLaboral -> " . $e->getMessage());
            return false;
        }
    }

    public function actualizarFormaPago($datos)
    {
        // Si no es BNA (1), nos aseguramos de que el número de cuenta quede vacío
        $numero_cuenta = ($datos['id_fpago'] == 1) ? $datos['numero_cuenta'] : '';

        $sql = "UPDATE afiliados_maestra 
                SET id_fpago = :id_fpago, 
                    numero_cuenta = :numero_cuenta 
                WHERE id_afiliado = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_fpago' => $datos['id_fpago'],
            ':numero_cuenta' => $numero_cuenta,
            ':id' => $datos['id_afiliado']
        ]);
    }
}
