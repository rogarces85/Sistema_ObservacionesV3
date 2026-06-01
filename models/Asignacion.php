<?php
/**
 * Clase Asignacion
 * Manejo de asignaciones de establecimientos a registradores por año y meses
 * Soporta asignaciones anuales y temporales con fusión de meses
 */

require_once __DIR__ . '/Database.php';

class Asignacion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Normalizar meses a formato canónico
     * @param mixed $meses 'ALL', string vacío, o lista de meses
     * @return string 'ALL' o lista ordenada ascendente '1,2,3'
     */
    public static function normalizarMeses($meses)
    {
        if (empty($meses) || strtoupper(trim($meses)) === 'ALL') {
            return 'ALL';
        }
        $mesesArray = array_map('intval', explode(',', $meses));
        $mesesArray = array_filter($mesesArray, fn($m) => $m >= 1 && $m <= 12);
        if (empty($mesesArray)) {
            return 'ALL';
        }
        if (count($mesesArray) === 12) {
            return 'ALL';
        }
        sort($mesesArray);
        return implode(',', $mesesArray);
    }

    /**
     * Parsear meses a array de enteros
     */
    public static function parsearMeses($meses)
    {
        if ($meses === 'ALL' || empty($meses)) {
            return range(1, 12);
        }
        return array_map('intval', explode(',', $meses));
    }

    /**
     * Verificar si dos conjuntos de meses se solapan
     */
    private function mesesSolapan($mesesA, $mesesB)
    {
        if ($mesesA === 'ALL' || $mesesB === 'ALL') {
            return true;
        }
        if (empty($mesesA) || empty($mesesB)) {
            return true;
        }
        $setA = array_map('intval', explode(',', $mesesA));
        $setB = array_map('intval', explode(',', $mesesB));
        return count(array_intersect($setA, $setB)) > 0;
    }

    /**
     * Verificar si un conjunto de meses contiene un mes específico
     */
    private function contieneMes($meses, $mesNum)
    {
        if ($meses === 'ALL' || empty($meses)) {
            return true;
        }
        $mesesArray = array_map('intval', explode(',', $meses));
        return in_array($mesNum, $mesesArray);
    }

    /**
     * Obtener todos los registradores activos
     */
    public function obtenerRegistradores()
    {
        $sql = "SELECT id, username, nombre_completo, activo 
                FROM usuarios 
                WHERE rol = 'registrador' AND activo = 1 
                ORDER BY nombre_completo ASC";
        return $this->db->query($sql);
    }

    /**
     * Obtener estadísticas de asignaciones por registrador para un año
     */
    public function obtenerEstadisticas($anio)
    {
        $sql = "SELECT u.id, u.nombre_completo, u.username,
                       COUNT(ae.establecimiento_id) as total_establecimientos
                FROM usuarios u
                LEFT JOIN asignaciones_establecimientos ae ON u.id = ae.usuario_id AND ae.anio = ?
                WHERE u.rol = 'registrador' AND u.activo = 1
                GROUP BY u.id, u.nombre_completo, u.username
                ORDER BY u.nombre_completo ASC";
        return $this->db->query($sql, [$anio]);
    }

    /**
     * Obtener todos los establecimientos activos con información de asignación
     */
    public function obtenerEstablecimientosConAsignacion($registradorId, $anio)
    {
        $sql = "SELECT e.*, c.nombre as comuna_nombre,
                       CASE WHEN ae_mi.usuario_id IS NOT NULL THEN 1 ELSE 0 END as asignado_a_mi,
                       ae_mi.meses as meses_mios,
                       ae_mi.tipo_asignacion as tipo_asignacion_mi,
                       (SELECT a1.usuario_id
                        FROM asignaciones_establecimientos a1
                        WHERE a1.establecimiento_id = e.id AND a1.anio = ? AND a1.usuario_id != ?
                        LIMIT 1) as asignado_a_usuario_id,
                       (SELECT u2.nombre_completo
                        FROM asignaciones_establecimientos a2
                        INNER JOIN usuarios u2 ON a2.usuario_id = u2.id
                        WHERE a2.establecimiento_id = e.id AND a2.anio = ? AND a2.usuario_id != ?
                        LIMIT 1) as asignado_a_nombre,
                       (SELECT a3.meses
                        FROM asignaciones_establecimientos a3
                        WHERE a3.establecimiento_id = e.id AND a3.anio = ? AND a3.usuario_id != ?
                        LIMIT 1) as meses_otro,
                       (SELECT a4.tipo_asignacion
                        FROM asignaciones_establecimientos a4
                        WHERE a4.establecimiento_id = e.id AND a4.anio = ? AND a4.usuario_id != ?
                        LIMIT 1) as tipo_asignacion_otro
                FROM establecimientos e
                INNER JOIN comunas c ON e.comuna_id = c.id
                LEFT JOIN asignaciones_establecimientos ae_mi
                       ON e.id = ae_mi.establecimiento_id AND ae_mi.anio = ? AND ae_mi.usuario_id = ?
                WHERE e.activo = 1
                ORDER BY c.nombre ASC, e.nombre ASC";
        return $this->db->query($sql, [$anio, $registradorId, $anio, $registradorId, $anio, $registradorId, $anio, $registradorId, $anio, $registradorId]);
    }

    /**
     * Obtener establecimientos asignados a un registrador para un año
     */
    public function obtenerPorRegistrador($registradorId, $anio)
    {
        $sql = "SELECT ae.id as asignacion_id, ae.anio, ae.meses, ae.tipo_asignacion, 
                       e.*, c.nombre as comuna_nombre 
                FROM asignaciones_establecimientos ae
                INNER JOIN establecimientos e ON ae.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE ae.usuario_id = ? AND ae.anio = ?
                ORDER BY c.nombre ASC, e.nombre ASC";
        return $this->db->query($sql, [$registradorId, $anio]);
    }

    /**
     * Verificar conflicto de asignación temporal
     * Rechazar si mismo mes ya tiene temporal de otro usuario
     */
    private function verificarConflictoTemporal($usuarioId, $establecimientoId, $anio, $meses)
    {
        $sql = "SELECT usuario_id, meses FROM asignaciones_establecimientos 
                WHERE establecimiento_id = ? AND anio = ? AND usuario_id != ?
                AND tipo_asignacion = 'temporal'";
        $filas = $this->db->query($sql, [$establecimientoId, $anio, $usuarioId]);
        
        foreach ($filas as $fila) {
            if ($this->mesesSolapan($fila['meses'], $meses)) {
                return $fila;
            }
        }
        return false;
    }

    /**
     * Verificar conflicto de asignación anual
     */
    private function verificarConflictoAnual($usuarioId, $establecimientoId, $anio, $meses)
    {
        $sql = "SELECT usuario_id, meses, tipo_asignacion FROM asignaciones_establecimientos 
                WHERE establecimiento_id = ? AND anio = ? AND usuario_id != ?";
        $filas = $this->db->query($sql, [$establecimientoId, $anio, $usuarioId]);
        
        foreach ($filas as $fila) {
            if ($this->mesesSolapan($fila['meses'], $meses)) {
                return $fila;
            }
        }
        return false;
    }

    /**
     * Obtener asignación propia existente
     */
    private function obtenerAsignacionPropia($usuarioId, $establecimientoId, $anio, $tipo)
    {
        $sql = "SELECT id, meses FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ? AND tipo_asignacion = ?
                LIMIT 1";
        return $this->db->queryOne($sql, [$usuarioId, $establecimientoId, $anio, $tipo]);
    }

    /**
     * Crear o actualizar una asignación
     * Reglas de fusión:
     * - ALL + lista específica → la nueva lista reemplaza
     * - ALL + ALL → sin cambios
     * - Lista + ALL → se actualiza a ALL
     * - Lista + Lista → se fusionan (unión)
     */
    public function crear($usuarioId, $establecimientoId, $anio, $meses = 'ALL', $tipo = 'anual')
    {
        $meses = self::normalizarMeses($meses);
        $tipo = ($tipo === 'temporal') ? 'temporal' : 'anual';

        // Validar que temporal tenga meses específicos
        if ($tipo === 'temporal' && $meses === 'ALL') {
            throw new Exception('Para asignación temporal debe especificar los meses');
        }

        // Verificar conflictos con otros usuarios
        if ($tipo === 'temporal') {
            $conflicto = $this->verificarConflictoTemporal($usuarioId, $establecimientoId, $anio, $meses);
            if ($conflicto) {
                throw new Exception('Ya existe una asignación temporal para ese periodo con otro registrador');
            }
        } else {
            $conflicto = $this->verificarConflictoAnual($usuarioId, $establecimientoId, $anio, $meses);
            if ($conflicto) {
                throw new Exception('El establecimiento ya está asignado a otro registrador para ese periodo');
            }
        }

        // Verificar si ya existe asignación propia del mismo tipo
        $propia = $this->obtenerAsignacionPropia($usuarioId, $establecimientoId, $anio, $tipo);
        
        if ($propia) {
            // Aplicar reglas de fusión
            $mesesFusionados = $this->fusionarMeses($propia['meses'], $meses);
            
            if (empty($mesesFusionados)) {
                // Si no quedan meses, eliminar el registro
                $this->eliminar($usuarioId, $establecimientoId, $anio, $tipo);
                return true;
            }
            
            $sql = "UPDATE asignaciones_establecimientos SET meses = ?, fecha_actualizacion = NOW() 
                    WHERE id = ?";
            $this->db->execute($sql, [$mesesFusionados, $propia['id']]);
            return true;
        }

        // Insertar nueva asignación
        $sql = "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio, meses, tipo_asignacion, fecha_creacion, fecha_actualizacion) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio, $meses, $tipo]);
        return true;
    }

    /**
     * Fusionar meses según reglas de negocio
     */
    private function fusionarMeses($mesesActual, $mesesNuevo)
    {
        // ALL + ALL → sin cambios
        if ($mesesActual === 'ALL' && $mesesNuevo === 'ALL') {
            return 'ALL';
        }
        
        // ALL + lista específica → la nueva lista reemplaza
        if ($mesesActual === 'ALL' && $mesesNuevo !== 'ALL') {
            return $mesesNuevo;
        }
        
        // Lista + ALL → se actualiza a ALL
        if ($mesesActual !== 'ALL' && $mesesNuevo === 'ALL') {
            return 'ALL';
        }
        
        // Lista + Lista → se fusionan (unión)
        if ($mesesActual !== 'ALL' && $mesesNuevo !== 'ALL') {
            $setA = array_map('intval', explode(',', $mesesActual));
            $setB = array_map('intval', explode(',', $mesesNuevo));
            $union = array_unique(array_merge($setA, $setB));
            sort($union);
            
            if (count($union) === 12) {
                return 'ALL';
            }
            
            return implode(',', $union);
        }
        
        return 'ALL';
    }

    /**
     * Actualizar una asignación existente
     */
    public function actualizar($id, $meses, $tipo = 'anual')
    {
        $meses = self::normalizarMeses($meses);
        $tipo = ($tipo === 'temporal') ? 'temporal' : 'anual';

        // Si se remueven todos los meses, eliminar el registro
        if (empty($meses) || $meses === '') {
            return $this->eliminarPorId($id);
        }

        $sql = "UPDATE asignaciones_establecimientos SET meses = ?, tipo_asignacion = ?, fecha_actualizacion = NOW() 
                WHERE id = ?";
        $resultado = $this->db->execute($sql, [$meses, $tipo, $id]);
        
        return $resultado;
    }

    /**
     * Eliminar una asignación
     */
    public function eliminar($usuarioId, $establecimientoId, $anio, $tipo = 'anual')
    {
        $sql = "DELETE FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ? AND tipo_asignacion = ?";
        return $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio, $tipo]);
    }

    /**
     * Eliminar por ID
     */
    public function eliminarPorId($id)
    {
        $sql = "DELETE FROM asignaciones_establecimientos WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Asignación masiva transaccional
     * Si falla alguna, se hace rollback completo
     */
    public function asignacionMasiva($usuarioId, $establecimientoIds, $anio, $meses = 'ALL', $tipo = 'anual')
    {
        $meses = self::normalizarMeses($meses);
        $tipo = ($tipo === 'temporal') ? 'temporal' : 'anual';

        if ($tipo === 'temporal' && $meses === 'ALL') {
            throw new Exception('Para asignación temporal debe especificar los meses');
        }

        $this->db->beginTransaction();
        
        try {
            foreach ($establecimientoIds as $establecimientoId) {
                $this->crear($usuarioId, $establecimientoId, $anio, $meses, $tipo);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Copiar asignaciones de un año a otro
     * Incluye anuales y temporales
     */
    public function copiarAsignaciones($anioOrigen, $anioDestino)
    {
        $this->db->beginTransaction();
        
        try {
            // Obtener todas las asignaciones del año origen
            $sql = "SELECT usuario_id, establecimiento_id, meses, tipo_asignacion 
                    FROM asignaciones_establecimientos 
                    WHERE anio = ?";
            $asignaciones = $this->db->query($sql, [$anioOrigen]);
            
            // Insertar en el año destino
            $sqlInsert = "INSERT INTO asignaciones_establecimientos 
                          (usuario_id, establecimiento_id, anio, meses, tipo_asignacion, fecha_creacion, fecha_actualizacion)
                          VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            
            foreach ($asignaciones as $asignacion) {
                $this->db->execute($sqlInsert, [
                    $asignacion['usuario_id'],
                    $asignacion['establecimiento_id'],
                    $anioDestino,
                    $asignacion['meses'],
                    $asignacion['tipo_asignacion']
                ]);
            }
            
            $this->db->commit();
            return count($asignaciones);
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Obtener todas las asignaciones temporales activas para un año
     */
    public function obtenerTemporales($anio)
    {
        $sql = "SELECT ae.id, ae.meses, ae.fecha_creacion,
                       u.id as registrador_id, u.nombre_completo as registrador_nombre,
                       e.id as establecimiento_id, e.nombre as establecimiento_nombre, 
                       e.codigo_establecimiento, e.nombre_corto,
                       c.nombre as comuna_nombre
                FROM asignaciones_establecimientos ae
                INNER JOIN usuarios u ON ae.usuario_id = u.id
                INNER JOIN establecimientos e ON ae.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE ae.anio = ? AND ae.tipo_asignacion = 'temporal'
                ORDER BY ae.fecha_creacion DESC";
        return $this->db->query($sql, [$anio]);
    }

    /**
     * Obtener el titular anual de un establecimiento
     */
    public function obtenerTitularAnual($establecimientoId, $anio)
    {
        $sql = "SELECT u.id, u.nombre_completo, ae.meses
                FROM asignaciones_establecimientos ae
                INNER JOIN usuarios u ON ae.usuario_id = u.id
                WHERE ae.establecimiento_id = ? AND ae.anio = ? AND ae.tipo_asignacion = 'anual'
                LIMIT 1";
        return $this->db->queryOne($sql, [$establecimientoId, $anio]);
    }

    /**
     * Obtener referentes de un establecimiento
     */
    public function obtenerReferentes($establecimientoId)
    {
        $sql = "SELECT * FROM referentes_establecimientos 
                WHERE establecimiento_id = ? AND activo = 1
                ORDER BY FIELD(cargo, 'Encargado Estadísticas', 'Digitador Estadísticas') ASC";
        return $this->db->query($sql, [$establecimientoId]);
    }

    /**
     * Verificar si un usuario tiene asignado un establecimiento para un mes específico
     */
    public function tieneAsignacionParaMes($usuarioId, $establecimientoId, $anio, $mesNum)
    {
        // Primero buscar asignación temporal del usuario
        $sql = "SELECT meses FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ? 
                AND tipo_asignacion = 'temporal'";
        $fila = $this->db->queryOne($sql, [$usuarioId, $establecimientoId, $anio]);
        
        if ($fila && $this->contieneMes($fila['meses'], $mesNum)) {
            return true;
        }

        // Buscar asignación anual del usuario
        $sql = "SELECT meses FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ? 
                AND tipo_asignacion = 'anual'";
        $fila = $this->db->queryOne($sql, [$usuarioId, $establecimientoId, $anio]);
        
        if (!$fila) return false;

        if ($this->contieneMes($fila['meses'], $mesNum)) {
            // Verificar que no haya temporal de otro usuario para este mes
            $sql = "SELECT meses FROM asignaciones_establecimientos 
                    WHERE establecimiento_id = ? AND anio = ? AND usuario_id != ?
                    AND tipo_asignacion = 'temporal'";
            $temporales = $this->db->query($sql, [$establecimientoId, $anio, $usuarioId]);
            
            foreach ($temporales as $temp) {
                if ($this->contieneMes($temp['meses'], $mesNum)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }
}
