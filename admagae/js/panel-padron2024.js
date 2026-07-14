$(document).ready(function () {
    
    var  opcion;
    opcion = 4;

    // $('#dni').mask("00.000.000"); // le da una mascara de ingreso 


    tablaPadron2024 = $("#tablaPadron2024").DataTable({
        ajax: {
        url: "./ajax/abmPadron2024.php",
        method: "POST", //usamos el metodo POST
        data: { opcion: opcion }, //enviamos opcion 4 para que haga un SELECT
        dataSrc: "",
        },
        columns: [
        { data: "nombres" },
        { data: "dni" },
        { data: "T" },
        { data: "F" },
        { data: "celular" }
        ],
        language: {
        lengthMenu: "Mostrar _MENU_ registros",
        zeroRecords: "No se encontraron resultados",
        info: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
        infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
        infoFiltered: "(filtrado de un total de _MAX_ registros)",
        sSearch: "Buscar:",
        oPaginate: {
            sFirst: "Primero",
            sLast: "Último",
            sNext: "Siguiente",
            sPrevious: "Anterior",
        },
        sProcessing: "Procesando...",
        },
    });



});
