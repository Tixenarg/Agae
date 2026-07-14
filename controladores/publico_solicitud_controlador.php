<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../modelos/Solicitud_modelo.php';

$response = ["status" => "error", "message" => "Petición no procesada."];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_input = file_get_contents("php://input");
    $data = json_decode($json_input, true);

    // Sanitización exhaustiva
    $apellidos = isset($data['apellidos']) ? trim(strip_tags($data['apellidos'])) : '';
    $nombres   = isset($data['nombres'])   ? trim(strip_tags($data['nombres'])) : '';
    $email     = isset($data['email'])     ? trim(filter_var($data['email'], FILTER_SANITIZE_EMAIL)) : '';
    $whatsapp  = isset($data['whatsapp'])  ? trim(strip_tags($data['whatsapp'])) : '';
    $dni       = isset($data['dni'])       ? trim(strip_tags($data['dni'])) : '';
    
    $dniLimpio = str_replace('.', '', $dni);

    // Validaciones duras del Servidor
    if (empty($apellidos) || empty($nombres) || empty($email) || empty($whatsapp) || empty($dniLimpio)) {
        $response['message'] = "Todos los campos son obligatorios.";
        echo json_encode($response);
        exit;
    }

    $modelo = new SolicitudModelo();

    // Verificación de Reglas de Negocio usando el Modelo
    if ($modelo->existeDniPendiente($dniLimpio)) {
        $response['message'] = "Ya poseemos una solicitud de afiliación pendiente para el DNI ingresado.";
        echo json_encode($response);
        exit;
    }

    // Armamos el paquete y lo enviamos al Modelo
    $paquete = [
        'apellidos' => $apellidos,
        'nombres'   => $nombres,
        'email'     => $email,
        'whatsapp'  => $whatsapp,
        'dni'       => $dniLimpio
    ];

    if ($modelo->registrarSolicitud($paquete)) {
        $response['status'] = "success";
        $response['message'] = "¡Solicitud registrada correctamente!";
    } else {
        $response['message'] = "Error interno del servidor. Por favor, intente nuevamente.";
    }
} else {
    $response['message'] = "Método de comunicación inválido.";
}

echo json_encode($response);
exit;