<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AdminUsuarioRepository;

final class AdminAuthService
{
    public function __construct(
        private readonly AdminUsuarioRepository $usuarios
    ) {
    }

    public function login(
        string $email,
        string $password
    ): ?array {
        $email =
            mb_strtolower(
                trim($email)
            );

        if (
            $email === ''
            || $password === ''
        ) {
            return null;
        }

        $usuario =
            $this->usuarios
                ->obtenerActivoPorEmail(
                    $email
                );

        if ($usuario === null) {
            return null;
        }

        if (
            !password_verify(
                $password,
                (string) $usuario[
                    'password_hash'
                ]
            )
        ) {
            return null;
        }

        $this->usuarios
            ->actualizarUltimoAcceso(
                (int) $usuario['id']
            );

        return [
            'id' =>
                (int) $usuario['id'],

            'nombre' =>
                (string) $usuario['nombre'],

            'apellido' =>
                (string) $usuario['apellido'],

            'email' =>
                (string) $usuario['email'],

            'rol_id' =>
                (int) $usuario['rol_id'],

            'rol_nombre' =>
                (string) $usuario['rol_nombre'],

            'rol_codigo' =>
                (string) $usuario['rol_codigo'],
        ];
    }
}