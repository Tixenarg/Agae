<?php
// Le decimos al navegador que vamos a responder con formato JSON (para Vue.js)
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Llamamos a nuestro motor de base de datos
require_once __DIR__ . '/../config/conexion.php';

$response = ["status" => "error", "message" => "Petición no válida."];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibimos los datos que manda Vue.js
    $json_input = file_get_contents("php://input");
    $data = json_decode($json_input, true);

    // 1. Limpieza básica de datos
    $apellidos = isset($data['apellidos']) ? trim(strip_tags($data['apellidos'])) : '';
    $nombres   = isset($data['nombres'])   ? trim(strip_tags($data['nombres'])) : '';
    $email     = isset($data['email'])     ? trim(filter_var($data['email'], FILTER_SANITIZE_EMAIL)) : '';
    $whatsapp  = isset($data['whatsapp'])  ? trim(strip_tags($data['whatsapp'])) : '';
    $dni       = isset($data['dni'])       ? trim(strip_tags($data['dni'])) : '';
    
    // Le quitamos los puntos al DNI si es que vinieron del front
    $dniLimpio = str_replace('.', '', $dni);

    // 2. Validamos que no vengan vacíos
    if (empty($apellidos) || empty($nombres) || empty($email) || empty($whatsapp) || empty($dniLimpio)) {
        $response['message'] = "Todos los campos con asterisco (*) son obligatorios.";
        echo json_encode($response);
        exit;
    }

    try {
        // Nos conectamos a la Base de Datos usando tu clase estática
        $db = Conexion::conectar();

        // 3. Verificamos que el DNI no tenga ya un trámite PENDIENTE
        $sqlCheck = "SELECT id FROM solicitudes_afiliacion WHERE dni = :dni AND estado = 'PENDIENTE' LIMIT 1";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->bindValue(':dni', $dniLimpio, PDO::PARAM_STR);
        $stmtCheck->execute();

        if ($stmtCheck->fetch()) {
            $response['message'] = "Ya poseemos una solicitud de afiliación pendiente para el DNI ingresado.";
            echo json_encode($response);
            exit;
        }

        // 4. Guardamos en la tabla de Staging (Pre-registro)
        $sqlInsert = "INSERT INTO solicitudes_afiliacion (apellidos, nombres, email, whatsapp, dni, estado) 
                      VALUES (:apellidos, :nombres, :email, :whatsapp, :dni, 'PENDIENTE')";
        $stmtInsert = $db->prepare($sqlInsert);
        $stmtInsert->bindValue(':apellidos', $apellidos, PDO::PARAM_STR);
        $stmtInsert->bindValue(':nombres', $nombres, PDO::PARAM_STR);
        $stmtInsert->bindValue(':email', $email, PDO::PARAM_STR);
        $stmtInsert->bindValue(':whatsapp', $whatsapp, PDO::PARAM_STR);
        $stmtInsert->bindValue(':dni', $dniLimpio, PDO::PARAM_STR);
        
        $stmtInsert->execute();

        // Si llegamos acá, ¡todo salió perfecto!
        $response['status'] = "success";
        $response['message'] = "¡Solicitud registrada correctamente!";

    } catch (PDOException $e) {
        // Guardamos el error real en los logs para vos, pero mostramos algo genérico al usuario
        error_log("Error en procesar_solicitud.php -> " . $e->getMessage());
        $response['message'] = "Error interno del servidor. Intente más tarde.";
    }
}

echo json_encode($response);
exit;