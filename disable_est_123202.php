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

    // Desactivar establecimiento 123202
    $stmt = $pdo->prepare("UPDATE establecimientos SET activo = 0 WHERE codigo_establecimiento = ?");
    $stmt->execute([123202]);
    echo "Filas afectadas (desactivar 123202): " . $stmt->rowCount() . "\n";

    // Verificar
    $est = $pdo->query("SELECT codigo_establecimiento, nombre, activo FROM establecimientos WHERE codigo_establecimiento = 123202")->fetch();
    if ($est) {
        echo "Establecimiento {$est['codigo_establecimiento']} - {$est['nombre']} -> activo={$est['activo']}\n";
    } else {
        echo "No encontrado.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
