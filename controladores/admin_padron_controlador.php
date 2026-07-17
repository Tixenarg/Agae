<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión expirada."]);
    exit;
}

require_once '../modelos/AfiliadoModelo.php';

// Atajamos peticiones POST (Acciones de escritura)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['accion'])) {
        $modelo = new AfiliadoModelo();
        
        // Acción: Desafiliar
        if ($input['accion'] === 'desafiliar') {
            try {
                $resultado = $modelo->desafiliarAfiliado($input['id_afiliado'], $input['motivo'], $_SESSION['id_usuario']);
                echo json_encode(["status" => $resultado ? "success" : "error"]);
            } catch (Exception $e) {
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            exit;
        }
        
        // Nueva Acción: Reafiliar
        if ($input['accion'] === 'reafiliar') {
            try {
                $resultado = $modelo->reafiliarAfiliado($input['id_afiliado'], $_SESSION['id_usuario']);
                if ($resultado) {
                    echo json_encode(["status" => "success", "message" => "Afiliado reactivado correctamente."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "No se pudo reactivar el afiliado."]);
                }
            } catch (Exception $e) {
                echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
            }
            exit;
        }
    }
}

// Atajamos peticiones GET (Lectura del padrón)
try {
    $modelo = new AfiliadoModelo();
    // Leemos qué estado quiere ver el operador (1 = Activos por defecto, 2 = Bajas)
    $estadoId = isset($_GET['estado']) ? intval($_GET['estado']) : 1;
    $afiliados = $modelo->obtenerPadronConSemaforo($estadoId);
    
    echo json_encode(["status" => "success", "data" => $afiliados]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error en el servidor: " . $e->getMessage()]);
}
?>