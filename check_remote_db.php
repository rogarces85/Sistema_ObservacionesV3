<?php
$host = '10.8.152.199';
$port = '3306';
$user = 'root';
$pass = 'estadi2021';
$dbname = 'observaciones_rem';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ]);

    echo "Conectado a la BD remota.\n";

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas existentes: " . implode(', ', $tables) . "\n\n";

    if (in_array('usuarios', $tables)) {
        $usuarios = $pdo->query("SELECT username, nombre_completo, rol FROM usuarios")->fetchAll();
        echo "Usuarios:\n";
        foreach ($usuarios as $u) {
            echo "  - {$u['username']} ({$u['nombre_completo']}) [{$u['rol']}]\n";
        }
    } else {
        echo "No existe tabla usuarios.\n";
    }

    if (in_array('establecimientos', $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM establecimientos")->fetchColumn();
        echo "\nEstablecimientos: $count registros.\n";
    }

    if (in_array('comunas', $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM comunas")->fetchColumn();
        echo "Comunas: $count registros.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
