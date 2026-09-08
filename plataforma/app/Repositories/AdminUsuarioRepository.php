<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AdminUsuarioRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function obtenerActivoPorEmail(
        string $email
    ): ?array {
        $sql = '
            SELECT
                u.id,
                u.nombre,
                u.apellido,
                u.email,
                u.password_hash,
                u.rol_id,
                u.activo,

                r.nombre AS rol_nombre,
                r.codigo AS rol_codigo

            FROM usuarios_admin u

            INNER JOIN roles r
                ON r.id = u.rol_id

            WHERE u.email = :email
              AND u.activo = 1

            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'email' =>
                $email,
        ]);

        $usuario =
            $statement->fetch();

        return $usuario ?: null;
    }

    public function actualizarUltimoAcceso(
        int $usuarioId
    ): void {
        $sql = '
            UPDATE usuarios_admin
            SET ultimo_acceso_en =
                CURRENT_TIMESTAMP

            WHERE id = :id

            LIMIT 1
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'id' =>
                $usuarioId,
        ]);
    }

    public function listar(): array
{
    $sql = '
        SELECT
            u.id,
            u.nombre,
            u.apellido,
            u.email,
            u.rol_id,
            u.activo,
            u.ultimo_acceso_en,
            u.creado_en,

            r.nombre AS rol_nombre,
            r.codigo AS rol_codigo

        FROM usuarios_admin u

        INNER JOIN roles r
            ON r.id = u.rol_id

        ORDER BY
            u.activo DESC,
            u.nombre ASC,
            u.apellido ASC
    ';

    $statement =
        $this->pdo->query($sql);

    return $statement->fetchAll();
}

public function obtenerRoles(): array
{
    $sql = '
        SELECT
            id,
            nombre,
            codigo
        FROM roles
        ORDER BY nombre
    ';

    return $this->pdo
        ->query($sql)
        ->fetchAll();
}

public function crear(
    array $datos
): int {

    $sql = '
        INSERT INTO usuarios_admin (

            nombre,
            apellido,
            email,
            password_hash,
            rol_id,
            activo

        )

        VALUES (

            :nombre,
            :apellido,
            :email,
            :password_hash,
            :rol_id,
            1

        )
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([

        'nombre' =>
            trim($datos['nombre']),

        'apellido' =>
            trim($datos['apellido']),

        'email' =>
            mb_strtolower(
                trim($datos['email'])
            ),

        'password_hash' =>
            password_hash(
                $datos['password'],
                PASSWORD_DEFAULT
            ),

        'rol_id' =>
            (int) $datos['rol_id'],

    ]);

    return (int)
        $this->pdo->lastInsertId();
}

public function existeEmail(
    string $email
): bool {

    $statement =
        $this->pdo->prepare(
            '
                SELECT id

                FROM usuarios_admin

                WHERE email = :email

                LIMIT 1
            '
        );

    $statement->execute([

        'email' =>
            mb_strtolower(
                trim($email)
            ),

    ]);

    return (bool)
        $statement->fetchColumn();
}

public function obtenerPorId(
    int $id
): ?array {

    $statement =
        $this->pdo->prepare(
            '
                SELECT
                    *
                FROM usuarios_admin
                WHERE id = :id
                LIMIT 1
            '
        );

    $statement->execute([
        'id' => $id,
    ]);

    $usuario =
        $statement->fetch();

    return $usuario === false
        ? null
        : $usuario;
}

public function actualizar(
    int $id,
    array $datos
): void {

    $sql = '
        UPDATE usuarios_admin
        SET
            nombre = :nombre,
            apellido = :apellido,
            email = :email,
            rol_id = :rol_id,
            activo = :activo
        WHERE id = :id
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([

        'id' =>
            $id,

        'nombre' =>
            trim($datos['nombre']),

        'apellido' =>
            trim($datos['apellido']),

        'email' =>
            mb_strtolower(
                trim($datos['email'])
            ),

        'rol_id' =>
            (int) $datos['rol_id'],

        'activo' =>
            (int) $datos['activo'],

    ]);
}

public function actualizarPassword(
    int $id,
    string $password
): void {

    $statement =
        $this->pdo->prepare(
            '
                UPDATE usuarios_admin
                SET password_hash = :hash
                WHERE id = :id
            '
        );

    $statement->execute([

        'id' =>
            $id,

        'hash' =>
            password_hash(
                $password,
                PASSWORD_DEFAULT
            ),

    ]);
}

}