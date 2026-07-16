<?php
// /admin/index.php

// 1. Iniciamos la sesión para leer las variables globales
session_start();

// 2. Evaluamos si existe el ID del usuario en la sesión
if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario'])) {
    // Si está logueado, subimos un nivel (../) y entramos a la carpeta de vistas
    header('Location: ../vistas/admin/dashboard.php');
    exit;
} else {
    // Si no está logueado, lo mandamos a la vista pública de login
    header('Location: ../vistas/admin/login.php');
    exit;
}