<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>\n";
echo "=== Test de Login Directo ===\n\n";

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';

echo "Sesión iniciada: " . (isset($_SESSION['usuario_id']) ? 'Sí' : 'No') . "\n";
echo "Session ID: " . session_id() . "\n\n";

// Test login directo
try {
    $db = Database::obtenerInstancia();
    
    // Probar con supervisor1
    $username = 'supervisor1';
    $password = 'admin123';
    
    echo "Probando login: {$username} / {$password}\n\n";
    
    $sql = "SELECT id, username, password_hash, nombre_completo, rol, activo, password_reset_required
            FROM usuarios WHERE username = :username LIMIT 1";
    $usuario = $db->consultarUno($sql, ['username' => $username]);
    
    if (!$usuario) {
        echo "❌ Usuario no encontrado\n";
    } else {
        echo "✅ Usuario encontrado:\n";
        echo "   ID: {$usuario['id']}\n";
        echo "   Username: {$usuario['username']}\n";
        echo "   Nombre: {$usuario['nombre_completo']}\n";
        echo "   Rol: {$usuario['rol']}\n";
        echo "   Activo: {$usuario['activo']}\n";
        echo "   Hash: " . substr($usuario['password_hash'], 0, 30) . "...\n";
        
        $verifica = password_verify($password, $usuario['password_hash']);
        echo "\n   password_verify('{$password}'): " . ($verifica ? '✅ VÁLIDO' : '❌ INVÁLIDO') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}

echo "\n</pre>";
