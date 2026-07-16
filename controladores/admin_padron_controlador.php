<?php
// 1. Blindaje de seguridad: Solo usuarios logueados pueden interactuar con este controlador
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión expirada."]);
    exit;
}

require_once '../modelos/AfiliadoModelo.php';

// 2. Evaluamos si recibimos una petición POST (Es Vue mandando la orden de desafiliar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decodificamos el JSON que viene de Vue
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Verificamos que la acción sea la de desafiliar
    if (isset($input['accion']) && $input['accion'] === 'desafiliar') {
        try {
            $modelo = new AfiliadoModelo();
            // Ejecutamos la baja pasando el ID del afiliado, el motivo y el ID del administrador logueado
            $resultado = $modelo->desafiliarAfiliado($input['id_afiliado'], $input['motivo'], $_SESSION['id_usuario']);
            
            if ($resultado) {
                echo json_encode(["status" => "success", "message" => "Afiliado dado de baja correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No se pudo dar de baja al afiliado."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error en base de datos: " . $e->getMessage()]);
        }
        exit; // Detenemos la ejecución acá para que no siga con el código de abajo
    }
}

// 3. Si no es un POST, es un GET normal (Vue pidiendo la lista de afiliados)
try {
    $modelo = new AfiliadoModelo();
    $afiliados = $modelo->obtenerPadronConSemaforo();
    
    echo json_encode(["status" => "success", "data" => $afiliados]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error en el servidor: " . $e->getMessage()]);
}
?>