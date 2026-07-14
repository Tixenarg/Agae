$(document).ready(function () {
  // detalle();
  // buscosocio();
  foco();
  $("#dataTablecodigo").DataTable({
    language: {
      sProcessing: "Procesando...",
      sLengthMenu: "Mostrar _MENU_ registros",
      sZeroRecords: "No se encontraron resultados",
      sEmptyTable: "Ningún dato disponible en esta tabla",
      sInfo:
        "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
      sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
      sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
      sInfoPostFix: "",
      sSearch: "Buscar:",
      sUrl: "",
      sInfoThousands: ",",
      sLoadingRecords: "Cargando...",
      oPaginate: {
        sFirst: "Primero",
        sLast: "Último",
        sNext: "Siguiente",
        sPrevious: "Anterior",
      },
      oAria: {
        sSortAscending:
          ": Activar para ordenar la columna de manera ascendente",
        sSortDescending:
          ": Activar para ordenar la columna de manera descendente",
      },
    },
    responsive: true,
  });
  
  $("#cantidad").keypress(function (e) {
    if (e.which == 13) {
      setTimeout(function() { $('#carga_codigo').focus() }, 500);
    }
  });
  
  $("#codigo").keypress(function (e) {
    if (e.which == 13) {
      traigoCodigo();
    }
  });

  $('#personas').select2();
  $('#tipoventa').select2();

  $("form").keypress(function (e) {
    if (e == 13) {
      return false;
    }
  });

  $("input").keypress(function (e) {
    if (e.which == 13) {
      return false;
    }
  });

  $("#detalle_ajax").hide();
  
});

$(document).on('focus', '.select2', function (e) {
  if (e.originalEvent) {
    $(this).siblings('select').select2('open');    
  } 
});
function fcnPrueba() {
  alert("aca entre a la fcnPrueba .....!!!!!");
}

function borro(numero) {
  // delete linea[numero];

  linea.splice(numero, 1);
  MostrarTabla(linea);
  // alert('envie el numero de indice a borrar....'+numero );
};
function limpioLinea(){
  $('#codigo').val("");
  $('#descripcion').val("");
  $('#importe').val("");
  $('#cantidad').val("");

};

var total = 0;

function MostrarTabla(linea) {
  if (linea.length > 0) {
    $("#detalle_ajax").show();
    var d = "";
    total=0;
    console.log("cantidad de la linea recibida : ......" + linea.length);
    for (var i = 0; i < linea.length; i++) {
      var subtotal = (linea[i].importe *  linea[i].cantidad );
      d +=
        "<tr>" +
        "<td>" +
        linea[i].codigo +
        "</td>" +
        "<td>" +
        linea[i].descripcion +
        "</td>" +
        "<td>" +
        linea[i].importe.toFixed(2) +
        "</td>" +
        "<td>" +
        linea[i].cantidad.toFixed(2) +
        "</td>" +
        "<td><div class='text-right'>" +
        '$ '+subtotal.toFixed(2) +
        "</div></td>" +
        "<td><div class='text-center'><div class='btn-group'><button class='btn btn-outline-danger btn-sm btnEliminar' onclick='borro(" +
        i +
        ");'><i class='far fa-trash-alt'></i></button></div></div></td>";
      ("</tr>");
      total=total+subtotal;
    }
    // $("#ajax_lineas").append(d);
     d+="<tr>"+"<td>"+"</td>"+"<td>"+"</td>"+"<td>"+"</td>"+"<td>"+"</td>"+"<td>"+"<strong>TOTAL . $ "+total.toFixed(2)+"</strong></td>"+"<td>"+"</td>";      

    $("#ajax_lineas").html(d);
  } else {
    $("#detalle_ajax").hide();
  }
}

var linea = new Array();
var totalVenta=0;


$("#carga_codigo").click(function (e) {
  e.preventDefault();
  var codigo = parseInt($("#codigo").val());
  var descripcion = $("#descripcion").val();
  var importe = parseFloat($("#importe").val());
  var cantidad = parseFloat($("#cantidad").val());

  objLinea = {
    codigo: codigo,
    descripcion: descripcion,
    importe: importe,
    cantidad: cantidad,
  };

  linea.push(objLinea);
  console.log(linea);
  // $("#frm-linea")[0].reset();
  limpioLinea();
  $("#codigo").focus();
  MostrarTabla(linea);
});

$("#grabar").click(function (e) {
  e.preventDefault();
  // var objparseado = JSON.parse(linea);
  
  var tipoventa=0;
  var fecha = $('#fecha').val();
  var cliente = parseInt($('#personas').val());
  var detalle = JSON.stringify(linea);
  if( $("#ctacte").is(':checked')) {tipoventa=1};
  if (cliente==0 && tipoventa==1){
  
    Swal.fire({
      icon: "error",
      title: "Atención...",
      text: "La venta es en cta-cte. Debe seleccionar un Cliente Valido!",
    });
  
    
  }else{
    $.ajax({
      type: "POST",
      url: "ajax/registraVenta.php",
      data: { fecha: fecha, cliente: cliente, detalle: detalle, total:total, tipoventa:tipoventa },
      success: function (response) {
        linea = []; // vacia
        MostrarTabla(linea);
        Swal.fire({
          icon: 'success',
          title: 'Ok...',
          text: 'Se registro la FACTURA Nro :'+response
          
        });
        setTimeout('location.reload()',1000);	
        //console.log(response);
      },
    });
  }

  // console.log("esta es el objeto parseado JSON.stringify..... : " + detalle);
});

function focoImporte() {
  $("#importe").focus();
  $("#importe").select();
}
function foco() {
  $("#codigo").focus();
};

$(document).on("click", ".btnElegir", function () {
  fila = $(this).closest("tr");
  id = parseInt(fila.find("td:eq(0)").text());
  $("#codigo").val(id);
  $("#modalCodigo").modal("hide");
  traigoCodigo();
  setTimeout(function() { $('#carga_codigo').focus() }, 500);
});

////////////////////fcnpara traer buscar el codigo en sustituye al evento blur de codigo/////////////////////////
function traigoCodigo() {
  var codigo = parseInt($("#codigo").val());
  if (codigo == null || codigo.length == 0 || /^\s+$/.test(codigo) || isNaN(codigo)) {
    limpioLinea();
    // $("#frm-linea").trigger("reset");
    alert("el codigo esta vacio");
  } else {
    $.ajax({
      type: "POST",
      url: "ajax/busco_codigo.php",
      data: { codigo: codigo },
      success: function (respuesta) {
        var cod = JSON.parse(respuesta);
        var template = "";
        if (cod != "") {
          $("#descripcion").val(cod[0].descripcion);
          $("#importe").val(cod[0].importe);
          $("#cantidad").val(1);
          setTimeout(function() { $('#carga_codigo').focus() }, 200);
          // $("#carga_codigo").focus();
        } else {
          limpioLinea();
          // $("#frm-linea")[0].reset();
          $("#codigo").val("");
          Swal.fire({
            icon: "error",
            title: "Atención...",
            text: "El Código de Artículo no existe!",
          });
        }
      }, ////fin del success
    }); /////fin de ajax
  } ////fin del else
};


