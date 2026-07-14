$(document).ready(function () {
    
    var id, opcion;
    opcion = 4;

    $('#dni').mask("00.000.000"); // le da una mascara de ingreso 


    tablaPadron = $("#tablaPadron").DataTable({
        ajax: {
        url: "./ajax/abmPadron.php",
        method: "POST", //usamos el metodo POST
        data: { opcion: opcion }, //enviamos opcion 4 para que haga un SELECT
        dataSrc: "",
        },
        columns: [
        { data: "id_abogado" },
        { data: "apellidos" },
        { data: "nombres" },
        { data: "dni" },
        { data: "T" },
        { data: "F" },
        { data: "celular" },
        { data: "referente" },
        {
            defaultContent:
            //"<div class='text-center'><div class='btn-group'><button class='btn btn-primary btn-sm btnEditar'><i class='far fa-edit '></i></div>",
            // </button><button class='btn btn-danger btn-sm btnBorrar'><i class='far fa-trash-alt '></i></button></div>
            "<div class='text-center'><div class='btn-group'><button class='btn btn-primary btn-sm btnEditar'><i class='far fa-edit '></i></button><button class='btn btn-danger btn-sm btnBorrar'><i class='far fa-trash-alt '></i></button></div></div>"
        },
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



    $("#dni").blur(function (e) {
        e.preventDefault();
        var dni = $("#dni").val();
        //  console.log('el dni con mascara '+dni);
        if(dni.length==0){
            Swal.fire({
                icon :"warning",
                tilte : "Atención...",
                text : "Debe Ingresar el Nro de Documento"
            });
           return false;
        }
        $.ajax({
        type: "POST",
        url: "./ajax/existe.php",
        data: { dni, dni },

        success: function (response) {
            var existe = parseInt(response);
            console.log("Repuesta desde el servidor " + existe);
            if (existe > 0) {
            $("#dni").val("");
            // setTimeout(function() { $('#dni').focus() }, 500)
            Swal.fire({
                icon: "warning",
                title: "Atención...",
                text: "El DNI Nro " + dni + " Ya está registrado",
                //footer: '<a href>Why do I have this issue?</a>'
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                
                $("#formPadron").trigger("reset");

                }
            });
            }
        },
        });
    });

    var fila; //captura la fila, para editar o eliminar
    //submit para el Alta y Actualización
    $("#formPadron").submit(function (e) {
        e.preventDefault(); //evita el comportambiento normal del submit, es decir, recarga total de la página
        var id = $.trim($("#id").val());
        var dni = $.trim($("#dni").val());
        var apellido = $("#apellido").val();
        var nombre = $("#nombre").val();
        var t = $.trim($("#t").val());
        var f = $.trim($("#f").val());
        var celular = $.trim($("#celular").val());
        var referente = $.trim($("#referente").val());

        console.log(id);
        if (t.length == 0) {
        t = 0;
        }
        if (f.length == 0) {
        f = 0;
        }

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

        if (apellido == null || apellido.length == 0 || /^\s+$/.test(apellido)) {
        //alert('El dni esta vacio desde aca');
        $("#apellido").focus();
        Swal.fire({
            icon: "warning",
            title: "Atención...",
            text: "No ingreso el Apellido",
            //footer: '<a href>Why do I have this issue?</a>'
        });
        return false;
        }

        if (nombre == null || nombre.length == 0 || /^\s+$/.test(nombre)) {
        //alert('El dni esta vacio desde aca');
        $("#nombre").focus();
        Swal.fire({
            icon: "warning",
            title: "Atención...",
            text: "No Ingreso el Nombre",
            //footer: '<a href>Why do I have this issue?</a>'
        });
        return false;
        }
        var mensaje = "";
        if (opcion == 1) {
        mensaje = "Se dió de alta un Nuevo Registro";
        } else {
        mensaje = "Se Actualizo Registro";
        }
        $.ajax({
        url: "./ajax/abmPadron.php",
        type: "POST",
        datatype: "json",
        data: {
            id:id,
            dni: dni,
            apellido: apellido,
            nombre: nombre,
            t: t,
            f: f,
            celular: celular,
            referente: referente,
            opcion: opcion,
        },
        success: function (data) {
            tablaPadron.ajax.reload(null, false);
            Swal.fire({
            icon: "success",
            title: "Correcto...",
            text: mensaje,
            });
        },
        });
        $("#modalCRUD").modal("hide");
    });

    //para limpiar los campos antes de dar de Alta una Persona
    $("#btnNuevo").click(function () {
        opcion = 1; //alta
        id = null;
        $("#formPadron").trigger("reset");
        $("#cabecera").css("background-color", "#17a2b8");
        $("#cabecera").css("color", "white");

        $("#exampleModalLabel").text("Alta de Registro de Abogados");
        $("#modalCRUD").modal("show");
        setTimeout('$("#dni").focus()', 500);
    });

    //Editar
    $(document).on("click", ".btnEditar", function () {
        opcion = 2; //editar
        fila = $(this).closest("tr");
        id = parseInt(fila.find("td:eq(0)").text()); //capturo el ID
        apellido = fila.find("td:eq(1)").text();
        nombre = fila.find("td:eq(2)").text();
        dni = fila.find("td:eq(3)").text();
        t = fila.find("td:eq(4)").text();
        f = fila.find("td:eq(5)").text();
        celular = fila.find("td:eq(6)").text();
        referente = fila.find("td:eq(7)").text();
        
        $("#id").val(id);
        $("#dni").val(dni);
        $("#nombre").val(nombre);
        $("#apellido").val(apellido);
        $("#dni").val(dni);
        $("#t").val(t);
        $("#f").val(f);
        $("#celular").val(celular);
        $("#referente").val(referente);
        $("#cabecera").css("background-color", "#007bff");
        $("#cabecera").css("color", "white");
        $("#exampleModalLabel").text("Editar Registro de Abogado");
        $("#modalCRUD").modal("show");
        setTimeout('$("#apellido").focus()', 500);
    });

    //Borrar
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
            url: "./ajax/abmPadron.php",
            type: "POST",
            datatype: "json",
            data: { opcion: opcion, id: id },
            success: function () {
                tablaPadron.ajax.reload(null, false);
                
            },
            });
            Swal.fire("Registro Borrado!", "", "success");
        }
        });
    });

        $(".UpperCase").on("keypress", function () {
        $input=$(this);
        setTimeout(function () { $input.val($input.val().toUpperCase()) },50);
        });
    
        

});
