<?php
/**
 * Script para crear la tabla de observaciones eliminadas
 * Ejecutar: php setup_deleted_observations.php
 */

require_once 'config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS observaciones_eliminadas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        observacion_id INT NOT NULL,
        anio INT NOT NULL,
        mes VARCHAR(20) NOT NULL,
        establecimiento_id INT NOT NULL,
        establecimiento_nombre VARCHAR(200) NOT NULL,
        establecimiento_nombre_corto VARCHAR(50) NOT NULL,
        comuna VARCHAR(100) NOT NULL,
        codigo_serie VARCHAR(50) NOT NULL,
        codigo_hoja VARCHAR(50) NOT NULL,
        tipo_error VARCHAR(100) NOT NULL,
        detalle_observacion TEXT NOT NULL,
        plazo_entrega ENUM('dentro_plazo', 'fuera_plazo') NOT NULL,
        usa_validador ENUM('si', 'no') NOT NULL,
        estado_actual VARCHAR(50) NOT NULL,
        clasificacion VARCHAR(200) NULL,
        usuario_registro_id INT NOT NULL,
        nombre_registro VARCHAR(100) NOT NULL,
        usuario_supervisor_id INT NULL,
        motivo_eliminacion TEXT NULL,
        fecha_eliminacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_registro_original TIMESTAMP NULL,
        fecha_revision TIMESTAMP NULL,
        INDEX idx_observacion_id (observacion_id),
        INDEX idx_anio (anio),
        INDEX idx_fecha_eliminacion (fecha_eliminacion),
        INDEX idx_usuario_registro_id (usuario_registro_id),
        INDEX idx_usuario_supervisor_id (usuario_supervisor_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "Tabla 'observaciones_eliminadas' creada exitosamente.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
