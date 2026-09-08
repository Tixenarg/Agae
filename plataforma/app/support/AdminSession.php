<?php

declare(strict_types=1);

namespace App\Support;

final class AdminSession
{
    public static function iniciar(): void
    {
        if (
            session_status()
            === PHP_SESSION_ACTIVE
        ) {
            return;
        }

        $https =
            (
                !empty($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] !== 'off'
            )
            || (
                strtolower(
                    (string) (
                        $_SERVER[
                            'HTTP_X_FORWARDED_PROTO'
                        ] ?? ''
                    )
                ) === 'https'
            );

        ini_set(
            'session.use_strict_mode',
            '1'
        );

        session_name(
            'agae_eventos_admin'
        );

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function login(
        array $usuario
    ): void {
        self::iniciar();

        session_regenerate_id(
            true
        );

        $_SESSION['admin'] =
            $usuario;
    }

    public static function usuario(): ?array
    {
        self::iniciar();

        $usuario =
            $_SESSION['admin']
            ?? null;

        return is_array($usuario)
            ? $usuario
            : null;
    }

    public static function autenticado(): bool
    {
        return self::usuario()
            !== null;
    }

    public static function logout(): void
    {
        self::iniciar();

        $_SESSION = [];

        if (
            ini_get(
                'session.use_cookies'
            )
        ) {
            $params =
                session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' =>
                        time() - 42000,

                    'path' =>
                        $params['path'],

                    'secure' =>
                        $params['secure'],

                    'httponly' =>
                        $params['httponly'],

                    'samesite' =>
                        'Lax',
                ]
            );
        }

        session_destroy();
    }
}