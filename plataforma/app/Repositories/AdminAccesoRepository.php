<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AdminAccesoRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function listar(
        int $eventoId,
        string $busqueda,
        ?string $estado,
        ?string $checkin,
        int $pagina,
        int $porPagina
    ): array {
        $where = [
            'o.evento_id = :evento_id',
        ];

        $params = [
            'evento_id' => $eventoId,
        ];

        if ($busqueda !== '') {
            $where[] = '
                (
                    a.codigo LIKE :busqueda
                    OR a.nombre LIKE :busqueda
                    OR a.apellido LIKE :busqueda
                    OR a.dni_individual LIKE :busqueda
                    OR c.email LIKE :busqueda
                )
            ';

            $params['busqueda'] =
                '%' . $busqueda . '%';
        }

        if ($estado !== null) {
            $where[] =
                'a.estado = :estado';

            $params['estado'] =
                $estado;
        }

        if ($checkin === 'si') {
            $where[] =
                'a.utilizado_en IS NOT NULL';
        }

        if ($checkin === 'no') {
            $where[] =
                'a.utilizado_en IS NULL';
        }

        $whereSql =
            implode(
                ' AND ',
                $where
            );

        $sqlTotal = '
            SELECT COUNT(*)

            FROM accesos a

            INNER JOIN ordenes o
                ON o.id = a.orden_id

            LEFT JOIN compradores c
                ON c.id = o.comprador_id

            WHERE ' . $whereSql;

        $statementTotal =
            $this->pdo->prepare(
                $sqlTotal
            );

        $statementTotal->execute(
            $params
        );

        $total =
            (int) $statementTotal
                ->fetchColumn();

        $offset =
            ($pagina - 1)
            * $porPagina;

        $sql = '
            SELECT
                a.id,
                a.codigo,
                a.nombre,
                a.apellido,
                a.estado,
                a.utilizado_en,
                a.pdf_generado_en,
                a.email_enviado_en,

                ta.nombre AS tipo_acceso_nombre,

                o.codigo AS orden_codigo,

                c.email AS comprador_email

            FROM accesos a

            INNER JOIN ordenes o
                ON o.id = a.orden_id

            INNER JOIN tipos_acceso ta
                ON ta.id = a.tipo_acceso_id

            LEFT JOIN compradores c
                ON c.id = o.comprador_id

            WHERE ' . $whereSql . '

            ORDER BY a.id DESC

            LIMIT :limite
            OFFSET :offset
        ';

        $statement =
            $this->pdo->prepare(
                $sql
            );

        foreach ($params as $key => $value) {
            $statement->bindValue(
                ':' . $key,
                $value
            );
        }

        $statement->bindValue(
            ':limite',
            $porPagina,
            PDO::PARAM_INT
        );

        $statement->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $statement->execute();

        return [
            'items' =>
                $statement->fetchAll(),

            'total' =>
                $total,

            'pagina' =>
                $pagina,

            'por_pagina' =>
                $porPagina,

            'paginas' =>
                max(
                    1,
                    (int) ceil(
                        $total
                        / $porPagina
                    )
                ),
        ];
    }
}