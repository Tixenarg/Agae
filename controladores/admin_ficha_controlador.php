<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../modelos/Solicitud_modelo.php';
$modelo = new SolicitudModelo();

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $id = isset($_GET['id_solicitud']) ? (int)$_GET['id_solicitud'] : 0;
    $afiliado = $modelo->obtenerPorId($id);
    
    if ($afiliado) {
        // Traemos las formas de pago guardadas en la BD
        $formasPago = $modelo->obtenerFormasPago();
        
        echo json_encode([
            "status" => "success", 
            "data" => $afiliado,
            "formas_pago" => $formasPago
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "No se encontró el postulante."]);
    }
    exit;
}

if ($metodo === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $id         = isset($data['id_solicitud']) ? (int)$data['id_solicitud'] : 0;
    $id_fpago   = isset($data['id_fpago']) ? (int)$data['id_fpago'] : 0;
    $num_cuenta = isset($data['numero_cuenta']) ? trim(strip_tags($data['numero_cuenta'])) : '';

    if ($id <= 0 || $id_fpago <= 0) {
        echo json_encode(["status" => "error", "message" => "Información incompleta."]);
        exit;
    }

    // Si es ID 1 (Débito Banco Nación), validamos los 14 dígitos exactos
    if ($id_fpago === 1 && strlen($num_cuenta) !== 14) {
        echo json_encode(["status" => "error", "message" => "La cuenta de ahorro BNA debe contener 14 números obligatorios."]);
        exit;
    }

    if ($modelo->aprobarYCrearAfiliado($id, $id_fpago, $num_cuenta)) {
        echo json_encode(["status" => "success", "message" => "Afiliado registrado en el padrón definitivo."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al procesar el alta transaccional."]);
    }
    exit;
}