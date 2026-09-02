"use strict";

const API_CHECKIN =
    "/public/api/checkin.php";

const DURACION_EXITO_MS =
    1200;

let scanner = null;
let scannerActivo = false;
let procesando = false;

let ultimoCodigo = "";
let ultimoCodigoEn = 0;

let codigoPendiente = "";

const scannerStatus =
    document.getElementById(
        "scanner-status"
    );

const resultScreen =
    document.getElementById(
        "result-screen"
    );

const resultIcon =
    document.getElementById(
        "result-icon"
    );

const resultTitle =
    document.getElementById(
        "result-title"
    );

const resultName =
    document.getElementById(
        "result-name"
    );

const resultDetail =
    document.getElementById(
        "result-detail"
    );

const resultButton =
    document.getElementById(
        "result-button"
    );

const manualButton =
    document.getElementById(
        "manual-button"
    );

const manualPanel =
    document.getElementById(
        "manual-panel"
    );

const manualClose =
    document.getElementById(
        "manual-close"
    );

const manualForm =
    document.getElementById(
        "manual-form"
    );

const manualCode =
    document.getElementById(
        "manual-code"
    );

document.addEventListener(
    "DOMContentLoaded",
    () => {
        iniciarScanner();
        configurarBusquedaManual();
        configurarResultado();
    }
);

async function iniciarScanner() {
    if (
        typeof Html5Qrcode === "undefined"
    ) {
        mostrarErrorPermanente(
            "No se pudo cargar el lector QR."
        );

        return;
    }

    try {
        scannerStatus.textContent =
            "Solicitando cámara…";

        scanner =
            new Html5Qrcode(
                "reader",
                {
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats
                            .QR_CODE,
                    ],
                }
            );

        await scanner.start(
            {
                facingMode:
                    "environment",
            },
            {
                fps: 15,

                qrbox: (
                    ancho,
                    alto
                ) => {
                    const lado =
                        Math.floor(
                            Math.min(
                                ancho,
                                alto
                            ) * 0.68
                        );

                    return {
                        width: lado,
                        height: lado,
                    };
                },
            },
            onScanSuccess,
            () => {}
        );

        scannerActivo = true;

        scannerStatus.textContent =
            "Apuntá al código QR";

    } catch (error) {
        console.error(error);

        mostrarErrorPermanente(
            "No se pudo acceder a la cámara."
        );
    }
}

async function onScanSuccess(
    decodedText
) {
    const codigo =
        normalizarCodigo(
            decodedText
        );

    if (
        codigo === ""
        || procesando
    ) {
        return;
    }

    const ahora =
        Date.now();

    if (
        codigo === ultimoCodigo
        && (
            ahora
            - ultimoCodigoEn
        ) < 3000
    ) {
        return;
    }

    ultimoCodigo =
        codigo;

    ultimoCodigoEn =
        ahora;

    await validarAcceso(
        codigo
    );
}

async function validarAcceso(
    codigo
) {
    if (procesando) {
        return;
    }

    procesando = true;
    codigoPendiente = codigo;

    scannerStatus.textContent =
        "Validando…";

    await pausarScanner();

    try {
        const response =
            await fetch(
                API_CHECKIN,
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/json",
                    },

                    cache: "no-store",

                    body:
                        JSON.stringify({
                            codigo,
                        }),
                }
            );

        const data =
            await response.json();

        if (!response.ok) {
            throw new Error(
                "El servidor no pudo validar el acceso."
            );
        }

        if (data.ok === true) {
            mostrarResultadoValido(
                data
            );

            window.setTimeout(
                finalizarExito,
                DURACION_EXITO_MS
            );

            return;
        }

        mostrarResultadoNegocio(
            data
        );

    } catch (error) {
        console.error(error);

        mostrarResultadoTecnico();
    }
}

function mostrarResultadoValido(
    data
) {
    limpiarClasesResultado();

    resultScreen.classList.add(
        "is-success",
        "is-visible"
    );

    resultScreen.setAttribute(
        "aria-hidden",
        "false"
    );

    resultIcon.textContent =
        "✓";

    resultTitle.textContent =
        "¡TE DAMOS LA BIENVENIDA!";

    resultName.textContent =
        "";

    resultDetail.textContent =
        "Podés ingresar.";

    resultButton.hidden =
        true;

    vibrar(80);

    reproducirTono(
        "success"
    );
}

function mostrarResultadoNegocio(
    data
) {
    limpiarClasesResultado();

    resultScreen.classList.add(
        "is-error",
        "is-visible"
    );

    resultScreen.setAttribute(
        "aria-hidden",
        "false"
    );

    resultIcon.textContent =
        "×";

    const nombre =
        data.acceso?.nombre
        || "";

    resultName.textContent =
        nombre;

    switch (data.resultado) {

        case "ya_utilizado":

            resultTitle.textContent =
                "ACCESO YA UTILIZADO";

            resultDetail.textContent =
                data.utilizado_en
                    ? (
                        "Ingreso registrado: "
                        + formatearHora(
                            data.utilizado_en
                        )
                    )
                    : "Este acceso ya fue utilizado.";

            break;

        case "anulado":

            resultTitle.textContent =
                "ACCESO ANULADO";

            resultDetail.textContent =
                "Consultá con organización.";

            break;

        case "no_encontrado":

            resultTitle.textContent =
                "ACCESO NO ENCONTRADO";

            resultName.textContent =
                "";

            resultDetail.textContent =
                "Revisá el código o realizá una búsqueda manual.";

            break;

        case "no_habilitado":

        case "orden_no_valida":

            resultTitle.textContent =
                "ACCESO NO HABILITADO";

            resultDetail.textContent =
                "Consultá con organización.";

            break;

        default:

            resultTitle.textContent =
                data.mensaje
                || "ACCESO NO VÁLIDO";

            resultDetail.textContent =
                "Consultá con organización.";
    }

    resultButton.textContent =
        "Continuar";

    resultButton.dataset.action =
        "continue";

    resultButton.hidden =
        false;

    vibrar(
        [
            120,
            70,
            120,
        ]
    );

    reproducirTono(
        "error"
    );
}

function mostrarResultadoTecnico() {
    limpiarClasesResultado();

    resultScreen.classList.add(
        "is-technical",
        "is-visible"
    );

    resultScreen.setAttribute(
        "aria-hidden",
        "false"
    );

    resultIcon.textContent =
        "!";

    resultTitle.textContent =
        "SIN CONEXIÓN";

    resultName.textContent =
        "";

    resultDetail.textContent =
        "No se pudo validar el acceso. Verificá la conexión a Internet.";

    resultButton.textContent =
        "Reintentar";

    resultButton.dataset.action =
        "retry";

    resultButton.hidden =
        false;

    vibrar(
        [
            160,
            100,
            160,
        ]
    );
}

async function finalizarExito() {
    ocultarResultado();

    procesando =
        false;

    codigoPendiente =
        "";

    await reanudarScanner();
}

function configurarResultado() {
    resultButton.addEventListener(
        "click",
        async () => {
            const accion =
                resultButton
                    .dataset
                    .action;

            if (accion === "retry") {
                const codigo =
                    codigoPendiente;

                ocultarResultado();

                procesando =
                    false;

                await validarAcceso(
                    codigo
                );

                return;
            }

            ocultarResultado();

            procesando =
                false;

            codigoPendiente =
                "";

            await reanudarScanner();
        }
    );
}

function limpiarClasesResultado() {
    resultScreen.classList.remove(
        "is-success",
        "is-error",
        "is-technical"
    );
}

function ocultarResultado() {
    resultScreen.classList.remove(
        "is-visible",
        "is-success",
        "is-error",
        "is-technical"
    );

    resultScreen.setAttribute(
        "aria-hidden",
        "true"
    );

    resultName.textContent =
        "";

    resultDetail.textContent =
        "";

    resultButton.hidden =
        true;
}

async function pausarScanner() {
    if (
        scanner === null
        || !scannerActivo
    ) {
        return;
    }

    try {
        scanner.pause(true);
    } catch (error) {
        console.warn(
            "No se pudo pausar scanner:",
            error
        );
    }
}

async function reanudarScanner() {
    scannerStatus.textContent =
        "Apuntá al código QR";

    if (
        scanner === null
        || !scannerActivo
    ) {
        return;
    }

    try {
        scanner.resume();
    } catch (error) {
        console.warn(
            "No se pudo reanudar scanner:",
            error
        );
    }
}

function configurarBusquedaManual() {
    manualButton.addEventListener(
        "click",
        async () => {
            await pausarScanner();

            manualPanel.classList.add(
                "is-visible"
            );

            manualPanel.setAttribute(
                "aria-hidden",
                "false"
            );

            window.setTimeout(
                () => {
                    manualCode.focus();
                },
                150
            );
        }
    );

    manualClose.addEventListener(
        "click",
        cerrarBusquedaManual
    );

    manualPanel.addEventListener(
        "click",
        (event) => {
            if (
                event.target
                === manualPanel
            ) {
                cerrarBusquedaManual();
            }
        }
    );

    manualForm.addEventListener(
        "submit",
        async (event) => {
            event.preventDefault();

            const codigo =
                normalizarCodigo(
                    manualCode.value
                );

            if (codigo === "") {
                manualCode.focus();

                return;
            }

            manualPanel.classList.remove(
                "is-visible"
            );

            manualPanel.setAttribute(
                "aria-hidden",
                "true"
            );

            manualCode.value =
                "";

            await validarAcceso(
                codigo
            );
        }
    );
}

async function cerrarBusquedaManual() {
    manualPanel.classList.remove(
        "is-visible"
    );

    manualPanel.setAttribute(
        "aria-hidden",
        "true"
    );

    manualCode.value =
        "";

    await reanudarScanner();
}

function normalizarCodigo(
    codigo
) {
    return String(
        codigo ?? ""
    )
        .trim()
        .toUpperCase();
}

function formatearHora(
    fechaMysql
) {
    const partes =
        String(fechaMysql)
            .split(" ");

    if (partes.length !== 2) {
        return fechaMysql;
    }

    return partes[1]
        .substring(
            0,
            5
        )
        + " h";
}

function vibrar(
    patron
) {
    if (
        "vibrate"
        in navigator
    ) {
        navigator.vibrate(
            patron
        );
    }
}

function reproducirTono(
    tipo
) {
    try {
        const AudioContextClass =
            window.AudioContext
            || window.webkitAudioContext;

        if (!AudioContextClass) {
            return;
        }

        const context =
            new AudioContextClass();

        const oscillator =
            context.createOscillator();

        const gain =
            context.createGain();

        oscillator.connect(
            gain
        );

        gain.connect(
            context.destination
        );

        oscillator.type =
            "sine";

        oscillator.frequency.value =
            tipo === "success"
                ? 920
                : 210;

        gain.gain.value =
            0.09;

        oscillator.start();

        oscillator.stop(
            context.currentTime
            + (
                tipo === "success"
                    ? 0.08
                    : 0.20
            )
        );

    } catch (error) {
        /*
         * Audio complementario.
         */
    }
}

function mostrarErrorPermanente(
    mensaje
) {
    scannerStatus.textContent =
        mensaje;

    scannerStatus.style.background =
        "#b91c1c";
}