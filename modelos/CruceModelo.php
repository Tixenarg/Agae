<?php
require_once '../config/conexion.php';

class CruceModelo
{
    private $db;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
    }

    /**
     * Consulta masiva a la base de datos buscando coincidencias de DNI.
     * @param array $dnis Array de strings/integers con los DNIs a comparar
     * @return array Listado de afiliados que hicieron match en AGAE
     */
    public function buscarPorDnis($dnis)
    {
        try {
            // 1. Sanitización estricta: Elimina todo lo que no sea número
            $dnisLimpios = array_values(array_unique(array_filter(array_map(function ($dni) {
                return preg_replace('/[^0-9]/', '', (string)$dni);
            }, $dnis), function ($dni) {
                return strlen($dni) >= 6 && strlen($dni) <= 9;
            })));

            // Prevención del bug SQL: Si no hay DNIs válidos, devolvemos array vacío de inmediato
            if (empty($dnisLimpios)) {
                return [];
            }

            // 2. Procesamiento por lotes (Chunks) para evitar colapsar PDO con consultas gigantes
            $lotes = array_chunk($dnisLimpios, 1000);
            $todosLosResultados = [];

            foreach ($lotes as $lote) {
                // Preparamos los placeholders (?, ?, ?) según el tamaño del lote actual
                $placeholders = implode(',', array_fill(0, count($lote), '?'));

                $sql = "SELECT 
                        m.id_afiliado, m.dni, m.apellidos, m.nombres, m.telefono, m.email,
                        est.estado_nombre,
                        fp.fpago_nombre
                    FROM afiliados_maestra m
                    LEFT JOIN afiliado_estados est ON m.id_estado = est.id_estado
                    LEFT JOIN afiliados_datos_cobro c ON m.id_afiliado = c.id_afiliado
                    LEFT JOIN afiliado_forma_de_pago fp ON c.id_fpago = fp.id_fpago
                    WHERE m.dni IN ($placeholders)";

                $stmt = $this->db->prepare($sql);
                $stmt->execute($lote);

                $resultadosLote = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $todosLosResultados = array_merge($todosLosResultados, $resultadosLote);
            }

            return $todosLosResultados;
        } catch (PDOException $e) {
            // Registramos el error real en el log del servidor para depuración
            error_log("Error PDO en CruceModelo::buscarPorDnis -> " . $e->getMessage());
            return false;
        }
    }
}
