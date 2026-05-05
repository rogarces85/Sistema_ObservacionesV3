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

    echo "=== Validacion de muestra ===\n";
    $codigos = [123304, 123307, 123309, 123311, 123422, 200455, 200748, 201667, 202043];
    $placeholders = implode(',', array_fill(0, count($codigos), '?'));
    $stmt = $pdo->prepare("SELECT e.codigo_establecimiento, e.nombre, e.comuna_id, c.codigo_comuna, c.nombre as comuna_nombre FROM establecimientos e JOIN comunas c ON e.comuna_id = c.id WHERE e.codigo_establecimiento IN ($placeholders)");
    $stmt->execute($codigos);
    foreach ($stmt->fetchAll() as $r) {
        echo "  {$r['codigo_establecimiento']} | {$r['nombre']} | {$r['comuna_nombre']} ({$r['codigo_comuna']})\n";
    }

    echo "\n=== Resumen por comuna ===\n";
    $resumen = $pdo->query("SELECT c.nombre, COUNT(*) as total FROM establecimientos e JOIN comunas c ON e.comuna_id = c.id WHERE e.activo = 1 GROUP BY c.nombre ORDER BY total DESC")->fetchAll();
    foreach ($resumen as $r) {
        echo "  {$r['nombre']}: {$r['total']}\n";
    }

    echo "\n=== Establecimientos inactivos ===\n";
    $inactivos = $pdo->query("SELECT codigo_establecimiento, nombre FROM establecimientos WHERE activo = 0 LIMIT 10")->fetchAll();
    foreach ($inactivos as $i) {
        echo "  {$i['codigo_establecimiento']} | {$i['nombre']}\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
