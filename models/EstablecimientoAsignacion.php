<?php
/**
 * Clase EstablecimientoAsignacion
 * Manejo de asignaciones de establecimientos a registradores por año y meses
 * Soporta reasignaciones temporales por meses sin solapamiento.
 */

require_once __DIR__ . '/Database.php';

class EstablecimientoAsignacion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todos los registradores activos
     */
    public function getAllRegistradores()
    {
        $sql = "SELECT id, username, nombre_completo 
                FROM usuarios 
                WHERE rol = 'registrador' AND activo = 1 
                ORDER BY nombre_completo ASC";
        return $this->db->query($sql);
    }

    /**
     * Obtener establecimientos asignados a un registrador para un año específico
     */
    public function getEstablecimientosByRegistrador($registradorId, $anio)
    {
        $sql = "SELECT ae.id as asignacion_id, ae.anio, ae.meses, ae.tipo_asignacion, e.*, c.nombre as comuna_nombre 
                FROM asignaciones_establecimientos ae
                INNER JOIN establecimientos e ON ae.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE ae.usuario_id = ? AND ae.anio = ?
                ORDER BY c.nombre ASC, e.nombre ASC";
        return $this->db->query($sql, [$registradorId, $anio]);
    }

    /**
     * Obtener todos los establecimientos
     */
    public function getAllEstablecimientos()
    {
        $sql = "SELECT e.*, c.nombre as comuna_nombre 
                FROM establecimientos e
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE e.activo = 1
                ORDER BY c.nombre ASC, e.nombre ASC";
        return $this->db->query($sql);
    }

    /**
     * Obtener todos los establecimientos activos con información de asignación
     * para un año y registrador específico.
     *
     * Campos extra:
     *   asignado_a_mi  (0/1)
     *   asignado_a_usuario_id  (NULL o ID del dueño con asignación 'ALL')
     *   asignado_a_nombre      (NULL o nombre del dueño con asignación 'ALL')
     *   meses_mios             (NULL o meses asignados a mí)
     *   meses_otro             (NULL o meses asignados a otro)
     *   tipo_asignacion_mi     (NULL o tipo de mi asignación)
     *   tipo_asignacion_otro   (NULL o tipo de asignación de otro)
     */
    public function getEstablecimientosConAsignacion($registradorId, $anio)
    {
        // Subconsultas correlacionadas para obtener info del primer otro asignado sin causar duplicados
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
     * Verificar si dos conjuntos de meses se solapan
     */
    private function mesesSolapan($mesesA, $mesesB)
    {
        // Si alguno es ALL, siempre se solapan
        if ($mesesA === 'ALL' || empty($mesesA) || $mesesB === 'ALL' || empty($mesesB)) {
            return true;
        }
        $setA = array_map('intval', explode(',', $mesesA));
        $setB = array_map('intval', explode(',', $mesesB));
        return count(array_intersect($setA, $setB)) > 0;
    }

    /**
     * Verificar si un conjunto de meses contiene un mes específico
     */
    private function tieneMes($meses, $mesNum)
    {
        if ($meses === 'ALL' || empty($meses)) {
            return true;
        }
        $mesesArray = array_map('intval', explode(',', $meses));
        return in_array($mesNum, $mesesArray);
    }

    /**
     * Convertir nombre de mes a número (1-12)
     */
    private function getMesNumero($mesNombre)
    {
        $mesesMap = [
            'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4, 
            'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8, 
            'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
        ];
        return $mesesMap[$mesNombre] ?? null;
    }

    /**
     * Verificar si existe una reasignación temporal de OTRO usuario para un mes específico
     */
    private function existeReasignacionTemporalOtro($usuarioId, $establecimientoId, $anio, $mesNum)
    {
        $sql = "SELECT meses FROM asignaciones_establecimientos 
                WHERE establecimiento_id = ? AND anio = ? AND usuario_id != ?
                AND tipo_asignacion = 'temporal'";
        $rows = $this->db->query($sql, [$establecimientoId, $anio, $usuarioId]);
        
        foreach ($rows as $row) {
            if ($this->tieneMes($row['meses'], $mesNum)) {
                return true; // Otro usuario tiene reasignación temporal para este mes
            }
        }
        return false;
    }

    /**
     * Obtener los meses asignados a otro usuario para un establecimiento/año
     * que se solapan con los meses solicitados.
     * 
     * @param string $tipo 'anual' o 'temporal'
     */
    private function getConflictoAsignacion($usuarioId, $establecimientoId, $anio, $meses, $tipo = 'anual')
    {
        // Si es asignación TEMPORAL, permitir solapamiento con anual
        // Solo verificar conflicto con otra asignación TEMPORAL del mismo periodo
        if ($tipo === 'temporal') {
            $sql = "SELECT usuario_id, meses FROM asignaciones_establecimientos 
                    WHERE establecimiento_id = ? AND anio = ? AND usuario_id != ?
                    AND tipo_asignacion = 'temporal'";
            $rows = $this->db->query($sql, [$establecimientoId, $anio, $usuarioId]);
            foreach ($rows as $row) {
                if ($this->mesesSolapan($row['meses'], $meses)) {
                    return $row; // Conflicto con otra temporal
                }
            }
            return false; // No hay conflicto, permitir temporal sobre anual
        }

        // Si es asignación ANUAL, verificar conflictos con cualquier tipo
        $sql = "SELECT usuario_id, meses, tipo_asignacion FROM asignaciones_establecimientos 
                WHERE establecimiento_id = ? AND anio = ? AND usuario_id != ?";
        $rows = $this->db->query($sql, [$establecimientoId, $anio, $usuarioId]);
        foreach ($rows as $row) {
            if ($this->mesesSolapan($row['meses'], $meses)) {
                return $row; // Conflicto encontrado
            }
        }
        return false;
    }

    /**
     * Obtener la asignación propia existente para un establecimiento/año
     * @param string $tipo 'anual' o 'temporal'
     */
    private function getAsignacionPropia($usuarioId, $establecimientoId, $anio, $tipo = 'anual')
    {
        $sql = "SELECT id, meses FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ? AND tipo_asignacion = ?
                LIMIT 1";
        return $this->db->queryOne($sql, [$usuarioId, $establecimientoId, $anio, $tipo]);
    }

    /**
     * Fusionar dos conjuntos de meses
     */
    private function fusionarMeses($mesesA, $mesesB)
    {
        if ($mesesA === 'ALL' || $mesesB === 'ALL' || empty($mesesA) || empty($mesesB)) {
            return 'ALL';
        }
        $setA = array_map('intval', explode(',', $mesesA));
        $setB = array_map('intval', explode(',', $mesesB));
        $union = array_unique(array_merge($setA, $setB));
        sort($union);
        return implode(',', $union);
    }

    /**
     * Restar meses de un conjunto
     */
    private function restarMeses($mesesTotal, $mesesQuitar)
    {
        if ($mesesTotal === 'ALL' || empty($mesesTotal)) {
            return 'ALL'; // No se pueden quitar meses de un ALL mediante resta parcial
        }
        if ($mesesQuitar === 'ALL' || empty($mesesQuitar)) {
            return $mesesTotal;
        }
        $setTotal = array_map('intval', explode(',', $mesesTotal));
        $setQuitar = array_map('intval', explode(',', $mesesQuitar));
        $resultado = array_diff($setTotal, $setQuitar);
        if (empty($resultado)) {
            return '';
        }
        sort($resultado);
        return implode(',', $resultado);
    }

    /**
     * Asignar un establecimiento a un registrador para un año
     * @param string $meses 'ALL' o lista de meses '1,2,3'
     * @param string $tipo 'anual' o 'temporal'
     */
    public function asignar($usuarioId, $establecimientoId, $anio, $meses = 'ALL', $tipo = 'anual')
    {
        // Normalizar meses
        if (empty($meses)) {
            $meses = 'ALL';
        }

        // Normalizar tipo
        if ($tipo !== 'temporal') {
            $tipo = 'anual';
        }

        // Verificar conflictos con otros usuarios
        $conflicto = $this->getConflictoAsignacion($usuarioId, $establecimientoId, $anio, $meses, $tipo);
        if ($conflicto) {
            return false; // Hay solapamiento con otro usuario
        }

        // Verificar si ya existe una asignación propia del MISMO tipo
        $propia = $this->getAsignacionPropia($usuarioId, $establecimientoId, $anio, $tipo);
        if ($propia) {
            // Fusionar meses
            $nuevosMeses = $this->fusionarMeses($propia['meses'], $meses);
            $sql = "UPDATE asignaciones_establecimientos SET meses = ? WHERE id = ?";
            try {
                $this->db->execute($sql, [$nuevosMeses, $propia['id']]);
                return true;
            } catch (Exception $e) {
                error_log("Error al actualizar asignación: " . $e->getMessage());
                return false;
            }
        }

        // Insertar nueva asignación
        $sql = "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio, meses, tipo_asignacion) 
                VALUES (?, ?, ?, ?, ?)";
        try {
            $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio, $meses, $tipo]);
            return true;
        } catch (Exception $e) {
            error_log("Error al asignar establecimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remover asignación completa o parcial (por meses)
     * @param string $meses 'ALL' para eliminar todo, o lista de meses '1,2,3' para eliminar solo esos meses
     * @param string $tipo 'anual' o 'temporal'
     */
    public function remover($usuarioId, $establecimientoId, $anio, $meses = 'ALL', $tipo = 'anual')
    {
        // Normalizar tipo
        if ($tipo !== 'temporal') {
            $tipo = 'anual';
        }

        $propia = $this->getAsignacionPropia($usuarioId, $establecimientoId, $anio, $tipo);
        if (!$propia) {
            return false; // No existe asignación de ese tipo
        }

        if ($meses === 'ALL' || empty($meses) || $propia['meses'] === 'ALL' || empty($propia['meses'])) {
            // Eliminar todo
            $sql = "DELETE FROM asignaciones_establecimientos 
                    WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ? AND tipo_asignacion = ?";
            try {
                return $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio, $tipo]);
            } catch (Exception $e) {
                error_log("Error al remover asignación: " . $e->getMessage());
                return false;
            }
        }

        // Restar meses específicos
        $nuevosMeses = $this->restarMeses($propia['meses'], $meses);
        if (empty($nuevosMeses)) {
            $sql = "DELETE FROM asignaciones_establecimientos 
                    WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ? AND tipo_asignacion = ?";
            try {
                return $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio, $tipo]);
            } catch (Exception $e) {
                error_log("Error al remover asignación: " . $e->getMessage());
                return false;
            }
        } else {
            $sql = "UPDATE asignaciones_establecimientos SET meses = ? WHERE id = ?";
            try {
                return $this->db->execute($sql, [$nuevosMeses, $propia['id']]);
            } catch (Exception $e) {
                error_log("Error al actualizar asignación parcial: " . $e->getMessage());
                return false;
            }
        }
    }

    /**
     * Remover todas las asignaciones de un registrador para un año
     */
    public function removerTodas($usuarioId, $anio)
    {
        $sql = "DELETE FROM asignaciones_establecimientos WHERE usuario_id = ? AND anio = ?";
        try {
            return $this->db->execute($sql, [$usuarioId, $anio]);
        } catch (Exception $e) {
            error_log("Error al remover todas las asignaciones: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asignar múltiples establecimientos a un registrador para un año
     * No elimina asignaciones existentes; fusiona meses si ya existe.
     * @param string $tipo 'anual' o 'temporal'
     */
    public function asignarMultiple($usuarioId, $establecimientoIds, $anio, $meses = 'ALL', $tipo = 'anual')
    {
        if (empty($meses)) {
            $meses = 'ALL';
        }

        // Normalizar tipo
        if ($tipo !== 'temporal') {
            $tipo = 'anual';
        }

        try {
            $this->db->beginTransaction();

            foreach ($establecimientoIds as $establecimientoId) {
                // Verificar conflictos con otros usuarios
                $conflicto = $this->getConflictoAsignacion($usuarioId, $establecimientoId, $anio, $meses, $tipo);
                if ($conflicto) {
                    continue; // Saltar este establecimiento
                }

                // Verificar si ya existe asignación propia del mismo tipo
                $propia = $this->getAsignacionPropia($usuarioId, $establecimientoId, $anio, $tipo);
                if ($propia) {
                    $nuevosMeses = $this->fusionarMeses($propia['meses'], $meses);
                    $sql = "UPDATE asignaciones_establecimientos SET meses = ? WHERE id = ?";
                    $this->db->execute($sql, [$nuevosMeses, $propia['id']]);
                } else {
                    $sql = "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio, meses, tipo_asignacion) 
                            VALUES (?, ?, ?, ?, ?)";
                    $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio, $meses, $tipo]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al asignar múltiples establecimientos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener IDs de establecimientos asignados a un registrador para un año
     */
    public function getIdsAsignados($usuarioId, $anio)
    {
        $sql = "SELECT establecimiento_id FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND anio = ?";
        $rows = $this->db->query($sql, [$usuarioId, $anio]);
        return array_map(fn($r) => (int)$r['establecimiento_id'], $rows);
    }

    /**
     * Verificar si un usuario tiene asignado un establecimiento para un mes específico
     * Lógica de prioridad: Las asignaciones temporales tienen prioridad sobre las anuales
     */
    public function tieneAsignacionParaMes($usuarioId, $establecimientoId, $anio, $mesNombre)
    {
        $mesNum = $this->getMesNumero($mesNombre);
        if (!$mesNum) return false;

        // 1. Buscar asignación TEMPORAL del usuario primero (prioridad)
        $sql = "SELECT meses FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ? 
                AND tipo_asignacion = 'temporal'";
        $row = $this->db->queryOne($sql, [$usuarioId, $establecimientoId, $anio]);
        
        if ($row && $this->tieneMes($row['meses'], $mesNum)) {
            return true; // Tiene asignación temporal para este mes
        }

        // 2. Buscar asignación ANUAL del usuario
        $sql = "SELECT meses FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ? 
                AND tipo_asignacion = 'anual'";
        $row = $this->db->queryOne($sql, [$usuarioId, $establecimientoId, $anio]);
        
        if (!$row) return false;

        // Si es ALL, tiene acceso a todos los meses (salvo que haya reasignación temporal de otro)
        if ($row['meses'] === 'ALL' || empty($row['meses'])) {
            // Verificar que no haya una reasignación temporal de OTRO usuario para este mes
            if ($this->existeReasignacionTemporalOtro($usuarioId, $establecimientoId, $anio, $mesNum)) {
                return false; // Otro usuario tiene reasignación temporal para este mes
            }
            return true;
        }

        // Verificar si el mes está en la lista de la asignación anual
        if ($this->tieneMes($row['meses'], $mesNum)) {
            // Verificar que no haya una reasignación temporal de OTRO usuario
            if ($this->existeReasignacionTemporalOtro($usuarioId, $establecimientoId, $anio, $mesNum)) {
                return false; // Otro usuario tiene reasignación temporal para este mes
            }
            return true;
        }

        return false;
    }

    /**
     * Verificar si un registrador tiene establecimientos asignados para un año
     */
    public function tieneAsignaciones($usuarioId, $anio)
    {
        $sql = "SELECT COUNT(*) as count FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND anio = ?";
        $result = $this->db->queryOne($sql, [$usuarioId, $anio]);
        return $result && $result['count'] > 0;
    }

    /**
     * Obtener registradores que NO tienen establecimientos asignados para un año
     */
    public function getRegistradoresSinAsignaciones($anio)
    {
        $sql = "SELECT u.id, u.username, u.nombre_completo 
                FROM usuarios u
                WHERE u.rol = 'registrador' AND u.activo = 1
                  AND u.id NOT IN (
                      SELECT DISTINCT usuario_id 
                      FROM asignaciones_establecimientos 
                      WHERE anio = ?
                  )
                ORDER BY u.nombre_completo ASC";
        return $this->db->query($sql, [$anio]);
    }

    /**
     * Obtener estadísticas de asignaciones por registrador para un año
     */
    public function getEstadisticasAsignaciones($anio)
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
     * Copiar asignaciones de un año a otro
     * Incluye tanto asignaciones anuales como temporales
     */
    public function copiarAsignaciones($anioOrigen, $anioDestino)
    {
        try {
            $sql = "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio, meses, tipo_asignacion)
                    SELECT usuario_id, establecimiento_id, ?, meses, tipo_asignacion
                    FROM asignaciones_establecimientos 
                    WHERE anio = ?";
            $this->db->execute($sql, [$anioDestino, $anioOrigen]);
            return true;
        } catch (Exception $e) {
            error_log("Error al copiar asignaciones: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener referentes de un establecimiento
     */
    public function getReferentes($establecimientoId)
    {
        $sql = "SELECT * FROM referentes_establecimientos 
                WHERE establecimiento_id = ? AND activo = 1
                ORDER BY FIELD(cargo, 'Encargado Estadísticas', 'Digitador Estadísticas') ASC";
        return $this->db->query($sql, [$establecimientoId]);
    }

    /**
     * Obtener referentes de múltiples establecimientos
     */
    public function getReferentesMultiple($establecimientoIds)
    {
        if (empty($establecimientoIds)) return [];
        
        $placeholders = implode(',', array_fill(0, count($establecimientoIds), '?'));
        $sql = "SELECT * FROM referentes_establecimientos 
                WHERE establecimiento_id IN ($placeholders) AND activo = 1
                ORDER BY establecimiento_id, FIELD(cargo, 'Encargado Estadísticas', 'Digitador Estadísticas') ASC";
        return $this->db->query($sql, $establecimientoIds);
    }

    /**
     * Obtener todas las asignaciones temporales activas para un año
     * Incluye información del registrador temporal y el establecimiento
     */
    public function getAsignacionesTemporalesActivas($anio)
    {
        $sql = "SELECT ae.id, ae.meses, ae.fecha_asignacion,
                       u.id as registrador_id, u.nombre_completo as registrador_nombre,
                       e.id as establecimiento_id, e.nombre as establecimiento_nombre, 
                       e.codigo_establecimiento, e.nombre_corto,
                       c.nombre as comuna_nombre
                FROM asignaciones_establecimientos ae
                INNER JOIN usuarios u ON ae.usuario_id = u.id
                INNER JOIN establecimientos e ON ae.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE ae.anio = ? AND ae.tipo_asignacion = 'temporal'
                ORDER BY ae.fecha_asignacion DESC";
        return $this->db->query($sql, [$anio]);
    }

    /**
     * Obtener el titular anual de un establecimiento (para mostrar en reasignaciones temporales)
     */
    public function getTitularAnual($establecimientoId, $anio)
    {
        $sql = "SELECT u.id, u.nombre_completo, ae.meses
                FROM asignaciones_establecimientos ae
                INNER JOIN usuarios u ON ae.usuario_id = u.id
                WHERE ae.establecimiento_id = ? AND ae.anio = ? AND ae.tipo_asignacion = 'anual'
                LIMIT 1";
        return $this->db->queryOne($sql, [$establecimientoId, $anio]);
    }
}
