<?php
/**
 * Script para crear la tabla de asignaciones de establecimientos
 * Ejecutar una vez: php setup_asignaciones.php
 */

require_once 'config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS asignaciones_establecimientos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        establecimiento_id INT NOT NULL,
        fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (establecimiento_id) REFERENCES establecimientos(id) ON DELETE CASCADE,
        UNIQUE KEY unique_asignacion (usuario_id, establecimiento_id),
        INDEX idx_usuario_id (usuario_id),
        INDEX idx_establecimiento_id (establecimiento_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "Tabla 'asignaciones_establecimientos' creada exitosamente.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
