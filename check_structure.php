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
    ]);

    echo "=== Estructura tabla comunas ===\n";
    $cols = $pdo->query("DESCRIBE comunas")->fetchAll();
    foreach ($cols as $c) {
        echo "  {$c['Field']} {$c['Type']} {$c['Null']} {$c['Key']} {$c['Default']}\n";
    }

    echo "\n=== Estructura tabla establecimientos ===\n";
    $cols = $pdo->query("DESCRIBE establecimientos")->fetchAll();
    foreach ($cols as $c) {
        echo "  {$c['Field']} {$c['Type']} {$c['Null']} {$c['Key']} {$c['Default']}\n";
    }

    echo "\n=== Comunas actuales ===\n";
    $comunas = $pdo->query("SELECT * FROM comunas")->fetchAll();
    foreach ($comunas as $co) {
        echo "  {$co['id']} | {$co['codigo_comuna']} | {$co['nombre']}\n";
    }

    echo "\n=== Establecimientos actuales (primeros 20) ===\n";
    $ests = $pdo->query("SELECT id, codigo_establecimiento, nombre, comuna_id FROM establecimientos LIMIT 20")->fetchAll();
    foreach ($ests as $e) {
        echo "  {$e['id']} | {$e['codigo_establecimiento']} | {$e['nombre']} | comuna_id={$e['comuna_id']}\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
