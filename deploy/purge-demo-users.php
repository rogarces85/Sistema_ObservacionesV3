<?php
/**
 * deploy/purge-demo-users.php
 * Elimina o desactiva usuarios demo de la base de datos.
 * Por defecto deja solo el usuario pasado por --keep; el resto
 * queda desactivado (activo=0). Con --hard los elimina.
 *
 * Uso:
 *   sudo -u www-data php deploy/purge-demo-users.php --keep=admin
 *   sudo -u www-data php deploy/purge-demo-users.php --keep=admin --hard
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("Solo CLI\n");
}

$opts = getopt('', ['keep:', 'hard']);
$keep = $opts['keep'] ?? 'admin';
$hard = isset($opts['hard']);

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../models/Database.php';
$db = Database::getInstance()->getConnection();

$sql = "SELECT id, username, rol, activo FROM usuarios WHERE username <> ?";
$stmt = $db->prepare($sql);
$stmt->execute([$keep]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "Nada que limpiar (solo existe '$keep').\n";
    exit(0);
}

$audit = new UserAudit();
$actor = get_current_user() ?: 'cli';

if ($hard) {
    $ids = array_column($rows, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $del = $db->prepare("DELETE FROM usuarios WHERE id IN ($placeholders)");
    $del->execute($ids);
    echo "Usuarios eliminados: " . count($ids) . "\n";
    foreach ($rows as $r) {
        $audit->logAction($r['id'], 'ELIMINACION_CLI', "Demo user eliminado por CLI keep=$keep");
        echo "  - {$r['username']} (id={$r['id']}, rol={$r['rol']})\n";
    }
} else {
    $upd = $db->prepare("UPDATE usuarios SET activo = 0, fecha_actualizacion = NOW() WHERE id = ?");
    foreach ($rows as $r) {
        $upd->execute([$r['id']]);
        $audit->logAction($r['id'], 'DESACTIVADO_CLI', "Demo user desactivado por CLI keep=$keep");
        echo "  ~ {$r['username']} desactivado (id={$r['id']}, rol={$r['rol']})\n";
    }
    echo "Total desactivados: " . count($rows) . "\n";
    echo "Ejecuta con --hard para eliminarlos definitivamente.\n";
}
