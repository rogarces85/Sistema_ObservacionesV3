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

    $newHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE username IN (?, ?, ?, ?, ?)");
    $stmt->execute([$newHash, 'supervisor1', 'registrador1', 'registrador2', 'registrador3', 'registrador4']);

    echo "Contraseñas actualizadas en servidor remoto.\n";
    echo "Hash usado: $newHash\n";

    // Verificar
    $usuarios = $pdo->query("SELECT username, password_hash FROM usuarios")->fetchAll();
    foreach ($usuarios as $u) {
        $valid = password_verify('admin123', $u['password_hash']);
        echo "  - {$u['username']}: " . ($valid ? "OK" : "FALLA") . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
