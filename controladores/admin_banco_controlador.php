<?php
// 1. Blindaje de seguridad
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Acceso denegado. Sesión expirada."]);
    exit;
}

require_once __DIR__ . '/../modelos/BancoModelo.php';

try {
    $modelo = new BancoModelo();

    // ==========================================
    // PETICIONES GET: Obtener datos para la vista
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $banco = $modelo->obtenerConfiguracionBanco();
        echo json_encode(["status" => "success", "data" => $banco], JSON_UNESCAPED_UNICODE);
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
                $nuevo_importe = floatval($input['importe']);
                if ($nuevo_importe <= 0) {
                    echo json_encode(["status" => "error", "message" => "El importe debe ser mayor a 0."]);
                    exit;
                }
                
                $modelo->actualizarImporte($nuevo_importe);
                echo json_encode(["status" => "success", "message" => "Importe actualizado correctamente."]);
                exit;
            }

            // ACCIÓN B: Generar Archivo TXT (Norma BNA 128 Caracteres)
            if ($input['accion'] === 'generar_archivo') {
                $banco = $modelo->obtenerConfiguracionBanco();
                $afiliados = $modelo->obtenerAfiliadosDebito();

                // Formateo de variables base
                $secuencia  = str_pad(substr($input['secuencia'] ?? '', 0, 4), 4, "0", STR_PAD_LEFT); // N4
                $fecha_tope = str_replace('-', '', $input['fecha_tope'] ?? ''); // N8 (AAAAMMDD)
                $saltolinea = "\r\n"; // Standard Windows / Mainframe BNA

                // Conversión del importe base a CENTAVOS ENTEROS (Elimina problemas de float)
                $importe_base_flotante = floatval($banco['bco_importe_debito'] ?? 0);
                $importe_base_centavos = (int) round($importe_base_flotante * 100);
                
                // N(15) 13,2 -> 15 dígitos rellenados con ceros a la izquierda
                $importe_formateado = str_pad((string)$importe_base_centavos, 15, "0", STR_PAD_LEFT);

                $contenidoTxt = "";
                $erroresValidacion = [];

                // ----------------------------------------------------
                // REGISTRO 1: CABECERA (Anexo I BNA - Exacto 128 chars)
                // ----------------------------------------------------
                $r1_tipo     = "1";                                                                         // N1
                $r1_casa     = str_pad(substr($banco['bco_sucursal'] ?? '', 0, 4), 4, "0", STR_PAD_LEFT);   // N4
                $r1_prod     = str_pad(substr($banco['bco_tipo_moneda'] ?? '', 0, 2), 2, "0", STR_PAD_LEFT); // N2 (10=Cta Cte $)
                $r1_cuenta   = str_pad(substr($banco['bco_ctacte'] ?? '', 0, 10), 10, "0", STR_PAD_LEFT);   // N10
                $r1_moneda   = str_pad(substr($banco['bco_moneda'] ?? 'P', 0, 1), 1, "P", STR_PAD_RIGHT);   // A1 (P=Pesos)
                $r1_id       = "E";                                                                         // A1 ("E"=Empresa)
                $r1_sec      = $secuencia;                                                                  // N4 (MMNN)
                $r1_f_tope   = str_pad(substr($fecha_tope, 0, 8), 8, "0", STR_PAD_LEFT);                   // N8 (AAAAMMDD)
                $r1_bna      = "REE";                                                                       // A3 (Clientes comunes)
                $r1_filler   = str_repeat(" ", 94);                                                         // A94 (Blancos)

                $linea1 = $r1_tipo . $r1_casa . $r1_prod . $r1_cuenta . $r1_moneda . $r1_id . $r1_sec . $r1_f_tope . $r1_bna . $r1_filler;

                // Auditoría de longitud Registro 1
                if (strlen($linea1) !== 128) {
                    throw new Exception("Error de estructura en Cabecera (Registro 1): posee " . strlen($linea1) . " caracteres, se requieren 128.");
                }
                $contenidoTxt .= $linea1 . $saltolinea;

                // ----------------------------------------------------
                // REGISTRO 2: DETALLE (Anexo I BNA - Exacto 128 chars)
                // ----------------------------------------------------
                $contadorAfiliados = 0;
                $total_debitar_centavos = 0;

                foreach ($afiliados as $afi) {
                    $cuenta = trim($afi['numero_cuenta'] ?? '');
                    
                    // Validación estricta de cuenta BNA de 14 dígitos
                    if (strlen($cuenta) === 14) {
                        $auxCtaCte    = substr($cuenta, 0, 4);
                        $sucCuentaCte = ($auxCtaCte === "0002") ? "0085" : $auxCtaCte;
                        $nroCuentaCte = "0" . substr($cuenta, 4); // "0" + 10 dígitos = N11

                        $r2_tipo       = "2";                                                     // N1
                        $r2_suc        = str_pad(substr($sucCuentaCte, 0, 4), 4, "0", STR_PAD_LEFT); // N4
                        $r2_sist       = "CA";                                                    // A2
                        $r2_cta        = str_pad(substr($nroCuentaCte, 0, 11), 11, "0", STR_PAD_LEFT); // N11
                        $r2_importe    = $importe_formateado;                                     // N(15) 13,2
                        $r2_f_vto      = "00000000";                                              // N8 (Ceros para empresa "E")
                        $r2_estado     = "0";                                                     // N1 (Cero para empresa "E")
                        $r2_desc_rech  = str_repeat(" ", 30);                                     // A30 (Blancos para "E")
                        $r2_concepto   = str_pad("CUOTA SOC", 10, " ", STR_PAD_RIGHT);             // A10 (Concepto débito)
                        $r2_filler     = str_repeat(" ", 46);                                     // A46 (Blancos)

                        $linea2 = $r2_tipo . $r2_suc . $r2_sist . $r2_cta . $r2_importe . $r2_f_vto . $r2_estado . $r2_desc_rech . $r2_concepto . $r2_filler;

                        if (strlen($linea2) !== 128) {
                            throw new Exception("Error de estructura en Registro 2 (Afiliado DNI {$afi['dni']}): posee " . strlen($linea2) . " caracteres, se requieren 128.");
                        }

                        $contenidoTxt .= $linea2 . $saltolinea;
                        $contadorAfiliados++;
                        $total_debitar_centavos += $importe_base_centavos;
                    } else {
                        $erroresValidacion[] = "DNI: {$afi['dni']} - {$afi['apellidos']}, {$afi['nombres']} (Cuenta inválida: '{$cuenta}')";
                    }
                }

                // ----------------------------------------------------
                // REGISTRO 3: FIN DE LOTE / CIERRE (Exacto 128 chars)
                // ----------------------------------------------------
                $r3_tipo        = "3";                                                                            // N1
                $r3_tot_deb     = str_pad((string)$total_debitar_centavos, 15, "0", STR_PAD_LEFT);                // N(15) 13,2 (Total acumulado en centavos)
                $r3_cant_reg    = str_pad((string)$contadorAfiliados, 6, "0", STR_PAD_LEFT);                      // N6
                $r3_tot_no_ap   = str_repeat("0", 15);                                                           // N(15) 13,2 ("0" para Empresa "E")
                $r3_cant_no_ap  = str_repeat("0", 6);                                                            // N6 ("0" para Empresa "E")
                $r3_filler      = str_repeat(" ", 85);                                                            // A85 (Blancos)

                $linea3 = $r3_tipo . $r3_tot_deb . $r3_cant_reg . $r3_tot_no_ap . $r3_cant_no_ap . $r3_filler;

                // Auditoría de longitud Registro 3
                if (strlen($linea3) !== 128) {
                    throw new Exception("Error de estructura en Registro 3 (Fin de Lote): posee " . strlen($linea3) . " caracteres, se requieren 128.");
                }

                $contenidoTxt .= $linea3 . $saltolinea;

                // Respuesta JSON exitosa para Vue.js
                echo json_encode([
                    "status"          => "success", 
                    "file_content"    => base64_encode($contenidoTxt),
                    "file_name"       => "Debito_BNA_{$fecha_tope}.txt",
                    "errores_cuentas" => $erroresValidacion
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }
} catch (Throwable $e) {
    echo json_encode([
        "status"  => "error", 
        "message" => "Error procesando el archivo: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}