<?php
require_once '../config/conexion.php';

class UsuarioModelo {
    private $db;

    public function __construct() {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
    }

    public function autenticarUsuario(string $usuario, string $clave) {
        try {
            // Buscamos al usuario por su nombre. 
            // Explicación: Usamos LIMIT 1 por rendimiento, ya que el usuario debería ser único.
            $sql = "SELECT id_usuario, usu_nombre, usu_nivel, usu_clave 
                    FROM usuarios 
                    WHERE usu_usuario = :usuario LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificamos si trajo un registro y si la clave en texto plano coincide
            if ($user && $user['usu_clave'] === $clave) {
                // Eliminamos la clave del array en memoria por seguridad antes de devolver los datos
                unset($user['usu_clave']);
                return $user;
            }

            return false; // Usuario no existe o clave incorrecta
        } catch (PDOException $e) {
            error_log("Error en UsuarioModelo::autenticarUsuario -> " . $e->getMessage());
            return false;
        }
    }
}
?>