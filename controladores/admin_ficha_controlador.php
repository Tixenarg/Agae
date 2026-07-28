<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../modelos/Solicitud_modelo.php';

// 1. Validar sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Sesión expirada o no autorizada."]);
    exit;
}

// 2. Aceptamos id_solicitud o id indistintamente
$id = $_GET['id_solicitud'] ?? $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "No se recibió un ID de solicitud válido."]);
    exit;
}

$modelo = new Solicitud_modelo();

// 3. Consulta de datos
$resSolicitud = $modelo->obtenerPorId($id);
$resFormas = $modelo->obtenerFormasPago();

if (isset($resSolicitud['exito']) && !$resSolicitud['exito']) {
    echo json_encode(["status" => "error", "message" => $resSolicitud['error'] ?? "Solicitud no encontrada"]);
    exit;
}

// 4. Normalizamos los datos de forma de pago para que coincidan con la vista Vue
$formasPago = [];
$rawFormas = $resFormas['data'] ?? [];

foreach ($rawFormas as $f) {
    $formasPago[] = [
        'id_fpago'     => $f['id_fpago'] ?? $f['id'] ?? null,
        'fpago_nombre' => $f['fpago_nombre'] ?? $f['descripcion'] ?? ''
    ];
}

// 5. Respuesta JSON con la estructura exacta que espera la vista
echo json_encode([
    "status"      => "success",
    "data"        => $resSolicitud['data'] ?? $resSolicitud,
    "formas_pago" => $formasPago
]);
exit;