<?php
$host = '10.8.152.199';
$port = '3306';
$user = 'root';
$pass = 'estadi2021';
$dbname = 'observaciones_rem';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $cols = $pdo->query("SHOW COLUMNS FROM asignaciones_establecimientos")->fetchAll(PDO::FETCH_COLUMN);
    echo "Columnas en asignaciones_establecimientos: " . implode(', ', $cols) . "\n";

    $count = $pdo->query("SELECT COUNT(*) FROM asignaciones_establecimientos")->fetchColumn();
    echo "Registros actuales: $count\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
