<?php 

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once '../db/conexion_Afiliados.php';

try {
    // Consulta segura
    $sql = "SELECT id_estado, estado_nombre 
            FROM afiliado_estados 
            ORDER BY estado_nombre ASC";

    $result = mysqli_query($link, $sql);


    if (!$result) {
        throw new Exception("Error en la consulta SQL: " . mysqli_error($conn));
    }

    $estados = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $estados[] = [
            'id_estado' => (int)$row['id_estado'],
            'estado_nombre' => htmlspecialchars($row['estado_nombre'], ENT_QUOTES, 'UTF-8')
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $estados
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

