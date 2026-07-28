<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../modelos/Solicitud_modelo.php';

// Validar sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Sesión expirada o no autorizada."]);
    exit;
}

// Leer datos JSON enviados desde Vue.js
$datos_post = json_decode(file_get_contents('php://input'), true);

$id_solicitud = $datos_post['id_solicitud'] ?? null;
$id_fpago     = $datos_post['id_fpago'] ?? null;
$numero_cuenta = $datos_post['numero_cuenta'] ?? '';

if (!$id_solicitud || !$id_fpago) {
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios para procesar el alta."]);
    exit;
}

// Instancia correcta del modelo
$modelo = new Solicitud_modelo();
$respuesta = $modelo->aprobarYAfiliar($id_solicitud, $id_fpago, $numero_cuenta);

if (isset($respuesta['exito']) && $respuesta['exito']) {
    echo json_encode([
        "status"      => "success",
        "message"     => "Afiliado dado de alta correctamente.",
        "id_afiliado" => $respuesta['id_afiliado']
    ]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => $respuesta['error'] ?? "Error al procesar la transacción en BD."
    ]);
}
exit;