$(document).ready(function () {
	// Mostrar formulario para editar
	$("#btnEditarImporte").click(function () {
		$("#formEditarImporte").slideDown();
		$("#nuevoImporte").focus();
	});

	// Cancelar edición
	$("#btnCancelarImporte").click(function () {
		$("#formEditarImporte").slideUp();
		$("#nuevoImporte").val("");
	});

	// Guardar nuevo importe por AJAX
	$("#btnGuardarImporte").click(function () {
		const nuevoImporte = $("#nuevoImporte").val().trim();
		if (!nuevoImporte || parseFloat(nuevoImporte) <= 0) {
			Swal.fire({
				icon: "warning",
				title: "Importe inválido",
				text: "Por favor, ingrese un importe válido mayor a 0.",
			});
			return;
		}

		$.ajax({
			url: "ajax/actualizar_importe_banco.php",
			type: "POST",
			data: { importe: nuevoImporte },
			beforeSend: function () {
				$("#btnGuardarImporte").prop("disabled", true);
			},
			success: function (resp) {
				try {
					const data = JSON.parse(resp);
					if (data.status === "success") {
						Swal.fire({
							icon: "success",
							title: "Importe actualizado",
							showConfirmButton: false,
							timer: 1200,
						}).then(() => {
							location.reload(); // 🔁 refrescar página para mostrar el nuevo valor
						});
					} else {
						Swal.fire({
							icon: "error",
							title: "Error",
							text: data.message || "No se pudo actualizar el importe.",
						});
					}
				} catch (e) {
					Swal.fire({
						icon: "error",
						title: "Error inesperado",
						text: resp,
					});
				}
			},
			error: function () {
				Swal.fire({
					icon: "error",
					title: "Error de conexión",
					text: "No se pudo contactar con el servidor.",
				});
			},
			complete: function () {
				$("#btnGuardarImporte").prop("disabled", false);
			},
		});
	});

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
