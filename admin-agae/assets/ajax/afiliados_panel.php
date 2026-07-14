<?php
require_once '../db/conexion.php'; // ruta actualizada

$estado = $_POST['estado'] ?? '';
$forma_pago = $_POST['forma_pago'] ?? '';

if ($estado == '' || $forma_pago == '') {
  echo "<div class='alert alert-warning'>Debe seleccionar ambos filtros.</div>";
  exit;
}

$sql = "SELECT dni, nombre, apellido, estado, forma_pago 
        FROM afiliados
        WHERE afi_estado = '$estado' AND afi_forma_pago = '$forma_pago'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
  echo "<table class='table table-striped table-bordered'>
          <thead class='table-primary'>
            <tr>
              <th>DNI</th>
              <th>Nombre</th>
              <th>Apellido</th>
              <th>Estado</th>
              <th>Forma de Pago</th>
            </tr>
          </thead>
          <tbody>";
  while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>{$row['afi_dni']}</td>
            <td>{$row['afi_nombres']}</td>
            <td>{$row['afi_apellidos']}</td>
            <td>{$row['afi_estado']}</td>
            <td>{$row['afi_forma_pago']}</td>
          </tr>";
  }
  echo "</tbody></table>";
} else {
  echo "<div class='alert alert-info'>No se encontraron resultados.</div>";
}
?>
