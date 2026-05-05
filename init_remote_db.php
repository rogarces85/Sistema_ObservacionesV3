<?php
/**
 * Script para inicializar la base de datos remota vía PHP PDO
 */

$host = '10.8.152.199';
$port = '3306';
$user = 'root';
$pass = 'estadi2021';
$dbname = 'observaciones_rem';
$charset = 'utf8mb4';

try {
    // Conectar sin base de datos para crearla
    $pdo = new PDO("mysql:host=$host;port=$port;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Conectado al servidor MySQL remoto.\n";

    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base de datos '$dbname' creada o ya existia.\n";

    // Seleccionar la base de datos
    $pdo->exec("USE $dbname");

    // Leer y ejecutar el script SQL
    $sql = file_get_contents(__DIR__ . '/config/init_db.sql');

    // Dividir en sentencias individuales
    $sentencias = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($sentencias as $sentencia) {
        $sentenciaLimpia = trim($sentencia);
        if (empty($sentenciaLimpia) || stripos($sentenciaLimpia, 'CREATE DATABASE') === 0 || stripos($sentenciaLimpia, 'USE ') === 0) {
            continue; // Saltar CREATE DATABASE y USE
        }
        try {
            $pdo->exec($sentenciaLimpia);
        } catch (PDOException $e) {
            echo "Aviso: " . $e->getMessage() . "\n";
        }
    }

    echo "\nBase de datos inicializada correctamente en el servidor remoto.\n";

    // Verificar tablas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas creadas: " . implode(', ', $tables) . "\n";

    // Verificar usuarios
    $usuarios = $pdo->query("SELECT username, nombre_completo, rol FROM usuarios")->fetchAll();
    echo "\nUsuarios de prueba:\n";
    foreach ($usuarios as $u) {
        echo "  - {$u['username']} ({$u['nombre_completo']}) [{$u['rol']}]\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
