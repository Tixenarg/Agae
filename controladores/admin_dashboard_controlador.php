<?php
// Blindaje de seguridad
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado."]);
    exit;
}

require_once '../config/conexion.php';

try {
    $conexion = new Conexion();
    $db = $conexion->conectar();
    
    // 1. Contamos las solicitudes pendientes en la bandeja
    $sqlPendientes = "SELECT COUNT(*) as total FROM solicitudes_afiliacion WHERE estado = 'PENDIENTE'";
    $stmtPend = $db->query($sqlPendientes);
    $cantPendientes = $stmtPend->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // 2. Contamos el total de afiliados activos en el padrón
    $sqlAfiliados = "SELECT COUNT(*) as total FROM afiliados_maestra";
    $stmtAfil = $db->query($sqlAfiliados);
    $cantAfiliados = $stmtAfil->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Aquí podés agregar más contadores a futuro (ej. novedades, pagos, etc.)

    echo json_encode([
        "status" => "success",
        "metricas" => [
            "pendientes" => (int)$cantPendientes,
            "afiliados" => (int)$cantAfiliados
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error de métricas: " . $e->getMessage()]);
}
?>