<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

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
        
        // Acción: Desafiliar (Pasa a estado 3)
        if ($input['accion'] === 'desafiliar') {
            try {
                $resultado = $modelo->desafiliarAfiliado($input['id_afiliado'], $input['motivo'], $_SESSION['id_usuario']);
                echo json_encode(["status" => $resultado ? "success" : "error"]);
            } catch (Exception $e) {
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            exit;
        }
        
        // Acción: Reafiliar (Pasa a estado 2)
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

// Atajamos peticiones GET (Lectura del padrón, métricas y formas de pago)
try {
    $modelo = new AfiliadoModelo();
    
    // Estado por defecto: 2 (Afiliados Activos)
    // 1 = Solicitudes, 2 = Afiliados, 3 = Desafiliados
    $estadoId = isset($_GET['estado']) ? intval($_GET['estado']) : 2;
    
    $afiliados = $modelo->obtenerPadronConSemaforo($estadoId);
    $stats = $modelo->obtenerEstadisticasPadron();
    $formasPago = $modelo->obtenerFormasPago();
    
    echo json_encode([
        "status" => "success", 
        "data" => $afiliados,
        "stats" => $stats,
        "formas_pago" => $formasPago
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error en el servidor: " . $e->getMessage()]);
}
?>