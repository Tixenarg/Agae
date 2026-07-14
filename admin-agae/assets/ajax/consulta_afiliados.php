<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');

require_once '../db/conexion_afiliados.php';

try {
    // Validar que los parámetros existan y no estén vacíos
    if (empty($_POST['estado']) || empty($_POST['forma_pago'])) {
        throw new Exception('Faltan parámetros obligatorios.');
    }

    $estado = (int)$_POST['estado'];
    $forma_pago = (int)$_POST['forma_pago'];

    // Consulta con JOINs de ejemplo (ajustá los nombres de las tablas según tu estructura)
    $sql = "SELECT 
                a.id,
                a.afi_apellidos,
                a.afi_nombres,
                e.estado_nombre,
                f.fpago_nombre
            FROM afiliados a
            INNER JOIN afiliado_estados e ON a.afi_estado = e.id_estado
            INNER JOIN afiliado_forma_de_pago f ON a.afi_forma_pago = f.id_fpago
            WHERE a.afi_estado = ? AND a.afi_forma_pago = ?
            ORDER BY a.afi_apellidos, a.afi_nombres";

    $stmt = $link->prepare($sql);
    echo $stmt;
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $link->error);
    }

    $stmt->bind_param('ii', $estado, $forma_pago);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Si no hay registros
    if ($resultado->num_rows === 0) {
        echo '<div class="alert alert-warning text-center">No se encontraron resultados.</div>';
        exit;
    }

    // Render de tabla
    echo '<table class="table table-bordered table-striped table-hover">';
    echo '<thead class="table-dark">';
    echo '<tr>
            <th>ID</th>
            <th>Apellido</th>
            <th>Nombre</th>
            <th>Estado</th>
            <th>Forma de Pago</th>
          </tr>';
    echo '</thead><tbody>';

    while ($row = $resultado->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars((string)$row['id_afiliado']) . '</td>';
        echo '<td>' . htmlspecialchars($row['apellido']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($row['estado_nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($row['forma_nombre']) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    $stmt->close();
} catch (Exception $e) {
    echo '<div class="alert alert-danger text-center">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

$link->close();
