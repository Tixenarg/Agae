<?php
require_once("../db/conexion_afiliados.php");

$id = $_POST['id'] ?? null;
$estado = $_POST['estado'] ?? null;
$pago = $_POST['pago'] ?? null;
$ctacte = $_POST['ctacte'] ?? null;

if (!$id || !$estado || !$pago) {
  echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios']);
  exit;
}

$sentencia = mysqli_prepare($link, "UPDATE afiliados SET afi_estado = ?, afi_forma_pago = ?, afi_ctacte = ? WHERE id = ?");
mysqli_stmt_bind_param($sentencia, "iisi", $estado, $pago, $ctacte, $id);
$ok = mysqli_stmt_execute($sentencia);

if ($ok) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Error al actualizar en la base de datos']);
}

mysqli_stmt_close($sentencia);
mysqli_close($link);
?>
