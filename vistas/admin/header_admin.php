<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Controlamos que exista el ID de usuario en la sesión
if (!isset($_SESSION['id_usuario'])) {
    // Si no está logueado, lo pateamos directo al login
    header("Location: login.php");
    exit;
}
?>