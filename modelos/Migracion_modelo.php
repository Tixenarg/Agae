<?php
require_once __DIR__ . '/../config/conexion.php';

class Migracion_modelo {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    /**
     * Ejecuta el proceso de migración, saneamiento y normalización de datos.
     * 
     * @param string $tabla_sabana Nombre exacto de la tabla de origen (default: 'afiliados')
     * @return array Resultado con contador de procesados y errores por fila
     */
    public function ejecutarMigracion($tabla_sabana = 'afiliados') {
        $procesados = 0;
        $errores = [];

        try {
            // Consultamos la tabla origen
            $stmt = $this->db->prepare("SELECT * FROM {$tabla_sabana}");
            $stmt->execute();
            $viejos_afiliados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($viejos_afiliados as $viejo) {
                try {
                    // Normalización de claves a minúsculas para prevenir fallos PDO
                    $viejo = array_change_key_case($viejo, CASE_LOWER);

                    // --- HELPERS DE LIMPIEZA PROFUNDA ---

                    // Limpia texto, realiza trim y convierte a MINÚSCULAS en UTF-8
                    $getValTexto = function($clave) use ($viejo) {
                        if (!isset($viejo[$clave])) return null;
                        $str = trim((string)$viejo[$clave]);
                        return ($str !== '') ? mb_strtolower($str, 'UTF-8') : null;
                    };

                    // Extrae SOLO dígitos (elimina espacios, guiones, +, etc.)
                    $getValNum = function($clave) use ($viejo) {
                        if (!isset($viejo[$clave])) return null;
                        $num = preg_replace('/[^0-9]/', '', (string)$viejo[$clave]);
                        return ($num !== '') ? $num : null;
                    };

                    // --- MAPEOS Y SANEAMIENTO DE DATOS ---

                    // Nombres y Apellidos normalizados a minúsculas
                    $apellido_limpio = $getValTexto('afi_apellidos') ?? $getValTexto('apellidos') ?? 'sin apellido';
                    $nombre_limpio   = $getValTexto('afi_nombres')   ?? $getValTexto('nombres')   ?? 's/d';

                    // Números limpios de caracteres basura
                    $telefono_limpio = $getValNum('afi_telefono') ?? $getValNum('telefono');
                    $dni_limpio      = $getValNum('afi_dni')      ?? $getValNum('dni');
                    $cuil_limpio     = $getValNum('afi_cuil')     ?? $getValNum('cuil');

                    // Validar presencia de DNI
                    if (empty($dni_limpio)) {
                        throw new Exception("Registro omitido: No posee un número de DNI válido.");
                    }

                    // Mapeo numérico directo de Estado (1: Solicitud, 2: Afiliado, 3: Desafiliado)
                    $val_estado = $viejo['afi_estado'] ?? $viejo['estado'] ?? 1;
                    $id_estado  = (is_numeric($val_estado) && in_array((int)$val_estado, [1, 2, 3], true)) ? (int)$val_estado : 1;

                    // Mapeo numérico directo de Forma de Pago (1: Débito/Nación, 2: Mercado Pago, 3: Otros)
                    $val_fpago = $viejo['afi_forma_pago'] ?? $viejo['forma_pago'] ?? 3;
                    $id_fpago  = (is_numeric($val_fpago) && in_array((int)$val_fpago, [1, 2, 3], true)) ? (int)$val_fpago : 3;

                    // --- INSERCIONES EN CADENA (Uso estricto de $this->db) ---

                    // 1. Tabla Maestra
                    $stmt_m = $this->db->prepare("INSERT INTO afiliados_maestra 
                        (apellidos, nombres, dni, telefono, email, id_estado, fecha_solicitud) 
                        VALUES (:apellidos, :nombres, :dni, :telefono, :email, :id_estado, :fecha_solicitud)");
                    
                    $stmt_m->execute([
                        ':apellidos'       => $apellido_limpio,
                        ':nombres'         => $nombre_limpio,
                        ':dni'             => $dni_limpio,
                        ':telefono'        => $telefono_limpio,
                        ':email'           => $getValTexto('afi_email') ?? $getValTexto('email'),
                        ':id_estado'       => $id_estado,
                        ':fecha_solicitud' => $viejo['afi_fechasolicitud'] ?? $viejo['fecha_solicitud'] ?? date('Y-m-d')
                    ]);
                    
                    $id_nuevo = $this->db->lastInsertId();

                    // 2. Datos Personales
                    $stmt_p = $this->db->prepare("INSERT INTO afiliados_datos_personales 
                        (id_afiliado, cuil, nacionalidad, sexo, estado_civil, fecha_nacimiento) 
                        VALUES (:id_afiliado, :cuil, :nacionalidad, :sexo, :estado_civil, :fecha_nacimiento)");
                    
                    $stmt_p->execute([
                        ':id_afiliado'      => $id_nuevo,
                        ':cuil'             => $cuil_limpio,
                        ':nacionalidad'     => $getValTexto('afi_nacionalidad') ?? $getValTexto('nacionalidad'),
                        ':sexo'             => $getValTexto('afi_sexo')         ?? $getValTexto('sexo'),
                        ':estado_civil'     => $getValTexto('afi_civil')        ?? $getValTexto('estado_civil'),
                        ':fecha_nacimiento' => $viejo['afi_nacimiento']        ?? $viejo['fecha_nacimiento'] ?? null
                    ]);

                    // 3. Domicilios
                    $stmt_d = $this->db->prepare("INSERT INTO afiliados_domicilios 
                        (id_afiliado, direccion, localidad, provincia, codigo_postal, es_principal) 
                        VALUES (:id_afiliado, :direccion, :localidad, :provincia, :codigo_postal, 1)");
                    
                    $stmt_d->execute([
                        ':id_afiliado'   => $id_nuevo,
                        ':direccion'     => $getValTexto('afi_domicilio')    ?? $getValTexto('direccion'),
                        ':localidad'     => $getValTexto('afi_localidad')    ?? $getValTexto('localidad'),
                        ':provincia'     => $getValTexto('afi_provincia')    ?? $getValTexto('provincia'),
                        ':codigo_postal' => $getValTexto('afi_codigopostal') ?? $getValTexto('codigo_postal')
                    ]);

                    // 4. Datos de Cobro
                    $raw_acepto  = $viejo['afi_aceptopago'] ?? $viejo['acepto_pago'] ?? '';
                    $acepto_pago = in_array(strtoupper((string)$raw_acepto), ['SI', '1', 'TRUE']) ? 1 : 0;
                    
                    $stmt_c = $this->db->prepare("INSERT INTO afiliados_datos_cobro 
                        (id_afiliado, id_fpago, numero_cuenta, acepto_pago) 
                        VALUES (:id_afiliado, :id_fpago, :numero_cuenta, :acepto_pago)");
                    
                    $stmt_c->execute([
                        ':id_afiliado'   => $id_nuevo,
                        ':id_fpago'      => $id_fpago,
                        ':numero_cuenta' => $getValTexto('afi_ctacte') ?? $getValTexto('numero_cuenta'),
                        ':acepto_pago'   => $acepto_pago
                    ]);

                    // 5. Educación
                    $stmt_e = $this->db->prepare("INSERT INTO afiliados_educacion 
                        (id_afiliado, nivel_estudio, titulo) 
                        VALUES (:id_afiliado, :nivel_estudio, :titulo)");
                    
                    $stmt_e->execute([
                        ':id_afiliado'   => $id_nuevo,
                        ':nivel_estudio' => $getValTexto('afi_estudio') ?? $getValTexto('nivel_estudio'),
                        ':titulo'        => $getValTexto('afi_titulo')  ?? $getValTexto('titulo')
                    ]);

                    // 6. Datos Laborales
                    $stmt_l = $this->db->prepare("INSERT INTO afiliados_laborales 
                        (id_afiliado, legajo, org_liquida_haber, org_trabaja, domicilio_trabajo, localidad_trabajo) 
                        VALUES (:id_afiliado, :legajo, :org_liquida_haber, :org_trabaja, :domicilio_trabajo, :localidad_trabajo)");
                    
                    $stmt_l->execute([
                        ':id_afiliado'       => $id_nuevo,
                        ':legajo'            => $getValTexto('afi_legajo')           ?? $getValTexto('legajo'),
                        ':org_liquida_haber' => $getValTexto('afi_orgliquidahaber') ?? $getValTexto('org_liquida_haber'),
                        ':org_trabaja'       => $getValTexto('afi_orgtrabaja')       ?? $getValTexto('org_trabaja'),
                        ':domicilio_trabajo' => $getValTexto('afi_domitrabajo')     ?? $getValTexto('domicilio_trabajo'),
                        ':localidad_trabajo' => $getValTexto('afi_locatrabajo')     ?? $getValTexto('localidad_trabajo')
                    ]);

                    $procesados++;

                } catch (Exception $eFila) {
                    $errores[] = [
                        "id_afiliado" => $viejo['id'] ?? $viejo['afi_dni'] ?? 'S/N',
                        "error"       => $eFila->getMessage()
                    ];
                }
            }

            return [
                "exito"      => true,
                "procesados" => $procesados,
                "errores"    => $errores
            ];

        } catch (PDOException $e) {
            return [
                "exito" => false,
                "error" => "Error crítico en la base de datos: " . $e->getMessage()
            ];
        }
    }
}