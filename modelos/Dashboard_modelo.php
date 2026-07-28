<?php
require_once __DIR__ . '/../config/conexion.php';

class Dashboard_modelo
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::conectar();
    }

    /**
     * Obtiene los indicadores numéricos (KPIs) para el Dashboard
     */
    /**
     * Obtiene los indicadores numéricos (KPIs) y el desglose por forma de pago para el Dashboard
     */
  /**
     * Obtiene los indicadores numéricos (KPIs) y el desglose por forma de pago para el Dashboard
     */
    public function obtenerMetricasGlobales() {
        try {
            // 1. Total de solicitudes pendientes de revisión web
            $sqlPendientes = "SELECT COUNT(*) AS total FROM solicitudes_afiliacion WHERE estado = 'PENDIENTE'";
            $stmt1 = $this->conexion->prepare($sqlPendientes);
            $stmt1->execute();
            $pendientes = $stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // 2. Total de afiliados en padrón activo (Filtrado estricto por id_estado = 2)
            $sqlAfiliados = "SELECT COUNT(*) AS total FROM afiliados_maestra WHERE id_estado = 2";
            $stmt2 = $this->conexion->prepare($sqlAfiliados);
            $stmt2->execute();
            $totalAfiliados = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // 3. Valor de la cuota vigente desde la tabla bancos
            $sqlImporte = "SELECT bco_importe_debito FROM bancos LIMIT 1";
            $stmt3 = $this->conexion->prepare($sqlImporte);
            $stmt3->execute();
            $resultadoBanco = $stmt3->fetch(PDO::FETCH_ASSOC);
            $importeCuota = $resultadoBanco ? (float)$resultadoBanco['bco_importe_debito'] : 0.00;

            // 4. Proyección de recaudación mensual sobre el padrón activo real
            $proyeccionTotal = $totalAfiliados * $importeCuota;

            // 5. Desglose de afiliados activos por Forma de Pago (Mapeo exacto de tablas)
            $sqlFormasPago = "SELECT 
                                fp.id_fpago,
                                fp.fpago_nombre,
                                COUNT(am.id_afiliado) AS total
                              FROM afiliado_forma_de_pago fp
                              LEFT JOIN afiliados_datos_cobro adc ON fp.id_fpago = adc.id_fpago
                              LEFT JOIN afiliados_maestra am ON adc.id_afiliado = am.id_afiliado AND am.id_estado = 2
                              GROUP BY fp.id_fpago, fp.fpago_nombre
                              ORDER BY fp.id_fpago ASC";
            $stmt5 = $this->conexion->prepare($sqlFormasPago);
            $stmt5->execute();
            $formasPago = $stmt5->fetchAll(PDO::FETCH_ASSOC);

            return [
                'solicitudes_pendientes' => (int)$pendientes,
                'total_afiliados'        => (int)$totalAfiliados,
                'importe_cuota'          => $importeCuota,
                'proyeccion_recaudacion' => $proyeccionTotal,
                'formas_pago'            => $formasPago
            ];
        } catch (PDOException $e) {
            error_log("Error en Dashboard_modelo::obtenerMetricasGlobales: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Obtiene las últimas 5 solicitudes web registradas en estado PENDIENTE
     */
    public function obtenerSolicitudesPendientesRecientes()
    {
        try {
            $sql = "SELECT id, dni, apellidos, nombres, email, whatsapp, fecha_solicitud 
                    FROM solicitudes_afiliacion 
                    WHERE estado = 'PENDIENTE' 
                    ORDER BY fecha_solicitud DESC 
                    LIMIT 5";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Dashboard_modelo::obtenerSolicitudesPendientesRecientes: " . $e->getMessage());
            return [];
        }
    }
}
