<?php

declare(strict_types=1);

namespace App\Support;

final class AdminGuard
{
    public static function proteger(): array
    {
        $usuario =
            AdminSession::usuario();

        if ($usuario === null) {
            header(
                'Location: /public/admin/login.php'
            );

            exit;
        }

        return $usuario;
    }

public static function requiereRol(
    array $rolesPermitidos
): array {
    $usuario =
        self::proteger();

    if (
        !in_array(
            $usuario['rol_codigo'],
            $rolesPermitidos,
            true
        )
    ) {
        $_SESSION['admin_flash'] = [
            'tipo' => 'error',
            'mensaje' =>
                'No tenés permisos para acceder a esa sección.',
        ];

        $destino =
            $_SERVER['HTTP_REFERER']
            ?? '/public/admin/dashboard.php';

        header(
            'Location: ' . $destino
        );

        exit;
    }

    return $usuario;
}

}