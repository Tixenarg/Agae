<?php
require_once '../config/conexion.php';

class ReporteModelo
{
    private $db;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
    }

    // 1. Obtener datos para los combos de filtros maestros
    public function obtenerParametrosFiltro()
    {
        try {
            $estados = $this->db->query("SELECT id_estado, estado_nombre FROM afiliado_estados")->fetchAll(PDO::FETCH_ASSOC);
            $fpagos = $this->db->query("SELECT id_fpago, fpago_nombre FROM afiliado_forma_de_pago")->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                "estados" => $estados,
                "fpagos" => $fpagos
            ];
        } catch (PDOException $e) {
            error_log("Error ReporteModelo::obtenerParametrosFiltro -> " . $e->getMessage());
            return false;
        }
    }

    // 2. Generar la sábana de datos filtrada
    public function generarReporte($id_estado = 'TODOS', $id_fpago = 'TODOS')
    {
        try {
            // Unimos todas las tablas. Usamos LEFT JOIN por si algún afiliado no tiene cargado algún módulo aún.
            $sql = "SELECT 
                        m.id_afiliado, m.dni, m.apellidos, m.nombres, m.telefono, m.email, m.fecha_solicitud, m.fecha_afiliacion,
                        est.estado_nombre,
                        p.cuil, p.nacionalidad, p.sexo, p.estado_civil, p.fecha_nacimiento,
                        fp.fpago_nombre, c.numero_cuenta, c.acepto_pago,
                        d.direccion, d.localidad, d.provincia, d.codigo_postal,
                        e.nivel_estudio, e.titulo,
                        l.legajo, l.org_liquida_haber, l.org_trabaja, l.domicilio_trabajo, l.localidad_trabajo
                    FROM afiliados_maestra m
                    LEFT JOIN afiliado_estados est ON m.id_estado = est.id_estado
                    LEFT JOIN afiliados_datos_personales p ON m.id_afiliado = p.id_afiliado
                    LEFT JOIN afiliados_datos_cobro c ON m.id_afiliado = c.id_afiliado
                    LEFT JOIN afiliado_forma_de_pago fp ON c.id_fpago = fp.id_fpago
                    LEFT JOIN afiliados_domicilios d ON m.id_afiliado = d.id_afiliado
                    LEFT JOIN afiliados_educacion e ON m.id_afiliado = e.id_afiliado
                    LEFT JOIN afiliados_laborales l ON m.id_afiliado = l.id_afiliado
                    WHERE 1=1";

            // Aplicamos los filtros maestros dinámicamente si no piden "TODOS"
            if ($id_estado !== 'TODOS' && is_numeric($id_estado)) {
                $sql .= " AND m.id_estado = :id_estado";
            }
            if ($id_fpago !== 'TODOS' && is_numeric($id_fpago)) {
                $sql .= " AND c.id_fpago = :id_fpago";
            }

            $stmt = $this->db->prepare($sql);

            if ($id_estado !== 'TODOS' && is_numeric($id_estado)) {
                $stmt->bindValue(':id_estado', $id_estado, PDO::PARAM_INT);
            }
            if ($id_fpago !== 'TODOS' && is_numeric($id_fpago)) {
                $stmt->bindValue(':id_fpago', $id_fpago, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error ReporteModelo::generarReporte -> " . $e->getMessage());
            return false;
        }
    }
}