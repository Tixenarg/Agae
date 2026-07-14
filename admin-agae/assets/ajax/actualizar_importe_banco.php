<?php
require_once('../db/conexion_afiliados.php');

$importe = isset($_POST['importe']) ? floatval($_POST['importe']) : 0;

if ($importe <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Importe inválido']);
    exit;
}

$sql = "UPDATE bancos SET bco_importe_debito = ? LIMIT 1";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "d", $importe);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($link)]);
}

mysqli_stmt_close($stmt);
mysqli_close($link);
?>
