<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class EmailRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function compraConfirmadaExiste(
        int $ordenId
    ): bool {
        $sql = '
            SELECT COUNT(*)
            FROM emails
            WHERE orden_id = :orden_id
              AND tipo = :tipo
              AND estado IN (
                  "pendiente",
                  "procesando",
                  "enviado",
                  "reintentando"
              )
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'orden_id' =>
                $ordenId,

            'tipo' =>
                'compra_confirmada',
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function crearCompraConfirmada(
        int $ordenId,
        string $destinatario,
        string $asunto,
        array $datosPlantilla
    ): int {
        $sql = '
            INSERT INTO emails (
                orden_id,
                acceso_id,
                tipo,
                destinatario,
                asunto,
                plantilla,
                datos_plantilla,
                estado,
                intentos,
                max_intentos,
                programado_para
            )
            VALUES (
                :orden_id,
                NULL,
                :tipo,
                :destinatario,
                :asunto,
                :plantilla,
                :datos_plantilla,
                :estado,
                0,
                5,
                CURRENT_TIMESTAMP
            )
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'orden_id' =>
                $ordenId,

            'tipo' =>
                'compra_confirmada',

            'destinatario' =>
                $destinatario,

            'asunto' =>
                $asunto,

            'plantilla' =>
                'compra_confirmada',

            'datos_plantilla' =>
                json_encode(
                    $datosPlantilla,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                ),

            'estado' =>
                'pendiente',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function obtenerPendientes(
    int $limite = 10
): array {
    $sql = '
        SELECT
            id,
            orden_id,
            tipo,
            destinatario,
            asunto,
            plantilla,
            datos_plantilla,
            estado,
            intentos,
            max_intentos

        FROM emails

        WHERE estado IN (
            "pendiente",
            "reintentando"
        )
          AND programado_para <= CURRENT_TIMESTAMP
          AND intentos < max_intentos

        ORDER BY programado_para ASC, id ASC

        LIMIT :limite
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->bindValue(
        'limite',
        $limite,
        PDO::PARAM_INT
    );

    $statement->execute();

    return $statement->fetchAll();
}

public function marcarProcesando(
    int $emailId
): void {
    $sql = '
        UPDATE emails
        SET
            estado = :estado,
            intentos = intentos + 1

        WHERE id = :id
          AND estado IN (
              "pendiente",
              "reintentando"
          )

        LIMIT 1
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'id' =>
            $emailId,

        'estado' =>
            'procesando',
    ]);
}

public function marcarEnviado(
    int $emailId
): void {
    $sql = '
        UPDATE emails
        SET
            estado = :estado,
            enviado_en = CURRENT_TIMESTAMP,
            ultimo_error = NULL

        WHERE id = :id

        LIMIT 1
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'id' =>
            $emailId,

        'estado' =>
            'enviado',
    ]);
}

public function marcarFallido(
    int $emailId,
    string $error,
    bool $reintentar
): void {
    $sql = '
        UPDATE emails
        SET
            estado = :estado,
            ultimo_error = :ultimo_error,
            programado_para =
                CASE
                    WHEN :reintentar = 1
                    THEN DATE_ADD(
                        CURRENT_TIMESTAMP,
                        INTERVAL 5 MINUTE
                    )
                    ELSE programado_para
                END

        WHERE id = :id

        LIMIT 1
    ';

    $statement =
        $this->pdo->prepare($sql);

    $statement->execute([
        'id' =>
            $emailId,

        'estado' =>
            $reintentar
                ? 'reintentando'
                : 'fallido',

        'ultimo_error' =>
            $error,

        'reintentar' =>
            $reintentar
                ? 1
                : 0,
    ]);
}
}