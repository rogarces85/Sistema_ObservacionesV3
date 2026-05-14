<?php
/**
 * Ejecutor de Migraciones - Servidor Remoto 10.8.152.199
 * Verifica y aplica migraciones pendientes en la BD de producción.
 *
 * Uso: php run_migrations.php
 */

$host = '10.8.152.199';
$port = '3306';
$dbname = 'observaciones_rem';
$user = 'root';
$pass = 'estadi2021';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conectado a $host/$dbname\n\n";
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage() . "\n");
}

function tableExists($pdo, $table) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$table'");
    return (int)$stmt->fetchColumn() > 0;
}

function columnExists($pdo, $table, $column) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$table' AND column_name = '$column'");
    return (int)$stmt->fetchColumn() > 0;
}

function indexExists($pdo, $table, $index) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = '$table' AND index_name = '$index'");
    return (int)$stmt->fetchColumn() > 0;
}

function execSQL($pdo, $label, $sql) {
    try {
        $pdo->exec($sql);
        echo "  ✅ $label\n";
        return true;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already') !== false) {
            echo "  ⏭️ $label (ya existe)\n";
            return false;
        }
        echo "  ❌ $label: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "=== Verificando migraciones pendientes ===\n\n";

// --- Sprint 1: Auditoría de Usuarios ---
echo "📦 Sprint 1: Auditoría de Usuarios\n";
if (!tableExists($pdo, 'historial_usuarios')) {
    execSQL($pdo, 'Crear tabla historial_usuarios', "
        CREATE TABLE historial_usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            usuario_responsable_id INT DEFAULT NULL,
            accion VARCHAR(50) NOT NULL COMMENT 'CREACION, ACTUALIZACION, CAMBIO_PASSWORD, ACTIVADO, DESACTIVADO, ELIMINACION',
            detalles TEXT DEFAULT NULL,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_responsable_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} else { echo "  ⏭️ historial_usuarios ya existe\n"; }

if (columnExists($pdo, 'establecimientos', 'nombre_corto')) {
    execSQL($pdo, 'nombre_corto permitir NULL', "ALTER TABLE establecimientos MODIFY COLUMN nombre_corto VARCHAR(100) DEFAULT NULL");
} else { echo "  ⏭️ nombre_corto ya ajustado\n"; }

// --- Sprint 2: Asignaciones Mensuales ---
echo "\n📦 Sprint 2: Asignaciones Mensuales\n";
if (tableExists($pdo, 'asignaciones_establecimientos') && !columnExists($pdo, 'asignaciones_establecimientos', 'meses')) {
    execSQL($pdo, 'Agregar columna meses', "ALTER TABLE asignaciones_establecimientos ADD COLUMN meses VARCHAR(50) DEFAULT 'ALL' COMMENT 'ALL para todo el año, o lista de IDs de meses ej: 1,2,3' AFTER anio");
} else if (!tableExists($pdo, 'asignaciones_establecimientos')) {
    echo "  ⚠️ Tabla asignaciones_establecimientos no existe\n";
} else { echo "  ⏭️ Columna meses ya existe\n"; }

// --- Sprint 3: Papelera de Observaciones Eliminadas ---
echo "\n📦 Sprint 3: Papelera de Observaciones Eliminadas\n";
if (!tableExists($pdo, 'observaciones_eliminadas')) {
    execSQL($pdo, 'Crear tabla observaciones_eliminadas', "
        CREATE TABLE observaciones_eliminadas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            observacion_id INT NOT NULL,
            anio INT NOT NULL,
            mes VARCHAR(20) NOT NULL,
            establecimiento_id INT NOT NULL,
            establecimiento_nombre VARCHAR(200) NOT NULL,
            establecimiento_nombre_corto VARCHAR(50) DEFAULT NULL,
            comuna VARCHAR(100) NOT NULL,
            codigo_serie VARCHAR(50) NOT NULL,
            codigo_hoja VARCHAR(50) NOT NULL,
            tipo_error VARCHAR(100) NOT NULL,
            detalle_observacion TEXT NOT NULL,
            plazo_entrega ENUM('dentro_plazo', 'fuera_plazo') NOT NULL,
            usa_validador ENUM('si', 'no') NOT NULL,
            estado_actual VARCHAR(50) NOT NULL,
            clasificacion VARCHAR(200) DEFAULT NULL,
            usuario_registro_id INT NOT NULL,
            nombre_registro VARCHAR(100) NOT NULL,
            usuario_supervisor_id INT DEFAULT NULL COMMENT 'Supervisor que eliminó',
            motivo_eliminacion TEXT DEFAULT NULL,
            fecha_eliminacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_registro_original TIMESTAMP NULL DEFAULT NULL,
            fecha_revision TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_observacion_id (observacion_id),
            INDEX idx_anio (anio),
            INDEX idx_mes (mes),
            INDEX idx_establecimiento_id (establecimiento_id),
            INDEX idx_comuna (comuna),
            INDEX idx_usuario_registro_id (usuario_registro_id),
            INDEX idx_fecha_eliminacion (fecha_eliminacion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} else { echo "  ⏭️ observaciones_eliminadas ya existe\n"; }

if (tableExists($pdo, 'observaciones_eliminadas') && !indexExists($pdo, 'observaciones_eliminadas', 'idx_filtros_eliminadas')) {
    execSQL($pdo, 'Crear índice idx_filtros_eliminadas', "CREATE INDEX idx_filtros_eliminadas ON observaciones_eliminadas (anio, mes, establecimiento_id)");
} else if (tableExists($pdo, 'observaciones_eliminadas')) { echo "  ⏭️ idx_filtros_eliminadas ya existe\n"; }

// --- Sprint 4: Exportación Asíncrona ---
echo "\n📦 Sprint 4: Exportación Asíncrona\n";
if (!tableExists($pdo, 'reportes_pendientes')) {
    execSQL($pdo, 'Crear tabla reportes_pendientes', "
        CREATE TABLE reportes_pendientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            tipo_reporte VARCHAR(50) NOT NULL,
            formato VARCHAR(10) NOT NULL,
            parametros TEXT DEFAULT NULL COMMENT 'JSON con filtros y configuración',
            estado ENUM('PENDIENTE', 'PROCESANDO', 'LISTO', 'ERROR') DEFAULT 'PENDIENTE',
            archivo_url VARCHAR(255) DEFAULT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_procesamiento TIMESTAMP NULL DEFAULT NULL,
            mensaje_error TEXT DEFAULT NULL,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} else { echo "  ⏭️ reportes_pendientes ya existe\n"; }

// --- Sprint 5: Sistema de Versionado ---
echo "\n📦 Sprint 5: Sistema de Versionado\n";
if (!tableExists($pdo, 'versiones_sistema')) {
    execSQL($pdo, 'Crear tabla versiones_sistema', "
        CREATE TABLE versiones_sistema (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version_tag VARCHAR(50) NOT NULL UNIQUE,
            descripcion TEXT NOT NULL,
            usuario_id INT NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            snapshot_path VARCHAR(255) NOT NULL COMMENT 'Ruta relativa al directorio del snapshot',
            archivos_json JSON DEFAULT NULL COMMENT 'Manifiesto de archivos incluidos (rutas y hashes)',
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} else { echo "  ⏭️ versiones_sistema ya existe\n"; }

// --- Migraciones adicionales del config ---
echo "\n📦 Migraciones adicionales (config/)\n";

// Índices de optimización reportes
$indicesReportes = [
    ['idx_anio_tipo_error', 'observaciones', 'ALTER TABLE observaciones ADD INDEX idx_anio_tipo_error (anio, tipo_error)'],
    ['idx_anio_plazo', 'observaciones', 'ALTER TABLE observaciones ADD INDEX idx_anio_plazo (anio, plazo_entrega)'],
    ['idx_anio_validador', 'observaciones', 'ALTER TABLE observaciones ADD INDEX idx_anio_validador (anio, usa_validador)'],
    ['idx_anio_serie_error', 'observaciones', 'ALTER TABLE observaciones ADD INDEX idx_anio_serie_error (anio, codigo_serie, tipo_error)'],
    ['idx_anio_hoja', 'observaciones', 'ALTER TABLE observaciones ADD INDEX idx_anio_hoja (anio, codigo_hoja)'],
    ['idx_anio_estado', 'observaciones', 'ALTER TABLE observaciones ADD INDEX idx_anio_estado (anio, estado_actual)'],
];
foreach ($indicesReportes as $idx) {
    if (!indexExists($pdo, $idx[1], $idx[0])) {
        execSQL($pdo, "Índice {$idx[0]}", $idx[2]);
    } else { echo "  ⏭️ Índice {$idx[0]} ya existe\n"; }
}

// Tabla asignaciones (si no existe)
if (!tableExists($pdo, 'asignaciones_establecimientos')) {
    execSQL($pdo, 'Crear tabla asignaciones_establecimientos', "
        CREATE TABLE asignaciones_establecimientos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            establecimiento_id INT NOT NULL,
            fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (establecimiento_id) REFERENCES establecimientos(id) ON DELETE CASCADE,
            UNIQUE KEY unique_asignacion (usuario_id, establecimiento_id),
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_establecimiento_id (establecimiento_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} else { echo "  ⏭️ asignaciones_establecimientos ya existe\n"; }

// Verificación final
echo "\n=== Tablas en la base de datos ===\n";
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    echo "  📋 $t\n";
}

echo "\n✅ Migraciones completadas.\n";
