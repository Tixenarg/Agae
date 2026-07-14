<?php
// /controladores/admin_procesar_alta_controlador.php

// 1. Llamamos a los archivos necesarios
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../modelos/Solicitud_modelo.php';

// 2. Configuramos que vamos a devolver un JSON
header('Content-Type: application/json');

// 3. Recibimos el paquete de datos que nos manda Vue (Fetch)
$datos = json_decode(file_get_contents("php://input"), true);

// 4. Validaciones de seguridad básicas
if (!$datos || !isset($datos['id_solicitud']) || !isset($datos['id_fpago'])) {
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios para procesar el alta."]);
    exit;
}

// 5. Limpiamos las variables
$id_solicitud = (int) $datos['id_solicitud'];
$id_fpago = (int) $datos['id_fpago'];
$numero_cuenta = isset($datos['numero_cuenta']) ? trim($datos['numero_cuenta']) : '';

// 6. Instanciamos el modelo y disparamos la transacción de alta
$modelo = new SolicitudModelo();
$id_nuevo = $modelo->aprobarYCrearAfiliado($id_solicitud, $id_fpago, $numero_cuenta);

// 7. Devolvemos la respuesta al Frontend (Vue)
if ($id_nuevo > 0) {
    echo json_encode(["status" => "success", "message" => "Afiliado registrado.", "id_afiliado" => $id_nuevo]);
} else {
    echo json_encode(["status" => "error", "message" => "Fallo en MySQL. El rollback protegió la base de datos."]);
}
?>
