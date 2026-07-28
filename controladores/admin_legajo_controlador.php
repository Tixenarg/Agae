<?php
// 1. Blindaje de seguridad: Solo usuarios logueados pueden interactuar con este controlador
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión expirada."]);
    exit;
}

require_once '../modelos/Legajo_modelo.php';
$modelo = new LegajoModelo();

// Detectamos si nos están pidiendo datos (GET) o si nos están mandando a guardar algo (POST)
$metodo = $_SERVER['REQUEST_METHOD'];

// ====================================================================
// A. LECTURA (GET) - Traer los datos para llenar el Acordeón
// ====================================================================
// ====================================================================
// A. LECTURA (GET) - Traer los datos para llenar el Acordeón
// ====================================================================
if ($metodo === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id > 0) {
        $datos = $modelo->obtenerLegajoCompleto($id);

        // Si no hay error de debug, devolvemos success
        if ($datos && !isset($datos['error_debug'])) {
            echo json_encode(["status" => "success", "data" => $datos]);
        } else {
            $msgError = $datos['error_debug'] ?? "No se encontró el legajo.";
            echo json_encode(["status" => "error", "message" => $msgError]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "ID de afiliado inválido."]);
    }
}
// ====================================================================
// B. ESCRITURA (POST) - Guardar por Módulos Independientes
// ====================================================================
elseif ($metodo === 'POST') {
    // Capturamos el JSON que envía Vue
    $input = json_decode(file_get_contents('php://input'), true);

    $accion = $input['accion'] ?? '';
    $datos = $input['datos'] ?? [];

    // Verificación básica
    if (empty($accion) || empty($datos['id_afiliado'])) {
        echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios para procesar la solicitud."]);
        exit;
    }

    $resultado = false;

    // Evaluamos qué botón apretó el operador
    switch ($accion) {
        case 'guardar_fpago': // <--- AGREGAMOS ESTE CASO
            $resultado = $modelo->actualizarFormaPago($datos);
            break;
        case 'guardar_identidad':
            $resultado = $modelo->actualizarIdentidad($datos);
            break;
        case 'guardar_domicilio':
            $resultado = $modelo->actualizarDomicilio($datos);
            break;
        case 'guardar_educacion':
            $resultado = $modelo->actualizarEducacion($datos);
            break;
        case 'guardar_laboral':
            $resultado = $modelo->actualizarLaboral($datos);
            break;
        case 'guardar_maestra':
            $resultado = $modelo->actualizarMaestra($datos);
            break;
        default:
            echo json_encode(["status" => "error", "message" => "Acción desconocida."]);
            exit;
    }

    if ($resultado) {
        echo json_encode(["status" => "success", "message" => "Módulo actualizado correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Ocurrió un error al guardar en la base de datos."]);
    }
}
// ====================================================================
// C. MÉTODO NO PERMITIDO
// ====================================================================
else {
    echo json_encode(["status" => "error", "message" => "Método HTTP no permitido."]);
}
