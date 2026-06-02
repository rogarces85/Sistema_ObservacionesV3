<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>\n";
echo "=== Test de Conexión Directa ===\n\n";

// Intento directo sin usar clases
$host = '10.8.152.199';
$port = '3306';
$name = 'observaciones_rem';
$user = 'root';
$pass = 'estadi2021';

echo "Intentando conectar a: {$host}:{$port}/{$name} como {$user}\n\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ Conexión exitosa!\n\n";

    // Ver usuarios
    $stmt = $pdo->query("SELECT id, username, nombre_completo, rol, activo, LENGTH(password_hash) as hash_len FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll();
    
    echo "Usuarios: " . count($usuarios) . "\n\n";
    foreach ($usuarios as $u) {
        echo "ID: {$u['id']} | User: {$u['username']} | Rol: {$u['rol']} | Activo: {$u['activo']} | Hash: {$u['hash_len']} chars\n";
    }

} catch (PDOException $e) {
    echo "❌ Error PDO: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
}

echo "\n</pre>";
