<?php
/**
 * Clase ReportQueue
 * Gestión de la cola de reportes asíncronos
 */

require_once __DIR__ . '/Database.php';

class ReportQueue
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Encolar un nuevo reporte
     */
    public function enqueue($userId, $tipoReporte, $formato, $parametros = [])
    {
        $sql = "INSERT INTO reportes_pendientes (usuario_id, tipo_reporte, formato, parametros) 
                VALUES (?, ?, ?, ?)";
        
        try {
            $this->db->execute($sql, [
                $userId, 
                $tipoReporte, 
                $formato, 
                json_encode($parametros)
            ]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al encolar reporte: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener reportes pendientes de un usuario
     */
    public function getUserReports($userId)
    {
        $sql = "SELECT * FROM reportes_pendientes 
                WHERE usuario_id = ? 
                ORDER BY fecha_creacion DESC 
                LIMIT 50";
        
        return $this->db->query($sql, [$userId]);
    }

    /**
     * Obtener siguiente reporte pendiente para procesar (Worker)
     */
    public function getNextPending()
    {
        $sql = "SELECT * FROM reportes_pendientes 
                WHERE estado = 'PENDIENTE' 
                ORDER BY fecha_creacion ASC 
                LIMIT 1 FOR UPDATE";
        
        return $this->db->queryOne($sql);
    }

    /**
     * Actualizar estado del reporte
     */
    public function updateStatus($reportId, $estado, $archivoUrl = null, $mensajeError = null)
    {
        $sql = "UPDATE reportes_pendientes 
                SET estado = ?, 
                    archivo_url = ?, 
                    mensaje_error = ?,
                    fecha_procesamiento = NOW() 
                WHERE id = ?";
        
        try {
            return $this->db->execute($sql, [$estado, $archivoUrl, $mensajeError, $reportId]);
        } catch (Exception $e) {
            error_log("Error al actualizar estado de reporte: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar reporte como procesando
     */
    public function markProcessing($reportId)
    {
        return $this->updateStatus($reportId, 'PROCESANDO');
    }

    /**
     * Marcar reporte como listo
     */
    public function markReady($reportId, $archivoUrl)
    {
        return $this->updateStatus($reportId, 'LISTO', $archivoUrl);
    }

    /**
     * Marcar reporte como error
     */
    public function markError($reportId, $mensajeError)
    {
        return $this->updateStatus($reportId, 'ERROR', null, $mensajeError);
    }
}
