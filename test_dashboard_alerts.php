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

    // Simular métodos del modelo
    $anio = 2026;

    // tieneAsignaciones para registrador 2
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM asignaciones_establecimientos WHERE usuario_id = ? AND anio = ?");
    $stmt->execute([2, $anio]);
    $tiene = $stmt->fetch()['count'] > 0;
    echo "Registrador 2 tiene asignaciones en $anio: " . ($tiene ? 'SI' : 'NO') . "\n";

    // getRegistradoresSinAsignaciones
    $stmt = $pdo->prepare("SELECT u.id, u.username, u.nombre_completo FROM usuarios u WHERE u.rol = 'registrador' AND u.activo = 1 AND u.id NOT IN (SELECT DISTINCT usuario_id FROM asignaciones_establecimientos WHERE anio = ?) ORDER BY u.nombre_completo ASC");
    $stmt->execute([$anio]);
    $sinAsig = $stmt->fetchAll();
    echo "\nRegistradores sin asignaciones en $anio: " . count($sinAsig) . "\n";
    foreach ($sinAsig as $r) {
        echo "  - {$r['nombre_completo']} ({$r['username']})\n";
    }

    // getIdsAsignados para registrador 2
    $stmt = $pdo->prepare("SELECT establecimiento_id FROM asignaciones_establecimientos WHERE usuario_id = ? AND anio = ?");
    $stmt->execute([2, $anio]);
    $ids = array_map(fn($r) => (int)$r['establecimiento_id'], $stmt->fetchAll());
    echo "\nIDs asignados a registrador 2: " . implode(', ', $ids) . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
