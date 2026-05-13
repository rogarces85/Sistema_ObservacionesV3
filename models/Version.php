<?php
/**
 * Clase Version
 * Manejo de versiones y snapshots del sistema
 */

require_once __DIR__ . '/Database.php';

class Version
{
    private $db;
    private $snapshotDir;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->snapshotDir = __DIR__ . '/../uploads/versiones/';
        
        // Asegurar que el directorio existe
        if (!is_dir($this->snapshotDir)) {
            mkdir($this->snapshotDir, 0755, true);
        }
    }

    /**
     * Crear una nueva versión (Snapshot)
     */
    public function createVersion($descripcion, $userId)
    {
        // Generar tag único
        $lastVersion = $this->db->queryOne("SELECT version_tag FROM versiones_sistema ORDER BY id DESC LIMIT 1");
        $nextNum = $lastVersion ? (intval(str_replace('v', '', $lastVersion['version_tag'])) + 1) : 1;
        $versionTag = 'v' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        
        $snapshotPath = $this->snapshotDir . $versionTag . '/';
        
        // Crear directorio para el snapshot
        if (!mkdir($snapshotPath, 0755, true)) {
            throw new Exception("No se pudo crear el directorio de snapshot");
        }

        // Archivos a incluir (basado en la estructura del proyecto)
        $filesToInclude = [
            'api/', 'models/', 'views/', 'config/', 'includes/', 'assets/', 'index.php'
        ];
        
        $manifest = [];

        // Copiar archivos
        foreach ($filesToInclude as $source) {
            $sourcePath = __DIR__ . '/../' . $source;
            $destPath = $snapshotPath . $source;
            
            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath, $manifest);
            } elseif (is_file($sourcePath)) {
                $this->copyFile($sourcePath, $destPath, $manifest);
            }
        }

        // Guardar en BD
        $sql = "INSERT INTO versiones_sistema (version_tag, descripcion, usuario_id, snapshot_path, archivos_json) 
                VALUES (?, ?, ?, ?, ?)";
        
        try {
            $this->db->execute($sql, [
                $versionTag, 
                $descripcion, 
                $userId, 
                $versionTag . '/', 
                json_encode($manifest)
            ]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al crear versión: " . $e->getMessage());
            // Limpiar snapshot si falla BD
            $this->deleteDirectory($snapshotPath);
            return false;
        }
    }

    /**
     * Obtener todas las versiones
     */
    public function getAllVersions()
    {
        $sql = "SELECT v.*, u.nombre_completo as autor_nombre 
                FROM versiones_sistema v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                ORDER BY v.id DESC";
        return $this->db->query($sql);
    }

    /**
     * Obtener detalles de una versión
     */
    public function getVersionDetails($id)
    {
        $sql = "SELECT * FROM versiones_sistema WHERE id = ?";
        $version = $this->db->queryOne($sql, [$id]);
        
        if ($version) {
            $version['archivos_json'] = json_decode($version['archivos_json'], true);
        }
        
        return $version;
    }

    /**
     * Realizar Rollback a una versión
     */
    public function rollback($versionId, $userId)
    {
        $version = $this->getVersionDetails($versionId);
        if (!$version) {
            throw new Exception("Versión no encontrada");
        }

        $snapshotPath = $this->snapshotDir . $version['snapshot_path'];
        if (!is_dir($snapshotPath)) {
            throw new Exception("El snapshot de la versión no existe en el sistema de archivos");
        }

        // Restaurar archivos
        $manifest = $version['archivos_json'];
        foreach ($manifest as $relativePath => $hash) {
            $sourceFile = $snapshotPath . $relativePath;
            $destFile = __DIR__ . '/../' . $relativePath;
            
            if (file_exists($sourceFile)) {
                // Asegurar directorio destino
                $destDir = dirname($destFile);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                copy($sourceFile, $destFile);
            }
        }

        // Registrar que se hizo un rollback creando una nueva versión
        $newId = $this->createVersion("Rollback desde versión {$version['version_tag']}", $userId);
        
        return $newId;
    }

    // Helpers para copiar directorios y archivos
    private function copyDirectory($src, $dest, &$manifest) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->copyDirectory($src . '/' . $file, $dest . '/' . $file, $manifest);
                } else {
                    $this->copyFile($src . '/' . $file, $dest . '/' . $file, $manifest);
                }
            }
        }
        closedir($dir);
    }

    private function copyFile($src, $dest, &$manifest) {
        copy($src, $dest);
        // Guardar hash en manifiesto relativo a la raíz del proyecto
        $relativePath = str_replace(__DIR__ . '/../', '', $dest);
        $manifest[$relativePath] = md5_file($src);
    }

    private function deleteDirectory($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }
}
