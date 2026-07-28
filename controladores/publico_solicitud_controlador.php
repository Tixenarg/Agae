<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../modelos/Solicitud_modelo.php';

$response = ["status" => "error", "message" => "Petición no procesada."];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $json_input = file_get_contents("php://input");
        $data = json_decode($json_input, true);

        // Sanitización
        $apellidos = isset($data['apellidos']) ? trim(strip_tags($data['apellidos'])) : '';
        $nombres   = isset($data['nombres'])   ? trim(strip_tags($data['nombres'])) : '';
        $email     = isset($data['email'])     ? trim(filter_var($data['email'], FILTER_SANITIZE_EMAIL)) : '';
        $whatsapp  = isset($data['whatsapp'])  ? trim(strip_tags($data['whatsapp'])) : '';
        $dni       = isset($data['dni'])       ? trim(strip_tags($data['dni'])) : '';

        $dniLimpio = str_replace('.', '', $dni);

        // Validaciones Duras
        if (empty($apellidos) || empty($nombres) || empty($email) || empty($whatsapp) || empty($dniLimpio)) {
            $response['message'] = "Todos los campos son obligatorios.";
            echo json_encode($response);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = "El formato de correo electrónico no es válido.";
            echo json_encode($response);
            exit;
        }

        // Instanciación Directa
        $modelo = new Solicitud_modelo();

        // Verificación de Duplicados
        if ($modelo->existeDniPendiente($dniLimpio)) {
            $response['message'] = "Ya poseemos una solicitud de afiliación pendiente para el DNI ingresado.";
            echo json_encode($response);
            exit;
        }

        // Paquete de datos
        $paquete = [
            'apellidos' => $apellidos,
            'nombres'   => $nombres,
            'email'     => $email,
            'whatsapp'  => $whatsapp,
            'dni'       => $dniLimpio
        ];

        // Registro
        if ($modelo->registrarSolicitud($paquete)) {
            $response['status'] = "success";
            $response['message'] = "¡Solicitud registrada correctamente!";
        } else {
            $response['message'] = "Error interno al guardar la solicitud. Por favor, intente nuevamente.";
        }

    } catch (Exception $e) {
        error_log("Error en publico_solicitud_controlador: " . $e->getMessage());
        $response['message'] = "Ocurrió un inconveniente técnico al procesar la solicitud.";
        $response['debug'] = $e->getMessage();
    }
} else {
    $response['message'] = "Método de comunicación inválido.";
}

// Emisión final de respuesta
echo json_encode($response);
exit;