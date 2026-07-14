<?php
// Arrancamos la sesión para poder destruirla
session_start();

// Destruimos todas las variables de sesión
session_unset();
session_destroy();

// Lo pateamos al login
header("Location: login.php");
exit;
?>