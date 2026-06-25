<?php
/**
 * deploy/create-first-supervisor.php
 * Crea el primer usuario supervisor del sistema. Pensado para
 * ejecutarse una sola vez via CLI, sin UI.
 *
 * Uso:
 *   sudo -u www-data php deploy/create-first-supervisor.php \
 *       --username=admin --nombre="Administrador General" \
 *       --email=admin@rem.example.cl
 *
 * La contrasena se imprime en pantalla una sola vez. Comunicarla
 * por canal seguro al usuario y pedir cambio en primer login
 * (deuda tecnica documentada: politica de primer-login).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("Solo CLI\n");
}

$opts = getopt('', ['username:', 'nombre:', 'email::']);
$username = $opts['username'] ?? null;
$nombre   = $opts['nombre']   ?? null;
$email    = $opts['email']    ?? null;

if (!$username || !$nombre) {
    fwrite(STDERR, "Uso: php create-first-supervisor.php --username=<u> --nombre=<n> [--email=<e>]\n");
    exit(1);
}

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../models/User.php';

if (strtoupper(getenv('REM_ENVIRONMENT') ?: 'production') === 'PRODUCTION'
    && is_file('/etc/rem/env.php')) {
    require '/etc/rem/env.php';
}

$userModel = new User();
if ($userModel->usernameExists($username)) {
    fwrite(STDERR, "ERROR: el usuario '$username' ya existe\n");
    exit(1);
}

$password = bin2hex(random_bytes(8)) . '!Aa1';
$id = $userModel->create($username, $password, $nombre, ROL_SUPERVISOR, $email);

if (!$id) {
    fwrite(STDERR, "ERROR: no se pudo crear el usuario\n");
    exit(1);
}

if ($email) {
    $audit = new UserAudit();
    $audit->logAction($id, 'CREACION', 'Usuario inicial creado via CLI');
}

echo "Usuario supervisor creado:\n";
echo "  ID: $id\n";
echo "  Username: $username\n";
echo "  Nombre: $nombre\n";
echo "  Email: " . ($email ?: '-') . "\n";
echo "  Contrasena temporal: $password\n";
echo "\nIMPORTANTE: comunique esta contrasena por canal seguro y pida cambio inmediato.\n";
