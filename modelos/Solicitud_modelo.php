<?php
// /modelos/Solicitud_modelo.php
require_once __DIR__ . '/../config/conexion.php';

class SolicitudModelo
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    /**
     * Obtiene todas las solicitudes con estado PENDIENTE.
     */
    public function obtenerPendientes(): array
    {
        try {
            $sql = "SELECT id, dni, apellidos, nombres, email, whatsapp, fecha_solicitud 
                    FROM solicitudes_afiliacion 
                    WHERE estado = 'PENDIENTE' 
                    ORDER BY fecha_solicitud DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SolicitudModelo::obtenerPendientes -> " . $e->getMessage());
            return []; // Devolvemos array vacío para no romper el Front
        }
    }

    /**
     * Valida si un DNI ya tiene un trámite iniciado para evitar duplicados.
     */
    public function existeDniPendiente(string $dni): bool
    {
        $sql = "SELECT id FROM solicitudes_afiliacion WHERE dni = :dni AND estado = 'PENDIENTE' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':dni', $dni, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch() ? true : false;
    }

    /**
     * Inserta los datos del formulario web en la tabla de Staging (estado PENDIENTE).
     */
    public function registrarSolicitud(array $datos): bool
    {
        try {
            $sql = "INSERT INTO solicitudes_afiliacion (apellidos, nombres, email, whatsapp, dni, estado) 
                    VALUES (:apellidos, :nombres, :email, :whatsapp, :dni, 'PENDIENTE')";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':apellidos', $datos['apellidos'], PDO::PARAM_STR);
            $stmt->bindValue(':nombres', $datos['nombres'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $datos['email'], PDO::PARAM_STR);
            $stmt->bindValue(':whatsapp', $datos['whatsapp'], PDO::PARAM_STR);
            $stmt->bindValue(':dni', $datos['dni'], PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en SolicitudModelo::registrarSolicitud -> " . $e->getMessage());
            return false;
        }
    }
}
