<?php
require_once '../config/conexion.php'; // Ajustá el path si global.php está en otra ruta

class Migracion_modelo
{

    public function obtenerLoteAfiliadosViejos($offset, $limit)
    {
        // Usamos la conexión estática que deberías tener en tu config/conexion.php
        $conexion = Conexion::conectar();

        $sql = "SELECT * FROM afiliados ORDER BY id ASC LIMIT :offset, :limit";
        $stmt = $conexion->prepare($sql);

        // PDO requiere que los límites se pasen como enteros estrictos
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertarAfiliadoNormalizado($datosLimpios, $datosCrudos)
    {
        $conexion = Conexion::conectar();

        try {
            // Iniciamos la transacción: Todo o Nada
            $conexion->beginTransaction();

            $sqlMaestra = "INSERT INTO afiliados_maestra 
                          (id_afiliado, estado, dni, cuil, fecha_nacimiento, numero_cuenta) 
                          VALUES (:id, :estado, :dni, :cuil, :nacimiento, :ctacte)";

            $stmtMaestra = $conexion->prepare($sqlMaestra);
            $stmtMaestra->execute([
                ':id'         => $datosLimpios['id_afiliado'],
                ':estado'     => $datosLimpios['estado'],
                ':dni'        => $datosLimpios['dni'],
                ':cuil'       => $datosLimpios['cuil'],
                ':nacimiento' => $datosLimpios['nacimiento'],
                ':ctacte'     => $datosLimpios['ctacte']
            ]);

            // 2. Insertar en afiliados_laborales
            $sqlLaboral = "INSERT INTO afiliados_laborales 
                          (id_afiliado, legajo) 
                          VALUES (:id, :legajo)";
            $stmtLaboral = $conexion->prepare($sqlLaboral);
            $stmtLaboral->execute([
                ':id'     => $datosLimpios['id_afiliado'],
                ':legajo' => $datosLimpios['legajo']
            ]);

            // NOTA: Siguiendo mi Regla de Oro (No asumir nombres), 
            // dejé estructurados los inserts principales. 
            // Debes replicar este bloque prepare/execute para:
            // - afiliados_domicilios
            // - afiliados_educacion
            // - afiliados_bajas
            // usando los campos exactos de tu nueva BD.

            // Si todo salió bien, confirmamos los cambios
            $conexion->commit();
        } catch (Exception $e) {
            // Si algo falla, deshacemos todos los inserts de este afiliado
            $conexion->rollBack();
            throw $e; // Relanzamos el error para que el controlador lo capture y lo envíe a Vue
        }
    }
}
