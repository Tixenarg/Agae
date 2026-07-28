<?php
session_start();
// Validación estricta usando TU variable de sesión correcta
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

require_once '../modelos/Reporte_modelo.php';
// ... (el resto del código queda igual) ...


// Leemos el JSON enviado por Vue (Fetch API)
$datosRecibidos = json_decode(file_get_contents("php://input"), true);
$accion = isset($datosRecibidos['accion']) ? $datosRecibidos['accion'] : (isset($_GET['accion']) ? $_GET['accion'] : '');

$modelo = new ReporteModelo();

switch ($accion) {
    case 'get_parametros':
        // Carga los selects de los filtros
        $parametros = $modelo->obtenerParametrosFiltro();
        if ($parametros !== false) {
            echo json_encode(['status' => 'success', 'data' => $parametros]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al cargar filtros maestros.']);
        }
        break;

    case 'generar_reporte':
        // Genera la tabla base según los filtros maestros
        $estado = isset($datosRecibidos['id_estado']) ? $datosRecibidos['id_estado'] : 'TODOS';
        $fpago = isset($datosRecibidos['id_fpago']) ? $datosRecibidos['id_fpago'] : 'TODOS';
        
        $datosReporte = $modelo->generarReporte($estado, $fpago);
        
        if ($datosReporte !== false) {
            echo json_encode(['status' => 'success', 'data' => $datosReporte]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al generar el reporte.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}