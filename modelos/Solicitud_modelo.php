<?php

require_once '../config/conexion.php';

class Solicitud_modelo
{
    private $db;

    public function __construct()
    {
        // Asumo que tu clase Conexion tiene un método estático o de instancia que devuelve PDO
        $this->db = Conexion::conectar();
    }

    /**
     * Lista todas las solicitudes que están en estado PENDIENTE
     */
    public function listarPendientes()
    {
        try {
            $sql = "SELECT id, dni, apellidos, nombres, email, whatsapp, fecha_solicitud, estado 
                    FROM solicitudes_afiliacion 
                    WHERE estado = 'PENDIENTE' 
                    ORDER BY fecha_solicitud DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return ["exito" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (PDOException $e) {
            return ["exito" => false, "error" => "Error al listar: " . $e->getMessage()];
        }
    }

    /**
     * Transacción atómica: Pasa de Solicitud a Afiliado Activo en las 6 tablas relacionales
     */
    public function aprobarYAfiliar($id_solicitud, $id_fpago, $numero_cuenta)
    {
        try {
            // 1. Obtener los datos de la solicitud pendiente
            $sqlSelect = "SELECT * FROM solicitudes_afiliacion WHERE id = ? AND estado = 'PENDIENTE' LIMIT 1";
            $stmtSelect = $this->db->prepare($sqlSelect);
            $stmtSelect->execute([$id_solicitud]);
            $solicitud = $stmtSelect->fetch(PDO::FETCH_ASSOC);

            if (!$solicitud) {
                return ["exito" => false, "error" => "La solicitud no existe o ya fue procesada anteriormente."];
            }

            // INICIAMOS LA TRANSACCIÓN SQL
            $this->db->beginTransaction();

            // 2. Insertar en tabla maestra (id_estado = 2 -> Activo)
            $fecha_solicitud = !empty($solicitud['fecha_solicitud'])
                ? date('Y-m-d', strtotime($solicitud['fecha_solicitud']))
                : date('Y-m-d');

            $sqlMaestra = "INSERT INTO afiliados_maestra (dni, apellidos, nombres, email, telefono, fecha_solicitud, id_estado) 
                           VALUES (?, ?, ?, ?, ?, ?, 2)";

            $stmtMaestra = $this->db->prepare($sqlMaestra);
            $stmtMaestra->execute([
                $solicitud['dni'],
                $solicitud['apellidos'],
                $solicitud['nombres'],
                $solicitud['email'],
                $solicitud['whatsapp'],
                $fecha_solicitud
            ]);

            $id_afiliado = $this->db->lastInsertId();

            // 3. Insertar datos de cobro
            $sqlCobro = "INSERT INTO afiliados_datos_cobro (id_afiliado, id_fpago, numero_cuenta) VALUES (?, ?, ?)";
            $stmtCobro = $this->db->prepare($sqlCobro);
            $stmtCobro->execute([$id_afiliado, $id_fpago, $numero_cuenta]);

            // 4. Crear registros vinculados vacíos en las 4 tablas hijas
            $tablas_hijas = ['afiliados_datos_personales', 'afiliados_domicilios', 'afiliados_educacion', 'afiliados_laborales'];
            foreach ($tablas_hijas as $tabla) {
                $sqlHija = "INSERT INTO $tabla (id_afiliado) VALUES (?)";
                $this->db->prepare($sqlHija)->execute([$id_afiliado]);
            }

            // 5. Actualizar la solicitud a APROBADO
            $sqlUpdate = "UPDATE solicitudes_afiliacion SET estado = 'APROBADO' WHERE id = ?";
            $this->db->prepare($sqlUpdate)->execute([$id_solicitud]);

            // CONFIRMAMOS CAMBIOS EN BD
            $this->db->commit();

            return [
                "exito"       => true,
                "mensaje"     => "Afiliado dado de alta correctamente.",
                "id_afiliado" => $id_afiliado
            ];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ["exito" => false, "error" => "Error crítico en DB: " . $e->getMessage()];
        }
    }

    /**
     * Obtiene los datos de una solicitud específica por su ID
     */
    public function obtenerPorId($id)
    {
        try {
            $sql = "SELECT * FROM solicitudes_afiliacion WHERE id = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            return $datos ? ["exito" => true, "data" => $datos] : ["exito" => false, "error" => "Solicitud no encontrada."];
        } catch (PDOException $e) {
            return ["exito" => false, "error" => "Error en la consulta: " . $e->getMessage()];
        }
    }

    /**
     * Obtiene la lista de formas de pago para los desplegables de la ficha
     */
    public function obtenerFormasPago()
    {
        try {
            $sql = "SELECT id, descripcion FROM formas_pago ORDER BY id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ["exito" => true, "data" => $data];
        } catch (PDOException $e) {
            return [
                "exito" => true,
                "data" => [
                    ["id" => 1, "descripcion" => "Débito Automático / Caja de Ahorro"],
                    ["id" => 2, "descripcion" => "Mercado Pago"],
                    ["id" => 3, "descripcion" => "Otros"]
                ]
            ];
        }
    }

    /**
     * Verifica si existe una solicitud registrada en estado PENDIENTE
     */
    public function existeDniPendiente($dni) {
        try {
            $sql = "SELECT COUNT(*) AS total 
                    FROM solicitudes_afiliacion 
                    WHERE dni = :dni AND estado = 'PENDIENTE'";
            
            $stmt = $this->db->prepare($sql); 
            $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return ((int)($resultado['total'] ?? 0)) > 0;

        } catch (PDOException $e) {
            error_log("Error en Solicitud_modelo::existeDniPendiente: " . $e->getMessage());
            throw new Exception("Error de base de datos al verificar DNI."); 
        }
    }

    /**
     * Registra una nueva solicitud pública de afiliación.
     */
    public function registrarSolicitud($datos) {
        try {
            $sql = "INSERT INTO solicitudes_afiliacion (apellidos, nombres, email, whatsapp, dni, estado, fecha_solicitud) 
                    VALUES (:apellidos, :nombres, :email, :whatsapp, :dni, 'PENDIENTE', NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute([
                ':apellidos' => $datos['apellidos'],
                ':nombres'   => $datos['nombres'],
                ':email'     => $datos['email'],
                ':whatsapp'  => $datos['whatsapp'],
                ':dni'       => $datos['dni']
            ]);

        } catch (PDOException $e) {
            error_log("Error en Solicitud_modelo::registrarSolicitud: " . $e->getMessage());
            return false;
        }
    }
}