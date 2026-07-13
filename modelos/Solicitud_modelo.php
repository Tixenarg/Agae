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
     * Trae todas las solicitudes con estado PENDIENTE para la bandeja de entrada.
     */
    public function obtenerPendientes(): array
    {
        try {
            $sql = "SELECT id, dni, apellidos, nombres, email, whatsapp, fecha_solicitud, estado 
                    FROM solicitudes_afiliacion 
                    WHERE estado = 'PENDIENTE' 
                    ORDER BY fecha_solicitud DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SolicitudModelo::obtenerPendientes -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Trae los datos de la bandeja temporal para que el operador los vea en el formulario.
     */
    public function obtenerPorId(int $id): ?array
    {
        try {
            $sql = "SELECT id, dni, apellidos, nombres, email, whatsapp, fecha_solicitud 
                    FROM solicitudes_afiliacion 
                    WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            error_log("Error en SolicitudModelo::obtenerPorId -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Trae las formas de pago de tu tabla maestra para dibujar los botones dinámicos.
     */
    public function obtenerFormasPago(): array
    {
        try {
            // Nota: En el archivo que subiste decía "afiliado_forma_de_pago". 
            // Lo dejé así, pero si los botones desaparecen, sacale el "_de_" (afiliado_forma_pago).
            $sql = "SELECT id_fpago, fpago_nombre FROM afiliado_forma_de_pago ORDER BY id_fpago ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SolicitudModelo::obtenerFormasPago -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * EL ALTA DEFINITIVA: Migra los datos web + forma de pago y prepara los módulos.
     */
    public function aprobarYCrearAfiliado(int $id_solicitud, int $id_fpago, string $numero_cuenta): bool
    {
        try {
            $this->db->beginTransaction();

            // PASO A: Buscar datos de la postulación web
            $sqlSel = "SELECT dni, apellidos, nombres, email, whatsapp, fecha_solicitud 
                       FROM solicitudes_afiliacion 
                       WHERE id = :id FOR UPDATE";
            $stmtSel = $this->db->prepare($sqlSel);
            $stmtSel->bindValue(':id', $id_solicitud, PDO::PARAM_INT);
            $stmtSel->execute();
            $solicitud = $stmtSel->fetch(PDO::FETCH_ASSOC);

            if (!$solicitud) {
                $this->db->rollBack();
                return false;
            }

            // PASO B: Insertar en la tabla maestra (Ya es Afiliado Oficial)
            $sqlMaestra = "INSERT INTO afiliados_maestra 
                          (dni, apellidos, nombres, id_fpago, numero_cuenta, fecha_solicitud_original, id_solicitud_origen) 
                          VALUES (:dni, :apellidos, :nombres, :id_fpago, :numero_cuenta, :fecha_solicitud, :id_solicitud_origen)";

            $stmtMaestra = $this->db->prepare($sqlMaestra);
            $stmtMaestra->bindValue(':dni', $solicitud['dni'], PDO::PARAM_STR);
            $stmtMaestra->bindValue(':apellidos', $solicitud['apellidos'], PDO::PARAM_STR);
            $stmtMaestra->bindValue(':nombres', $solicitud['nombres'], PDO::PARAM_STR);
            $stmtMaestra->bindValue(':id_fpago', $id_fpago, PDO::PARAM_INT);
            $stmtMaestra->bindValue(':numero_cuenta', ($id_fpago === 1 && !empty($numero_cuenta)) ? $numero_cuenta : null, PDO::PARAM_STR);
            $stmtMaestra->bindValue(':fecha_solicitud', $solicitud['fecha_solicitud'], PDO::PARAM_STR);
            $stmtMaestra->bindValue(':id_solicitud_origen', $id_solicitud, PDO::PARAM_INT);
            $stmtMaestra->execute();

            $id_afiliado_nuevo = $this->db->lastInsertId();

            // PASO C: Rescatar el Email y WhatsApp en la tabla de Domicilios
            $sqlDom = "INSERT INTO afiliados_domicilios (id_afiliado, telefono, email) VALUES (:id, :tel, :email)";
            $stmtDom = $this->db->prepare($sqlDom);
            $stmtDom->bindValue(':id', $id_afiliado_nuevo, PDO::PARAM_INT);
            $stmtDom->bindValue(':tel', $solicitud['whatsapp'], PDO::PARAM_STR);
            $stmtDom->bindValue(':email', $solicitud['email'], PDO::PARAM_STR);
            $stmtDom->execute();

            // PASO D: Crear fila vacía en Educación
            $sqlEdu = "INSERT INTO afiliados_educacion (id_afiliado) VALUES (:id)";
            $stmtEdu = $this->db->prepare($sqlEdu);
            $stmtEdu->bindValue(':id', $id_afiliado_nuevo, PDO::PARAM_INT);
            $stmtEdu->execute();

            // PASO E: Crear fila vacía en Laborales
            $sqlLab = "INSERT INTO afiliados_laborales (id_afiliado) VALUES (:id)";
            $stmtLab = $this->db->prepare($sqlLab);
            $stmtLab->bindValue(':id', $id_afiliado_nuevo, PDO::PARAM_INT);
            $stmtLab->execute();

            // PASO F: Limpiar la bandeja temporal (Cambia estado a APROBADO)
            $sqlUpd = "UPDATE solicitudes_afiliacion SET estado = 'APROBADO' WHERE id = :id";
            $stmtUpd = $this->db->prepare($sqlUpd);
            $stmtUpd->bindValue(':id', $id_solicitud, PDO::PARAM_INT);
            $stmtUpd->execute();

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            // Hacemos que "muera" acá imprimiendo el error exacto de la base de datos al frontend
            die(json_encode([
                "status" => "error",
                "message" => "DEBUG SQL: " . $e->getMessage()
            ]));
        }
    }

    /**
     * FUNCIONES DEL ÁREA PÚBLICA (afiliacionbeta.html)
     * Verifica si el DNI ya tiene una solicitud en curso para evitar duplicados.
     */
/**
     * FUNCIONES DEL ÁREA PÚBLICA (afiliacionbeta.html)
     * Verifica si el DNI ya existe (ya sea como pendiente o como afiliado oficial).
     */
    public function existeDniPendiente(string $dni): bool {
        try {
            // 1. Filtro Maestro: Verificamos si ya es un afiliado oficial (aprobado)
            // (Nota: Si tu tabla de aprobados se llama distinto, cambialo acá. 
            // Yo usé 'afiliados_maestra' como veníamos trabajando).
            $sqlMaestra = "SELECT dni FROM afiliados_maestra WHERE dni = :dni LIMIT 1";
            $stmtM = $this->db->prepare($sqlMaestra);
            $stmtM->bindValue(':dni', $dni, PDO::PARAM_STR);
            $stmtM->execute();
            
            if ($stmtM->fetch(PDO::FETCH_ASSOC)) {
                return true; // Ya es afiliado, bloqueamos
            }

            // 2. Filtro Temporal: Verificamos si ya mandó formulario y está 'PENDIENTE'
            $sqlTemporal = "SELECT id FROM solicitudes_afiliacion WHERE dni = :dni AND estado = 'PENDIENTE' LIMIT 1";
            $stmtT = $this->db->prepare($sqlTemporal);
            $stmtT->bindValue(':dni', $dni, PDO::PARAM_STR);
            $stmtT->execute();
            
            if ($stmtT->fetch(PDO::FETCH_ASSOC)) {
                return true; // Ya está en la bandeja esperando, bloqueamos
            }

            // Si pasa ambos filtros, el DNI está limpio y puede registrarse
            return false; 

        } catch (PDOException $e) {
            error_log("Error en SolicitudModelo::existeDniPendiente -> " . $e->getMessage());
            // Si hay un error de conexión, devolvemos TRUE por seguridad para que no guarde duplicados
            return true; 
        }
    }

    /**
     * Guarda la nueva solicitud temporal que llega desde la web.
     */
    public function registrarSolicitud(array $datos): bool
    {
        try {
            $sql = "INSERT INTO solicitudes_afiliacion 
                    (dni, apellidos, nombres, email, whatsapp, estado) 
                    VALUES (:dni, :apellidos, :nombres, :email, :whatsapp, 'PENDIENTE')";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':dni', $datos['dni'], PDO::PARAM_STR);
            $stmt->bindValue(':apellidos', $datos['apellidos'], PDO::PARAM_STR);
            $stmt->bindValue(':nombres', $datos['nombres'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $datos['email'], PDO::PARAM_STR);
            $stmt->bindValue(':whatsapp', $datos['whatsapp'], PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en SolicitudModelo::registrarSolicitud -> " . $e->getMessage());
            return false;
        }
    }
}
