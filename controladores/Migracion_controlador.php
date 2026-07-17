<?php
session_start();

// 1. Validación estricta de seguridad
/* if (!isset($_SESSION['admin_logueado'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'msg' => 'Acceso denegado.']);
    exit;
} */

require_once '../modelos/Migracion_modelo.php';
$modelo = new Migracion_modelo();

// 2. Recepción de datos JSON desde Vue (Axios/Fetch)
$input = json_decode(file_get_contents("php://input"), true);
$offset = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit = 50; // Procesamos de a 50 registros como acordamos

// 3. Obtenemos el lote de la tabla plana vieja
$afiliadosViejos = $modelo->obtenerLoteAfiliadosViejos($offset, $limit);

// Si ya no hay registros, avisamos al Frontend que terminamos
if (count($afiliadosViejos) === 0) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'terminado' => true, 'msg' => 'Migración finalizada con éxito.']);
    exit;
}

$exitos = 0;
$errores = [];

foreach ($afiliadosViejos as $viejo) {
    try {
        // --- LIMPIEZA DE DATOS (REGLAS DE NEGOCIO) ---

        // A. Fechas inválidas
        $fechaNacimiento = ($viejo['afi_nacimiento'] === '0000-00-00' || empty($viejo['afi_nacimiento'])) 
                            ? '1970-01-01' 
                            : $viejo['afi_nacimiento'];

        // B. Limpieza de DNI y CUIL (Solo números, eliminar guiones/puntos)
        $dniLimpio = preg_replace('/[^0-9]/', '', $viejo['afi_dni'] ?? '');
        $dniLimpio = empty($dniLimpio) ? null : $dniLimpio; // Vacíos a NULL

        $cuilLimpio = preg_replace('/[^0-9]/', '', $viejo['afi_cuil'] ?? '');
        $cuilLimpio = empty($cuilLimpio) ? null : $cuilLimpio; // Vacíos a NULL

        // C. Mapeo de Estados
        // Viejo 2 (Afiliado) -> Nuevo 1
        // Viejo 1 o 3 (Solicitud/Desafiliado) -> Nuevo 2
        $estadoNuevo = ($viejo['afi_estado'] == 2) ? 1 : 2;

        // D. Otros campos (Vacio a NULL, los textos se mantienen)
        $legajo = empty($viejo['afi_legajo']) ? null : $viejo['afi_legajo'];
        $ctaCte = empty($viejo['afi_ctacte']) ? null : $viejo['afi_ctacte'];

        // Empaquetamos los datos limpios para el modelo
        $datosLimpios = [
            'id_afiliado' => $viejo['id'],
            'estado'      => $estadoNuevo,
            'dni'         => $dniLimpio,
            'cuil'        => $cuilLimpio,
            'nacimiento'  => $fechaNacimiento,
            'legajo'      => $legajo,
            'ctacte'      => $ctaCte,
            // (Aquí se mapean el resto de los campos crudos del registro $viejo)
        ];

        // 4. Ejecutamos la migración transaccional de este registro
        $modelo->insertarAfiliadoNormalizado($datosLimpios, $viejo);
        $exitos++;

    } catch (Exception $e) {
        // Guardamos el ID que falló para mostrarlo en la tabla de Vue
        $errores[] = [
            'id_afiliado' => $viejo['id'],
            'error'       => $e->getMessage()
        ];
    }
}

// 5. Respuesta JSON pura para el Frontend
header('Content-Type: application/json');
echo json_encode([
    'status'    => 'success',
    'terminado' => false,
    'procesados'=> $exitos,
    'errores'   => $errores,
    'nextOffset'=> $offset + $limit // Calculamos el próximo inicio
]);