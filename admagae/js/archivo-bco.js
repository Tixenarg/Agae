$(document).ready(function () {
	function mes() {
		const fecha = new Date();
		const mesActual = fecha.getMonth() + 1;
		var mes;
		mes = mesActual < 10 ? String("0" + mesActual) : String(mesActual);
		$("#mes").val(mes);
	}

	function inicial() {
		$("#mes").prop("disabled", false);
		$("#nroArchivo").prop("disabled", false);
		$("#fechaTope").prop("disabled", false);

		$("#btndescarga").hide();
		$("#resultado").hide();
		$("#nuevocrear").hide();

		$("#crear").show();
		mes();
	}

	inicial();
	$("#nuevocrear").click(function (e) {
		inicial();
	});

	$("#crear").click(function (e) {
		e.preventDefault();
		var casa = $("#casaSucursal").val();
		var tipomoneda = $("#TipoMoneda").val();
		var cuenta = $("#cuenta").val();
		var moneda = $("#moneda").val();
		var secuencia = $("#mes").val() + $("#nroArchivo").val();
		var fecha = $("#fechaTope").val();
		var importe = $("#importe").val();

		var meses = $("#mes").val();

		// console.log("sucursal " + casa);
		// console.log("tipo de moneda " + tipomoneda);
		// console.log("cuenta " + cuenta);
		// console.log("tipo de moneda " + moneda);
		// console.log("secuencia " + secuencia);

		// console.log("fecha " + fecha);

		// console.log("importe " + importe);

		function validaDatos(fecha) {
			if (fecha == null || fecha == "") {
				$("#fechaTope").focus();
				Swal.fire({
				icon: "warning",
				title: "Atención...",
				text: "Debe Ingresar una Fecha tope Rendición ",
				});

				return false;
			} else {
				return true;
			}
			}

			if (validaDatos(fecha)) {
			$("#crear").hide();
			$("#btndescarga").show();
			$("#mes").prop("disabled", true);
			$("#nroArchivo").prop("disabled", true);
			$("#fechaTope").prop("disabled", true);
			$("#nuevocrear").show();

			$.ajax({
				type: "POST",
				url: "crear_archivo.php",
				data: {
				casa: casa,
				tipomoneda: tipomoneda,
				cuenta: cuenta,
				moneda: moneda,
				secuencia: secuencia,
				fecha: fecha,
				importe: importe,
				},
				beforeSend: function (data) {
				$("#resultados_ajax").html("Mensaje: Cargando...");
				},
				success: function (datos) {
				$("#resultado").show();
				$("#resultados_ajax").html(datos);
				},
			});
			}
	});
});
