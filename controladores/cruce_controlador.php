<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Guardián de Seguridad: Sincronizado exactamente con header_admin.php
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesión no válida o expirada. Por favor, inicie sesión.'
    ]);
    exit;
}

require_once '../modelos/CruceModelo.php';

try {
    // Capturamos el Payload JSON raw enviado mediante Fetch API por Vue
    $inputRaw = file_get_contents('php://input');
    $data = json_decode($inputRaw, true);

    $accion = $_GET['accion'] ?? ($data['accion'] ?? '');

    if ($accion === 'cruzar_padron') {
        $dnis = $data['dnis'] ?? [];

        if (!is_array($dnis) || empty($dnis)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se recibió una lista de DNIs válida para comparar.'
            ]);
            exit;
        }

        $modelo = new CruceModelo();
        $encontrados = $modelo->buscarPorDnis($dnis);

        // Devolvemos metadatos de auditoría y el array de registros encontrados
        echo json_encode([
            'status' => 'success',
            'total_consultados' => count($dnis),
            'total_encontrados' => count($encontrados),
            'data' => $encontrados
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'error',
        'message' => 'Acción no reconocida.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}