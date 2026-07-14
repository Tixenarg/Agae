<?php require_once("include/parte_superior.php"); ?>
<!-- Inicio del contenido Principal -->

<div class="container">

	<body id="home">
		<div class="container d-flex justify-content-center my-3">
			<!-- <a class="navbar-brand align-self-center" href="#"><img src="assets/img/navbar-logo.svg" style="height: 100px; width: auto" /></a> -->
		</div>
		<div class="paragraph my-2" style="text-align: center">
			<strong>Asociación Gremial de Abogados del Estado(AGAE) Inscripción N° 3097</strong>
		</div>

		<h2 class="my-3" style="text-align: center">Solicitud de afiliación&nbsp;</h2>
		<div class="container">
			<div class="paragraph px-5" style="text-align: justify">
				Por la presente solicito se acepte mi afiliación a esta organización
				gremial, declarando conocer su Estatuto y disposiciones legales vigentes, a
				las que ajustaré mi actuación. A tal fin, detallo los datos personales y
				laborales pertinentes:&nbsp;<br />​ Campos Obligatorios (<span class="text-danger">*</span>)<br /><br />
			</div>
		</div>

		<div class="container">
			<form id="frmafiliado" action="alta_afiliacion.php" method="post" autocomplete="off" class="row g-3 px-5 needs-validation" novalidate onsubmit="return checkSubmit();">
				<div class="col-md-6">
					<label for="apellidos" class="form-label">Apellido/s (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control UpperCase" id="apellidos" name="apellidos" required />
					<div class="invalid-feedback">Por favor ingrese su Apellido.</div>
				</div>
				<div class="col-md-6">
					<label for="nombres" class="form-label">Nombre/s (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control UpperCase" id="nombres" name="nombres" required />
					<div class="invalid-feedback">Por favor ingrese su Nombre.</div>
				</div>
				<div class="col-md-4">
					<label for="nacionalidad" class="form-label">Nacionalidad (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control UpperCase" id="nacionalidad" name="nacionalidad" required />
				</div>
				<div class="col-md-4">
					<label for="dni" class="form-label">Nº DNI (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control" id="dni" name="dni" required />
				</div>
				<div class="col-md-4">
					<label for="telefono" class="form-label">Tel. Contacto (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control" id="telefono" name="telefono" required />
				</div>
				<div class="col-md-4">
					<label for="sexo" class="form-label">Sexo (<span class="text-danger">*</span>)</label>
					<select id="sexo" name="sexo" class="form-control" required>
						<option selected disabled value="">Seleccione Sexo...</option>
						<option value="FEMENINO">FEMENINO</option>
						<option value="MASCULINO">MASCULINO</option>
						<option value="OTROS">OTROS</option>
					</select>
				</div>
				<div class="col-md-4">
					<label for="estadocivil" class="form-label">Estado Civil (<span class="text-danger">*</span>)</label>
					<select id="estadocivil" name="estadocivil" class="form-control">
						<option value="CASADA/O" selected>CASADA/O</option>
						<option value="SOLTERA/O">SOLTERA/O</option>
						<option value="DIVORCIADA/O">DIVORCIADA/O</option>
						<option value="VIUDA/O">VIUDA/O</option>
					</select>
				</div>
				<div class="col-md-4">
					<label for="fechanacimiento" class="form-label">Fecha de Nacimiento (<span class="text-danger">*</span>)</label>
					<input type="date" class="form-control" id="fechanacimiento" name="fechanacimiento" required />
				</div>
				<div class="col-md-6">
					<label for="domicilio" class="form-label">Domicilio (Calle y Nº) (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control UpperCase" id="domicilio" name="domicilio" required />
				</div>
				<div class="col-md-3">
					<label for="localidad" class="form-label">Localidad (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control UpperCase" id="localidad" name="localidad" required />
				</div>
				<div class="col-md-3">
					<label for="codigopostal" class="form-label">Código Postal (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control" id="codigopostal" name="codigopostal" required />
				</div>
				<div class="col-md-5">
					<label for="provincia" class="form-label">Provincia (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control UpperCase" id="provincia" name="provincia" required />
				</div>
				<div class="col-md-7">
					<label for="mail" class="form-label">Email (<span class="text-danger">*</span>)</label>
					<input type="email" class="form-control" id="mail" name="mail" required />
				</div>
				<div class="col-md-6">
					<label for="estudios" class="form-label">Estudios </label>
					<select id="estudios" name="estudios" class="form-control">
						<option value="UNIVERSITARIO" selected>UNIVERSITARIO</option>
						<option value="POSGRADO">POSGRADO</option>
					</select>
				</div>
				<div class="col-md-6">
					<label for="carrera" class="form-label">Título/Carrera</label>
					<input type="text" class="form-control UpperCase" id="carrera" name="carrera" />
				</div>
				<div class="col-12 d-flex justify-content-center m-3">
                            <div class="paragraph h4"><strong>Datos Laborales </strong></div>
                </div>
				<!-- <div class="paragraph"><strong>Datos Laborales: </strong></div> -->
				<div class="col-md-6">
					<label for="legajo" class="form-label">Nº de Legajo (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control" id="legajo" name="legajo" required />
				</div>
				<div class="col-md-6">
					<label for="organismo" class="form-label">Organismo que liquida su haber (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control UpperCase" id="organismo" name="organismo" required />
				</div>
				<div class="col-md-6">
					<label for="cuil" class="form-label">Nº de CUIL (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control" id="cuil" name="cuil" required />
				</div>
				<div class="col-md-6">
					<label for="trabajo" class="form-label">Organismo donde trabaja (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control UpperCase" id="trabajo" name="trabajo" required />
				</div>
				<div class="col-md-6">
					<label for="domiciliotrabajo" class="form-label">Domicilio del lugar de trabajo: (Calle y Nº)(<span class="text-danger">*</span>)
					</label>
					<input type="text" class="form-control UpperCase" id="domiciliotrabajo" name="domiciliotrabajo" required />
				</div>
				<div class="col-md-6">
					<label for="locatrabajo" class="form-label">Localidad (<span class="text-danger">*</span>)</label>
					<input type="text" class="form-control UpperCase" id="locatrabajo" name="locatrabajo" required />
				</div>
				<div class="container">
					<div class="paragraph" style="text-align: justify">
						Dejo expresa constancia que de solicitar en el futuro mi desafiliación, lo
						hare mediante nota individual presentada personalmente ante la entidad
						gremial, o bien, por telegrama o carta documento individual (no
						colectiva); ello en un todo de acuerdo a lo reglamentado por la Ley
						Nº23.551, su Decreto Reglamentario y su Estatuto Social a fin de
						garantizar la expresa voluntad del afiliado. Declaro conocer y aceptar las
						normas vigentes para el uso de los servicios al momento de requerir los
						mismos. &nbsp;<br />​<br />
					</div>
				</div>
				<div class="col-12">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" id="acepto" name="acepto" required />
						<label class="form-check-label" for="acepto">
							DECLARO Y ACEPTO (<span class="text-danger">*</span>)
						</label>
					</div>
				</div>
				<div class="col-12">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" id="aceptopago" name="aceptopago" required />
						<label class="form-check-label" for="aceptopago">
							ACEPTO PAGO DEBITO AUTOMATICO (<span class="text-danger">*</span>)
						</label>
					</div>
				</div>
				<div class="col-md-12">
					<!-- <label for="ctactesuc" class="form-label">Caja de Ahorro </label> -->

					<div class="row">
						<!-- <div class="col-md-4">
                        <select id="tipocta" name="tipocta" class="form-select" required>
                            <option selectec value="CA">Caja de ahorro</option>
                            <option value="CC">Cuenta Corriente</option>
                        </select>
                        <small id="emailHelp" class="form-text text-muted">Tipo de Cuenta. (<span class="text-danger">*</span>)</small>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="ctactesuc" name="ctactesuc" placeholder="000" required />
                        <small id="emailHelp" class="form-text text-muted">Sucursal. (<span class="text-danger">*</span>)</small>
                    </div> -->
						<div class="col-md-5">
							<label for="ctacte" class="form-label">Caja de Ahorro </label>
							<!-- <input type="number" class="form-control" id="ctacte" name="ctacte" placeholder="00000000000000" required min="00000000000001" max="99999999999999" maxlength="14" oninput="if(this.value.length = this.maxLength ) this.value = this.value.slice(0, this.maxLength);"/> -->
							<!-- minlength="14" maxlength="14" -->
							<input type="text" class="form-control" id="ctacte" name="ctacte" placeholder="00000000000000" required minlength="14" maxlength="14" pattern="[0-9]+" />
							<small id="emailHelp" class="form-text text-muted">Número de Cuenta (<span class="text-danger">*</span>)</small>
							<div class="invalid-feedback">
								Por favor ingrese los 14 dígitos numéricos de su Caja de Ahorro.
							</div>
						</div>
					</div>
				</div>

				<div class="col-12 d-flex justify-content-center mt-5">
					<button type="submit" class="btn btn-primary mr-3">Confirmar Alta de Afiliación</button>
					<a class="btn btn-success" href="panel-afiliados.php">Volver al Panel de Afiliados</a>
				</div>
			</form>
		</div>
		<br />
		<br />
		<br />

		<!-- Bootstrap core JS-->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
		<!-- Core theme JS-->
		<script src="js/scripts.js"></script>
		<!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
		<!-- * *                               SB Forms JS                               * *-->
		<!-- * * Activate your form at https://startbootstrap.com/solution/contact-forms * *-->
		<!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
		<script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
		<script>
			// Example starter JavaScript for disabling form submissions if there are invalid fields


			(function() {
				"use strict";

				// Fetch all the forms we want to apply custom Bootstrap validation styles to
				var forms = document.querySelectorAll(".needs-validation");

				// Loop over them and prevent submission
				Array.prototype.slice.call(forms).forEach(function(form) {
					form.addEventListener(
						"submit",
						function(event) {
							if (!form.checkValidity()) {
								event.preventDefault();
								event.stopPropagation();
								enviando = false;
							}

							form.classList.add("was-validated");
						},
						false
					);
				});
			})();
			enviando = false; //Obligaremos a entrar el if en el primer submit
			function checkSubmit() {
				if (!enviando) {
					enviando = true;
					return true;
				} else {
					//Si llega hasta aca significa que pulsaron 2 veces el boton submit
					//alert("El formulario ya se esta enviando");
					return false;
				}
			};
		</script>
		<!-- Para Arreglar ruben-->
		<script src="vendor/jquery/jquery.min.js"></script>
		<script>
			$(document).ready(function() {
				$(".UpperCase").on("keypress", function() {
					$input = $(this);
					setTimeout(function() {
						$input.val($input.val().toUpperCase());
					}, 50);
				});

				enviando = false; //Obligaremos a entrar el if en el primer submit


			});
		</script>
		<!-- <script src="js/frmafiliacion.js"></script> -->
		<!-- Para Arreglar ruben-->
	</body>

</div>
<?php require_once("include/parte_inferior.php"); ?>