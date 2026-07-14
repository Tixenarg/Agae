<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->

<div class="container">



  <body class="bg-light">

    <div class="container mt-5">
      <h3 class="text-center mb-4">Consulta de Afiliados</h3>

      <div class="row g-3 mb-4">
        <!-- Combo Estado -->
        <div class="col-md-6">
          <label for="estado" class="form-label fw-bold">Estado del Afiliado</label>
          <select id="estado" name="estado" class="form-control">

          </select>
        </div>

        <!-- Combo Forma de Pago -->
        <div class="col-md-6">
          <label for="forma_pago" class="form-label fw-bold">Forma de Pago</label>
          <select id="forma_pago" name="forma_pago" class="form-control">

          </select>
        </div>
      </div>

      <div class="text-center mb-4">
        <button id="btnExportar" type="button" class="btn btn-success px-3"><i class="far fa-file-excel"></i> Exportar a Excel </i></button>
      </div>

      <!-- Resultado -->
      <div id="resultado" class="mt-4"></div>
    </div>
  </body>
</div>

</html>
<?php require_once("include/parte_inferior.php"); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function() {

    $('#btnExportar').click(function(e) {
      e.preventDefault(); // evita comportamiento por defecto si es botón dentro de un form
      var estado = $.trim($("#estado").val());
      var pago = $.trim($("#forma_pago").val());
      // Construimos la URL con los parámetros GET
      var url = "exportar_a_excel.php?estado=" + encodeURIComponent(estado) + "&pago=" + encodeURIComponent(pago);
      // Redirigimos a la nueva URL
      window.location.href = url;
    });


    $.ajax({
      url: 'ajax/cargar_estados.php',
      type: 'GET',
      dataType: 'json',
      success: function(response) {
    /*     console.log(response); */

        // Aseguramos que venga el array de datos
        if (response.status === 'success' && Array.isArray(response.data)) {
          let opciones = '<option value=0>-- Todos --</option>';
          response.data.forEach(function(estado) {
            opciones += `<option value="${estado.id_estado}">${estado.estado_nombre}</option>`;
          });
          $('#estado').html(opciones);
        } else {
          alert('No se recibieron estados válidos desde el servidor.');
          console.error('Respuesta inesperada:', response);
        }
      },

      error: function() {
        alert('Error al cargar los estados.');
      }
    });


    $.ajax({
      url: 'ajax/cargar_forma_pago.php',
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        // Aseguramos que venga el array de datos
        if (response.status === 'success' && Array.isArray(response.data)) {
          let opciones = '<option value=0>-- Todos --</option>';
          response.data.forEach(function(fpago) {
            opciones += `<option value="${fpago.id_fpago}">${fpago.fpago_nombre}</option>`;
          });
          $('#forma_pago').html(opciones);
        } else {
          alert('No se recibieron estados válidos desde el servidor.');
          console.error('Respuesta inesperada:', response);
        }
      },

      error: function() {
        alert('Error al cargar los estados.');
      }
    });


    $('#btn-consultar').click(function() {
      const estado = $('#estado').val();
      const forma_pago = $('#forma_pago').val();

      if (estado === "" || forma_pago === "") {
        alert("Debe seleccionar ambos filtros antes de continuar.");
        return;
      }

      $.ajax({
        url: 'ajax/consulta_afiliados.php', // ruta actualizada
        type: 'POST',
        data: {
          estado: estado,
          forma_pago: forma_pago
        },
        beforeSend: function() {
          $('#resultado').html('<div class="text-center text-secondary">Cargando...</div>');
        },
        success: function(data) {
          $('#resultado').html(data);
        },
        error: function() {
          $('#resultado').html('<div class="alert alert-danger">Error al realizar la consulta.</div>');
        }
      });
    });
  });
</script>