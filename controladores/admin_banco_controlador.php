<?php
// 1. Blindaje de seguridad
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión expirada."]);
    exit;
}

require_once '../modelos/BancoModelo.php';
$modelo = new BancoModelo();

// ==========================================
// PETICIONES GET: Obtener datos para la vista
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $banco = $modelo->obtenerConfiguracionBanco();
        echo json_encode(["status" => "success", "data" => $banco]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Error al obtener configuración: " . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// PETICIONES POST: Actualizar o Generar TXT
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['accion'])) {
        
        // ACCIÓN A: Actualizar Importe
        if ($input['accion'] === 'actualizar_importe') {
            try {
                $nuevo_importe = floatval($input['importe']);
                if ($nuevo_importe <= 0) {
                    echo json_encode(["status" => "error", "message" => "El importe debe ser mayor a 0."]);
                    exit;
                }
                
                $modelo->actualizarImporte($nuevo_importe);
                echo json_encode(["status" => "success", "message" => "Importe actualizado correctamente."]);
            } catch (Exception $e) {
                echo json_encode(["status" => "error", "message" => "Error al actualizar: " . $e->getMessage()]);
            }
            exit;
        }

        // ACCIÓN B: Generar Archivo TXT
        if ($input['accion'] === 'generar_archivo') {
            try {
                $banco = $modelo->obtenerConfiguracionBanco();
                $afiliados = $modelo->obtenerAfiliadosDebito();

                // Formateo de las variables base ingresadas por el usuario
                $secuencia = $input['secuencia']; // Ej: "0701"
                $fecha_tope = str_replace('-', '', $input['fecha_tope']); // De 2026-07-15 a 20260715
                $saltolinea = "\r\n"; // Formato estándar de saltos de línea para Windows/Bancos
                
                // Formateo del importe sacando los puntos y rellenando con 0 (Ej: 15293.00 -> 00000001529300)
                $importe_base = floatval($banco['bco_importe_debito']);
                $importe_formateado = str_pad(number_format($importe_base, 2, '', ''), 15, "0", STR_PAD_LEFT);
                
                $contenidoTxt = "";
                $erroresValidacion = []; // Guardaremos los afiliados con cuentas incorrectas para avisarle al usuario
                
                // --- REGISTRO 1 (Cabecera) ---
                $espacios_r1 = str_repeat(" ", 94); // Reemplaza tu viejo bucle FOR
                $linea1 = "1" . $banco['bco_sucursal'] . $banco['bco_tipo_moneda'] . $banco['bco_ctacte'] . $banco['bco_moneda'] . "E" . $secuencia . $fecha_tope . "REE" . $espacios_r1;
                $contenidoTxt .= $linea1 . $saltolinea;

                // --- REGISTRO 2 (Cuerpo / Afiliados) ---
                $contadorAfiliados = 0;
                $espacios_r2 = str_repeat(" ", 86);
                
                foreach ($afiliados as $afi) {
                    $cuenta = trim($afi['numero_cuenta']);
                    
                    // Validamos que la cuenta tenga exactamente 14 caracteres como exigía tu código legacy
                    if (strlen($cuenta) == 14) {
                        $auxCtaCte = substr($cuenta, 0, 4);
                        $sucCuentaCte = ($auxCtaCte == "0002") ? "0085" : $auxCtaCte;
                        $nroCuentaCte = "0" . substr($cuenta, 4); 
                        
                        $linea2 = "2" . $sucCuentaCte . "CA" . $nroCuentaCte . $importe_formateado . "00000000" . "0" . $espacios_r2;
                        $contenidoTxt .= $linea2 . $saltolinea;
                        $contadorAfiliados++;
                    } else {
                        // Si la cuenta está mal, lo agendamos para avisar pero NO lo metemos al TXT (evita rechazos del banco)
                        $erroresValidacion[] = "DNI: {$afi['dni']} - {$afi['apellidos']}, {$afi['nombres']}";
                    }
                }

                // --- REGISTRO 3 (Control / Pie) ---
                $total_debitar = $importe_base * $contadorAfiliados;
                $total_debitar_formateado = str_pad(number_format($total_debitar, 2, '', ''), 15, "0", STR_PAD_LEFT);
                $cantidad_str = str_pad($contadorAfiliados, 6, "0", STR_PAD_LEFT);
                $espacios_r3 = str_repeat(" ", 85);
                
                $linea3 = "3" . $total_debitar_formateado . $cantidad_str . str_repeat("0", 15) . str_repeat("0", 6) . $espacios_r3;
                $contenidoTxt .= $linea3; // La última línea va sin salto
                
                // Retornamos el contenido procesado a Vue. ¡Cero archivos guardados en el disco!
                echo json_encode([
                    "status" => "success", 
                    "file_content" => base64_encode($contenidoTxt), // Usamos base64 para evitar que JSON rompa caracteres especiales
                    "file_name" => "Debito_BNA_{$fecha_tope}.txt",
                    "errores_cuentas" => $erroresValidacion // Le pasamos a Vue quiénes quedaron afuera
                ]);

            } catch (Exception $e) {
                echo json_encode(["status" => "error", "message" => "Error generando TXT: " . $e->getMessage()]);
            }
            exit;
        }
    }
}
?>