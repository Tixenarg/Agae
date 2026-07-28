<?php
// 1. Desactivamos los límites de tiempo y memoria para migraciones masivas
set_time_limit(0); 
ini_set('memory_limit', '512M');

// 2. Iniciamos búfer de salida para evitar que Warnings/Notices rompan el JSON
ob_start();

require_once __DIR__ . '/../modelos/Migracion_modelo.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $modelo = new Migracion_modelo();
    
    // Ejecutamos pasándole el nombre de tu tabla sábana vieja
    $resultado = $modelo->ejecutarMigracion('afiliados');

    // Limpiamos cualquier texto basura que PHP haya podido emitir antes
    ob_end_clean();

    echo json_encode($resultado);
    exit;

} catch (Throwable $e) {
    // Si ocurre un error fatal no capturado
    ob_end_clean();
    echo json_encode([
        "exito" => false,
        "error" => "Error crítico en servidor: " . $e->getMessage()
    ]);
    exit;
}