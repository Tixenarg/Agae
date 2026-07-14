<?php
// Explicación: Iniciamos sesión al principio para poder guardar las variables si el login es exitoso.
session_start();

require_once '../modelos/Usuario_modelo.php';

// Solo aceptamos peticiones POST para mayor seguridad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtenemos el JSON que nos va a mandar Vue.js (axios/fetch)
    $input = json_decode(file_get_contents('php://input'), true);
    
    $usuario = trim($input['usuario'] ?? '');
    $clave = trim($input['clave'] ?? '');

    // Validación básica backend
    if (empty($usuario) || empty($clave)) {
        echo json_encode(["status" => "error", "message" => "Faltan credenciales."]);
        exit;
    }

    $modelo = new UsuarioModelo();
    $resultado = $modelo->autenticarUsuario($usuario, $clave);

    if ($resultado) {
        // Credenciales correctas: Levantamos las variables de sesión
        $_SESSION['id_usuario'] = $resultado['id_usuario'];
        $_SESSION['usu_nombre'] = $resultado['usu_nombre'];
        $_SESSION['usu_nivel']  = $resultado['usu_nivel'];

        echo json_encode([
            "status" => "success", 
            "message" => "Autenticación exitosa."
        ]);
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Usuario o contraseña incorrectos."
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
?>