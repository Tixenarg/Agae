<?php
// Requerimos la conexión a la base de datos
require_once '../config/conexion.php';

class BancoModelo
{
    private $db;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
    }

    /**
     * Obtiene los datos de configuración de la cuenta bancaria (BNA)
     * Asumimos que toma el primer registro disponible.
     */
    public function obtenerConfiguracionBanco()
    {
        $sql = "SELECT bancos, bco_nombre, bco_tipo_moneda, bco_sucursal, bco_ctacte, bco_moneda, bco_importe_debito 
                FROM bancos LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el importe a debitar
     */
    public function actualizarImporte($nuevo_importe)
    {
        $sql = "UPDATE bancos SET bco_importe_debito = :importe";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':importe' => $nuevo_importe]);
    }

    /**
     * Obtiene los afiliados que cumplen las condiciones para débito automático
     * estado = 1 (Activo) e id_fpago = 1 (Débito)
     */
    public function obtenerAfiliadosDebito()
    {
        $sql = "SELECT id_afiliado, dni, apellidos, nombres, numero_cuenta 
                FROM afiliados_maestra 
                WHERE estado = 1 AND id_fpago = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>