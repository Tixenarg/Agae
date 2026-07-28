<?php
// 1. Blindaje de seguridad y cabecera JSON
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesión no válida o expirada'
    ]);
    exit;
}

require_once __DIR__ . '/../modelos/Dashboard_modelo.php';

try {
    // Instanciación exacta coincidiendo con el nombre de la clase en el Modelo
    $modelo = new Dashboard_modelo();

    // 2. Procesamiento de la petición GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $metricas = $modelo->obtenerMetricasGlobales();
        $solicitudes = $modelo->obtenerSolicitudesPendientesRecientes();

        if ($metricas !== false) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'usuario_nombre' => $_SESSION['nombre'] ?? 'Administrador',
                    'metricas'       => $metricas,
                    'solicitudes'    => $solicitudes
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al consultar la información del tablero'
            ]);
        }
        exit;
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Método HTTP no permitido'
        ]);
        exit;
    }
} catch (Throwable $e) {
    error_log("Error en admin_dashboard_controlador: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Ocurrió un error interno en el servidor.'
    ]);
    exit;
}