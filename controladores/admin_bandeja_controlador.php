<?php
// /controladores/admin_bandeja_controlador.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// INICIO DE SESIÓN (Preparado para cuando armemos el Login con tu tabla usuarios)
session_start();

/* Descomentar esto cuando el login esté listo
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. No hay sesión activa."]);
    exit;
}
*/

require_once __DIR__ . '/../modelos/Solicitud_modelo.php';

$response = ["status" => "error", "data" => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Instanciamos el modelo
    $modelo = new SolicitudModelo();
    
    // Le pedimos los datos
    $pendientes = $modelo->obtenerPendientes();
    
    // Armamos la respuesta exitosa
    $response["status"] = "success";
    $response["data"] = $pendientes;
} else {
    $response["message"] = "Método HTTP no permitido.";
}

echo json_encode($response);
exit;