<?php
/**
 * Clase VersionSistema
 * Manejo de versiones y snapshots del sistema
 * Fase 11 - Versionado y Snapshots
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class VersionSistema
{
    private $db;
    private $snapshotDir;
    private $raizProyecto;

    // Archivos y directorios excluidos del snapshot
    private $excluidos = [
        'node_modules',
        '.git',
        'uploads',
        'vendor',
        'assets/cache',
        '.env'
    ];

    // Extensiones permitidas para archivos sueltos
    private $extensionesPermitidas = ['.php', '.js', '.css', '.sql', '.json', '.md'];

    // Extensiones excluidas por patrón
    private $extensionesExcluidas = ['.log', '.tmp'];

    public function __construct()
    {
        $this->db = Database::obtenerInstancia();
        $this->raizProyecto = dirname(__DIR__);
        $this->snapshotDir = UPLOAD_PATH . '/versiones/';

        if (!is_dir($this->snapshotDir)) {
            mkdir($this->snapshotDir, 0755, true);
        }
    }

    /**
     * Listar todas las versiones ordenadas cronológicamente
     */
    public function listarVersiones()
    {
        $sql = "SELECT v.*, u.nombre_completo as autor_nombre 
                FROM versiones_sistema v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                ORDER BY v.id DESC";
        return $this->db->consultar($sql);
    }

    /**
     * Obtener detalle de una versión específica
     */
    public function obtenerDetalle($id)
    {
        $sql = "SELECT v.*, u.nombre_completo as autor_nombre 
                FROM versiones_sistema v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                WHERE v.id = ?";
        $version = $this->db->consultarUno($sql, [$id]);

        if ($version && $version['archivos_json']) {
            $version['manifiesto'] = json_decode($version['archivos_json'], true);
        } else {
            $version['manifiesto'] = [];
        }

        return $version;
    }

    /**
     * Obtener la última versión creada
     */
    public function obtenerUltimaVersion()
    {
        $sql = "SELECT version_tag FROM versiones_sistema ORDER BY id DESC LIMIT 1";
        return $this->db->consultarUno($sql);
    }

    /**
     * Generar el siguiente tag de versión (v001, v002... v999)
     */
    public function generarSiguienteTag()
    {
        $ultima = $this->obtenerUltimaVersion();

        if ($ultima) {
            $numeroActual = intval(str_replace('v', '', $ultima['version_tag']));
            $siguiente = $numeroActual + 1;
        } else {
            $siguiente = 1;
        }

        if ($siguiente > 999) {
            throw new Exception('Se alcanzó el límite máximo de versiones (v999)');
        }

        return 'v' . str_pad($siguiente, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Crear un nuevo snapshot del sistema
     */
    public function crearVersion($descripcion, $usuarioId)
    {
        $versionTag = $this->generarSiguienteTag();
        $snapshotPath = $this->snapshotDir . $versionTag . '/';

        // Crear directorio para el snapshot
        if (!mkdir($snapshotPath, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de snapshot');
        }

        $manifiesto = [];
        $archivosCopiados = 0;
        $errores = [];

        // Directorios a incluir en el snapshot
        $directorios = ['api', 'models', 'views', 'config', 'includes', 'assets'];

        foreach ($directorios as $directorio) {
            $origen = $this->raizProyecto . '/' . $directorio;
            $destino = $snapshotPath . $directorio;

            if (is_dir($origen)) {
                $resultado = $this->copiarDirectorio($origen, $destino, $manifiesto, $directorio);
                $archivosCopiados += $resultado['archivos'];
                $errores = array_merge($errores, $resultado['errores']);
            }
        }

        // Archivos sueltos en la raíz del proyecto
        $archivosRaiz = ['index.php', 'composer.json', 'composer.lock', 'README.md', 'AGENTS.md'];
        foreach ($archivosRaiz as $archivo) {
            $origen = $this->raizProyecto . '/' . $archivo;
            if (is_file($origen)) {
                $destino = $snapshotPath . $archivo;
                if (copy($origen, $destino)) {
                    $rutaRelativa = $archivo;
                    $manifiesto[$rutaRelativa] = [
                        'md5' => md5_file($origen),
                        'tamano' => filesize($origen),
                        'ruta' => $rutaRelativa
                    ];
                    $archivosCopiados++;
                } else {
                    $errores[] = $archivo;
                }
            }
        }

        // Guardar en base de datos
        $sql = "INSERT INTO versiones_sistema 
                (version_tag, descripcion, snapshot_path, archivos_json, usuario_id, fecha_creacion, fecha_actualizacion) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

        try {
            $this->db->ejecutar($sql, [
                $versionTag,
                $descripcion,
                $versionTag . '/',
                json_encode($manifiesto),
                $usuarioId
            ]);

            $idVersion = $this->db->ultimoIdInsertado();

            return [
                'id' => $idVersion,
                'version_tag' => $versionTag,
                'archivos_copiados' => $archivosCopiados,
                'errores' => $errores
            ];
        } catch (Exception $e) {
            // Limpiar snapshot si falla la base de datos
            $this->eliminarDirectorio($snapshotPath);
            throw new Exception('Error al guardar la versión en base de datos: ' . $e->getMessage());
        }
    }

    /**
     * Restaurar archivos desde una versión (rollback)
     */
    public function restaurarVersion($versionId, $usuarioId)
    {
        $version = $this->obtenerDetalle($versionId);

        if (!$version) {
            throw new Exception('Versión no encontrada');
        }

        $snapshotPath = $this->snapshotDir . $version['snapshot_path'];

        if (!is_dir($snapshotPath)) {
            throw new Exception('El snapshot de la versión no existe en el sistema de archivos');
        }

        $manifiesto = $version['manifiesto'];
        $archivosRestaurados = 0;
        $archivosFallidos = [];

        // Restaurar cada archivo del manifiesto
        foreach ($manifiesto as $rutaRelativa => $info) {
            $archivoOrigen = $snapshotPath . $rutaRelativa;
            $archivoDestino = $this->raizProyecto . '/' . $rutaRelativa;

            if (file_exists($archivoOrigen)) {
                // Asegurar que el directorio destino existe
                $directorioDestino = dirname($archivoDestino);
                if (!is_dir($directorioDestino)) {
                    mkdir($directorioDestino, 0755, true);
                }

                if (copy($archivoOrigen, $archivoDestino)) {
                    $archivosRestaurados++;
                } else {
                    $archivosFallidos[] = $rutaRelativa;
                }
            } else {
                $archivosFallidos[] = $rutaRelativa;
            }
        }

        // Crear un nuevo registro de versión que documenta el rollback
        $nuevoTag = $this->generarSiguienteTag();
        $descripcionRollback = "Rollback desde versión {$version['version_tag']}";

        $sql = "INSERT INTO versiones_sistema 
                (version_tag, descripcion, snapshot_path, archivos_json, usuario_id, fecha_creacion, fecha_actualizacion) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

        $this->db->ejecutar($sql, [
            $nuevoTag,
            $descripcionRollback,
            $nuevoTag . '/',
            json_encode($manifiesto),
            $usuarioId
        ]);

        return [
            'id_version_nueva' => $this->db->ultimoIdInsertado(),
            'version_tag_nueva' => $nuevoTag,
            'version_tag_origen' => $version['version_tag'],
            'archivos_restaurados' => $archivosRestaurados,
            'archivos_fallidos' => $archivosFallidos,
            'advertencia_bd' => 'Si hay cambios de esquema en la base de datos, ejecutar migraciones manualmente'
        ];
    }

    /**
     * Copiar un directorio recursivamente excluyendo rutas no permitidas
     */
    private function copiarDirectorio($origen, $destino, &$manifiesto, $prefijoRuta = '')
    {
        $archivosCopiados = 0;
        $errores = [];

        if (!is_dir($destino)) {
            mkdir($destino, 0755, true);
        }

        $elementos = scandir($origen);

        foreach ($elementos as $elemento) {
            if ($elemento === '.' || $elemento === '..') {
                continue;
            }

            $rutaOrigen = $origen . '/' . $elemento;
            $rutaDestino = $destino . '/' . $elemento;
            $rutaRelativa = $prefijoRuta ? $prefijoRuta . '/' . $elemento : $elemento;

            // Verificar si está excluido
            if ($this->estaExcluido($rutaRelativa, $elemento)) {
                continue;
            }

            if (is_dir($rutaOrigen)) {
                $resultado = $this->copiarDirectorio($rutaOrigen, $rutaDestino, $manifiesto, $rutaRelativa);
                $archivosCopiados += $resultado['archivos'];
                $errores = array_merge($errores, $resultado['errores']);
            } elseif (is_file($rutaOrigen)) {
                // Verificar extensión del archivo
                if ($this->extensionPermitida($elemento)) {
                    if (copy($rutaOrigen, $rutaDestino)) {
                        $manifiesto[$rutaRelativa] = [
                            'md5' => md5_file($rutaOrigen),
                            'tamano' => filesize($rutaOrigen),
                            'ruta' => $rutaRelativa
                        ];
                        $archivosCopiados++;
                    } else {
                        $errores[] = $rutaRelativa;
                    }
                }
            }
        }

        return [
            'archivos' => $archivosCopiados,
            'errores' => $errores
        ];
    }

    /**
     * Verificar si una ruta o archivo está excluido
     */
    private function estaExcluido($rutaRelativa, $nombre)
    {
        // Verificar directorios excluidos
        foreach ($this->excluidos as $excluido) {
            if (strpos($rutaRelativa, $excluido) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar si la extensión del archivo está permitida
     */
    private function extensionPermitida($nombreArchivo)
    {
        // Excluir por extensión
        foreach ($this->extensionesExcluidas as $ext) {
            if (substr($nombreArchivo, -strlen($ext)) === $ext) {
                return false;
            }
        }

        // Verificar extensiones permitidas
        $extension = strtolower(strrchr($nombreArchivo, '.'));
        return in_array($extension, $this->extensionesPermitidas);
    }

    /**
     * Eliminar un directorio recursivamente
     */
    private function eliminarDirectorio($directorio)
    {
        if (!is_dir($directorio)) {
            return;
        }

        $archivos = array_diff(scandir($directorio), ['.', '..']);

        foreach ($archivos as $archivo) {
            $ruta = $directorio . '/' . $archivo;
            is_dir($ruta) ? $this->eliminarDirectorio($ruta) : unlink($ruta);
        }

        rmdir($directorio);
    }
}
