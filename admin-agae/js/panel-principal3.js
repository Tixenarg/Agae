// Eventos después que el DOM está listo
$(document).ready(function () {
	// Si necesitás usar los botones o ediciones, van acá
	$(document).on("click", ".btnPago", function () {
		fila = $(this).closest("tr");
		id = parseInt(fila.find("td:eq(0)").text()); //capturo el ID
		window.location = "modifica-estado-pago.php?id=" + id;
	});
	$(document).on("click", ".btnEditar", function () {
		const fila = $(this).closest("tr");
		const id = parseInt(fila.find("td:eq(0)").text());
		window.location = "frm-modifica-afiliado.php?id=" + id;
	});

	$(document).on("click", ".btnImprimir", function () {
		const fila = $(this).closest("tr");
		const id = parseInt(fila.find("td:eq(0)").text());
		window.location = "reimprimir-ficha-afiliacion.php?id=" + id;
	});
});

let tablaPadron; // Variable global

function mostrartabla(opcion) {
	const contenedor = document.getElementById("contenedortabla");
	const titulo = document.getElementById("titulo");
	const encabezado = document.getElementById("encabezado");

	// Mostrar contenedor
	contenedor.style.display = "block";

	// 🔹 Quitar clases previas bg-*
	encabezado.classList.forEach((cls) => {
		if (cls.startsWith("bg-")) encabezado.classList.remove(cls);
	});

	// 🔹 Cambiar título y color
	switch (parseInt(opcion)) {
		case 1:
			titulo.textContent = "SOLICITUDES DE AFILIACIÓN";
			encabezado.classList.add("bg-success", "text-white");
			break;
		case 2:
			titulo.textContent = "AFILIADOS";
			encabezado.classList.add("bg-primary", "text-white");
			break;
		case 3:
			titulo.textContent = "DESAFILIADOS";
			encabezado.classList.add("bg-danger", "text-white");
			break;
		default:
			titulo.textContent = "AFILIADOS AGAE";
			encabezado.classList.add("bg-secondary", "text-white");
			break;
	}

	console.log("Mostrando tabla con opción:", opcion);

	// 🔹 Si la tabla ya existe, actualizamos el parámetro y recargamos datos
	if ($.fn.DataTable.isDataTable("#tablaPadron")) {
		tablaPadron.settings()[0].ajax.data = { opcion: opcion }; // cambia el parámetro
		tablaPadron.ajax.reload(null, true); // recarga sin reiniciar paginación
		return;
	}

	// 🔹 Si no existe, la creamos
	tablaPadron = $("#tablaPadron").DataTable({
		ajax: {
			url: "./ajax/abmafiliados.php",
			method: "POST",
			data: { opcion: opcion }, // pasa el valor inicial
			dataSrc: "",
		},
		columns: [
			{ data: "id" },
			{ data: "dni" },
			{ data: "apellidos" },
			{ data: "nombres" },
			{ data: "ctacte" },
			{ data: "fecha" },
			{
				title: "Acciones...",
				defaultContent:
					"<div class='text-center'><div class='btn-group'>" +
					"<button class='btn btn-danger btn-sm btnPago' title='Cambiar Estado del Afiliado y Forma de pago'><i class='fas fa-check'></i></button>" +
					"<button class='btn btn-primary btn-sm btnEditar'><i class='far fa-edit'></i></button>" +
					"<button class='btn btn-success btn-sm btnImprimir'><i class='fas fa-print'></i></button>" +
					"</div></div>",
			},
		],
		destroy: true, // permite recrear si hace falta
		language: {
			lengthMenu: "Mostrar _MENU_ registros",
			zeroRecords: "No se encontraron resultados",
			info: "Mostrando del _START_ al _END_ de _TOTAL_ registros",
			infoEmpty: "Mostrando 0 registros",
			infoFiltered: "(filtrado de _MAX_ totales)",
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
}
