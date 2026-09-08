<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AdminOrdenRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function listar(
        int $eventoId,
        ?string $estado,
        string $busqueda,
        int $pagina,
        int $porPagina
    ): array {
        $where = [
            'o.evento_id = :evento_id',
        ];

        $params = [
            'evento_id' => $eventoId,
        ];

        if ($estado !== null) {
            $where[] =
                'o.estado = :estado';

            $params['estado'] =
                $estado;
        }

        if ($busqueda !== '') {
            $where[] = '
                (
                    o.codigo LIKE :busqueda
                    OR c.nombre LIKE :busqueda
                    OR c.apellido LIKE :busqueda
                    OR c.email LIKE :busqueda
                )
            ';

            $params['busqueda'] =
                '%' . $busqueda . '%';
        }

        $whereSql =
            implode(
                ' AND ',
                $where
            );

        /*
         * TOTAL
         */

        $sqlTotal = '
            SELECT COUNT(*)

            FROM ordenes o

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

        /*
         * LISTADO
         */

        $offset =
            ($pagina - 1)
            * $porPagina;

        $sql = '
            SELECT
                o.id,
                o.codigo,
                o.estado,
                o.origen,
                o.cantidad_accesos,
                o.precio_unitario,
                o.total,
                o.moneda,
                o.pagada_en,
                o.creado_en,

                c.nombre
                    AS comprador_nombre,

                c.apellido
                    AS comprador_apellido,

                c.email
                    AS comprador_email

            FROM ordenes o

            LEFT JOIN compradores c
                ON c.id = o.comprador_id

            WHERE ' . $whereSql . '

            ORDER BY o.id DESC

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

    public function contarPorEstado(
        int $eventoId
    ): array {
        $sql = '
            SELECT
                estado,
                COUNT(*) AS cantidad

            FROM ordenes

            WHERE evento_id = :evento_id

            GROUP BY estado
        ';

        $statement =
            $this->pdo->prepare($sql);

        $statement->execute([
            'evento_id' =>
                $eventoId,
        ]);

        $resultado = [];

        foreach (
            $statement->fetchAll()
            as $fila
        ) {
            $resultado[
                (string) $fila['estado']
            ] =
                (int) $fila['cantidad'];
        }

        return $resultado;
    }
}