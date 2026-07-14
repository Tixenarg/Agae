////////////herramientas js/////////////////////////

/////////// devuelve la primer letra ingresada en mayuscula//////////
$("#nombres").on("keyup", function (e) {
  var txt = $(this).val();
  $(this).val(
    txt.replace(/^(.)|\s(.)/g, function ($1) {
      return $1.toUpperCase();
    })
  );
});

//////////// valida si esta vacio ////////////////////////////////
if (dni == null || dni.length == 0 || /^\s+$/.test(dni)) {
  //alert('El dni esta vacio desde aca');
  $("#dni").focus();
  Swal.fire({
    icon: "warning",
    title: "Atención...",
    text: "El Nro de DNI esta vacio",
    //footer: '<a href>Why do I have this issue?</a>'
  });
  return false;
}

///////////// obtengo en id en una tabla////////////////////////////
$(document).on("click", ".borro-item", function () {
  var elemento = $(this)[0].parentElement.parentElement;
  var id = $(elemento).attr("idcodigo");
  $.post("ajax/borro_item.php", { id }, function (respuesta) {
    detalle();
    //console.log(respuesta);
  });
});

/////////////////// si no es numero//////////////////////////////
if (!$.isNumeric(recibo)) {
  foco();
  Swal.fire({
    icon: "warning",
    title: "Atención...",
    text: "Debe ingresar un número de recibo valido",
  });
  return false;
}

//////////////////////7serializar un formulario/////////////////////////////////////
$("#frmsocio_actividad").submit(function (event) {
  // $('#guardar_datos').attr("disabled", true);

  var parametros = $(this).serialize();
  $.ajax({
    type: "POST",
    url: "ajax/socio_actividad.php",
    data: parametros,
    beforeSend: function (objeto) {
      $("#resultados_ajax").html("Mensaje: Cargando...");
    },
    success: function (datos) {
      $("#resultados_ajax").html(datos);
      $("#guardar_datos").hide;
      refrescar_tabla();
    },
  });
  event.preventDefault();
});

///////////////////llenar combo///////////////////////////////

function cargacombo() {
  $.ajax({
    type: "POST",
    url: "ajax/cbo-actividades.php",
    success: function (response) {
      $("#cbo").html(response);
    },
  });
}

/////////////////obtener id de una tabla//////////////////////////////
$(document).on("click", ".btnSeleccionar", function () {
  fila = $(this).closest("tr");
  id = fila.find("td:eq(0)").text();
  //id = parseInt(fila.find('td:eq(0)').text());
  // url = "op.php?dni="+id;
  // $(location).attr('href',url);

  $("#codigo").val(id);
  $("#Modal-consulta-articulos").modal("hide");
  $("#codigo").blur();
});

////////////////////////// Borro solo un item del detalle de pagos
$(document).on("click", ".borro-item", function () {
  var elemento = $(this)[0].parentElement.parentElement;
  var id = $(elemento).attr("idcodigo");
  $.post("ajax/borro_item.php", { id }, function (respuesta) {
    detalle();
    //console.log(respuesta);
  });
});

////////////////////////// traigo un json para procesarlo////////////
$("#codigo").blur(function () {
  var codigo = $("#codigo").val();
  if (codigo == "") {
    $("#frm-op").trigger("reset");
  } else {
    $.ajax({
      type: "POST",
      url: "ajax/busco_codigo.php",
      data: { codigo: codigo },
      success: function (respuesta) {
        var cod = JSON.parse(respuesta);
        // var template = "";
        if (cod != "") {
          $("#descripcion").val(cod[0].descripcion);
          $("#importe").val(cod[0].importe);
          focoImporte();
        } else {
          Swal.fire({
            icon: "error",
            title: "Atención...",
            text: "El Código de cobranza no exixte!",
          });

          // template += `
          //             <div class="alert alert-danger" role="alert">
          //             <button type="button" class="close" data-dismiss="alert">&times;</button>
          //             <strong>Error! No Existe el codigo ingresado </strong>
          //             </div>`;
          $("#codigo").val("");
          $("#codigo").focus();
        }

        // $('#codNombre').html(template);
      }, ////fin del success
    }); /////fin de ajax
  } ////fin del else
});

//////////////////////////////inicializar datatable y en español//////////////////////////////
    $('#dataInventario').DataTable({
        language:{
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                    },
        responsive: true
    });

    $(document).on("click", ".btnBorrar", function () {
      fila = $(this);
      id = parseInt($(this).closest("tr").find("td:eq(0)").text());
      opcion = 3; //eliminar

      Swal.fire({
      icon: "question",
      title: "Está seguro que desea Eliminar el registro " + id + " ?",
      showCancelButton: true,
      confirmButtonText: `Eliminar`,
      }).then((result) => {
      /* Read more about isConfirmed, isDenied below */

      if (result.isConfirmed) {
          $.ajax({
          url: "./ajax/AbmOficinas.php",
          type: "POST",
          datatype: "json",

          data: { opcion: opcion, id: id },
          success: function () {
              tablaOficinas.row(fila.parents("tr")).remove().draw();
          },
          });
          Swal.fire("Registro Borrado!", "", "success");
      }
      });
  });

  ///////ejecuta la funcion con una espera en milisegundos

  setTimeout(function() { $('#carga_codigo').focus() }, 500);