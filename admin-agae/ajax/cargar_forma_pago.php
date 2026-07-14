<?php 

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once '../db/conexion_afiliados.php';

try {
    // Consulta segura
    $sql = "SELECT id_fpago, fpago_nombre 
            FROM afiliado_forma_de_pago
            ORDER BY fpago_nombre ASC";

    $result = mysqli_query($link, $sql);


    if (!$result) {
        throw new Exception("Error en la consulta SQL: " . mysqli_error($link));
    }

    $estados = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $fpagos[] = [
            'id_fpago' => (int)$row['id_fpago'],
            'fpago_nombre' => htmlspecialchars($row['fpago_nombre'], ENT_QUOTES, 'UTF-8')
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $fpagos
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($link);
?>

