<?php
require_once 'config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Agregar columna anio
    $columnCheck = $pdo->query("SHOW COLUMNS FROM asignaciones_establecimientos LIKE 'anio'")->fetchAll();
    if (empty($columnCheck)) {
        $pdo->exec("ALTER TABLE asignaciones_establecimientos ADD COLUMN anio INT NOT NULL DEFAULT 2026 AFTER usuario_id");
        echo "Columna 'anio' agregada.\n";
    }

    // 2. Actualizar registros existentes con año actual
    $pdo->exec("UPDATE asignaciones_establecimientos SET anio = 2026 WHERE anio = 0 OR anio IS NULL");

    // 3. Recrear unique key con anio
    $pdo->exec("ALTER TABLE asignaciones_establecimientos DROP INDEX unique_asignacion");
    $pdo->exec("ALTER TABLE asignaciones_establecimientos ADD UNIQUE KEY unique_asignacion (usuario_id, establecimiento_id, anio)");
    $pdo->exec("ALTER TABLE asignaciones_establecimientos ADD INDEX idx_anio (anio)");
    echo "Unique key actualizado con anio.\n";

    // 4. Crear tabla referentes
    $pdo->exec("CREATE TABLE IF NOT EXISTS referentes_establecimientos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        establecimiento_id INT NOT NULL,
        cargo VARCHAR(100) NOT NULL,
        nombre VARCHAR(200) NOT NULL,
        telefono VARCHAR(50) NULL,
        email VARCHAR(200) NULL,
        activo BOOLEAN NOT NULL DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (establecimiento_id) REFERENCES establecimientos(id) ON DELETE CASCADE,
        INDEX idx_establecimiento_id (establecimiento_id),
        INDEX idx_cargo (cargo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Tabla 'referentes_establecimientos' creada.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
