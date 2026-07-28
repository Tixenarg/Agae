<?php
session_start();
require_once '../modelos/Solicitud_modelo.php';

// Validamos seguridad básica
if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["exito" => false, "error" => "Sesión no válida o expirada."]);
    exit;
}

$modelo = new Solicitud_modelo();
$accion = $_GET['accion'] ?? '';

header('Content-Type: application/json; charset=utf-8');

switch ($accion) {
    
    case 'listar_pendientes':
        $respuesta = $modelo->listarPendientes();
        echo json_encode($respuesta);
        break;

    case 'aprobar_afiliacion':
        // Leemos el payload JSON enviado por Vue.js (axios o fetch envían raw json)
        $datos_post = json_decode(file_get_contents('php://input'), true);
        
        $id_solicitud = $datos_post['id_solicitud'] ?? null;
        $id_fpago = $datos_post['id_fpago'] ?? null;
        $numero_cuenta = $datos_post['numero_cuenta'] ?? null;

        // Validaciones del lado del servidor
        if (!$id_solicitud || !$id_fpago) {
            echo json_encode(["exito" => false, "error" => "Faltan datos obligatorios (Solicitud o Forma de Pago)."]);
            exit;
        }

        // Si elige Débito (1), exigimos el CBU/Cuenta
        if ($id_fpago == 1 && empty(trim($numero_cuenta))) {
            echo json_encode(["exito" => false, "error" => "El número de cuenta es obligatorio para el pago por Débito."]);
            exit;
        }

        $respuesta = $modelo->aprobarYAfiliar($id_solicitud, $id_fpago, $numero_cuenta);
        echo json_encode($respuesta);
        break;

    default:
        echo json_encode(["exito" => false, "error" => "Acción no reconocida."]);
        break;
}
exit;