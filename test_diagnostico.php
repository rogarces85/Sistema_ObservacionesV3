<?php
/**
 * Script de diagnóstico - Verificar conexión y usuarios
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<pre>\n";
echo "=== Diagnóstico de Conexión y Usuarios ===\n\n";

try {
    $db = Database::obtenerInstancia();
    echo "✅ Conexión a BD exitosa\n";
    echo "   Host: " . DB_HOST . "\n";
    echo "   DB: " . DB_NAME . "\n";
    echo "   User: " . DB_USER . "\n\n";

    // Ver usuarios
    $usuarios = $db->consultar("SELECT id, username, nombre_completo, rol, activo, password_reset_required, LENGTH(password_hash) as hash_len FROM usuarios ORDER BY id");
    
    echo "Usuarios encontrados: " . count($usuarios) . "\n\n";
    
    foreach ($usuarios as $u) {
        echo "ID: {$u['id']}\n";
        echo "  Username: {$u['username']}\n";
        echo "  Nombre: {$u['nombre_completo']}\n";
        echo "  Rol: {$u['rol']}\n";
        echo "  Activo: {$u['activo']}\n";
        echo "  Reset Required: {$u['password_reset_required']}\n";
        echo "  Hash Length: {$u['hash_len']}\n";
        
        // Check if password is bcrypt (should be 60 chars) or plain text
        if ($u['hash_len'] == 60) {
            echo "  Password Format: bcrypt ✅\n";
        } else {
            echo "  Password Format: PLAIN TEXT or other (length: {$u['hash_len']}) ⚠️\n";
        }
        echo "\n";
    }

    // Test password verification for first user
    if (count($usuarios) > 0) {
        $primerUsuario = $usuarios[0];
        $sql = "SELECT password_hash FROM usuarios WHERE id = :id LIMIT 1";
        $resultado = $db->consultarUno($sql, ['id' => $primerUsuario['id']]);
        
        echo "\n=== Prueba de Verificación ===\n";
        echo "Usuario: {$primerUsuario['username']}\n";
        echo "Hash: " . substr($resultado['password_hash'], 0, 20) . "...\n";
        
        // Try common passwords
        $passwordsToTest = ['admin123', '123456', 'password', 'estadi2021'];
        foreach ($passwordsToTest as $pwd) {
            $verifica = password_verify($pwd, $resultado['password_hash']);
            echo "  password_verify('{$pwd}'): " . ($verifica ? '✅ MATCH' : '❌ No match') . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n</pre>";
