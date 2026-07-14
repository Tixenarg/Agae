<?php
class Conexion
{

    public static function conectar()
    {
        // === 1. CONFIGURACIÓN DE TUS CREDENCIALES ===
        // Profe: Acá tenés que poner los datos reales de tu servidor local o hosting
        $host = 'localhost';
        $db   = 'agaeweb'; // <-- ¡Cambiar por tu BD!
        $user = 'root';                       // <-- ¡Cambiar por tu usuario!
        $pass = '';                           // <-- ¡Cambiar por tu clave!

        // Usamos el charset moderno para soportar todos los caracteres correctamente
        $charset = 'utf8mb4';

        // === 2. ARMADO DEL DSN (Data Source Name) ===
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        // === 3. OPCIONES DE SEGURIDAD Y RENDIMIENTO ===
        $options = [
            // Que los errores de MySQL rompan la ejecución y los podamos atrapar (try/catch)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Que por defecto nos devuelva arrays asociativos (ej: $fila['nombre'])
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Apagamos la emulación para que las consultas preparadas sean 100% nativas y seguras
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // === 4. CREACIÓN DE LA INSTANCIA ===
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);

            // Forzamos la colación de ordenamiento que definimos en tu arquitectura
            // Si tu MySQL es un poco más viejo y no soporta uca1400, cambialo por 'utf8mb4_unicode_ci'
            // Cambiá la línea vieja por esta nueva:
            $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");

            return $pdo;
        } catch (\PDOException $e) {
            // Si falla, guardamos el error en el log del servidor, pero no se lo mostramos al usuario público
            error_log("Error crítico de Conexión BD: " . $e->getMessage());
            die("Error de conexión a la base de datos. Por favor, intente más tarde.");
        }
    }
}
