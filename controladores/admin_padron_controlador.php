<?php
// 1. Blindaje de seguridad: Solo usuarios logueados pueden interactuar con este controlador
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión expirada."]);
    exit;
}

// 2. Importamos únicamente el Modelo
require_once '../modelos/AfiliadoModelo.php';

try {
    // 3. Instanciamos el modelo y le pedimos los datos generales (Esta función no pide ID)
    $modelo = new AfiliadoModelo();
    $afiliados = $modelo->obtenerPadronConSemaforo();
    
    // 4. Respondemos al frontend con la lista completa
    echo json_encode(["status" => "success", "data" => $afiliados]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error en el servidor: " . $e->getMessage()]);
}
?>