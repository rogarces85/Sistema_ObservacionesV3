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

    // Simular getEstablecimientosConAsignacion para registrador 2, anio 2026
    $registradorId = 2;
    $anio = 2026;

    $sql = "SELECT e.*, c.nombre as comuna_nombre,
                   CASE WHEN ae_mi.usuario_id IS NOT NULL THEN 1 ELSE 0 END as asignado_a_mi,
                   ae_otro.usuario_id as asignado_a_usuario_id,
                   u.nombre_completo as asignado_a_nombre
            FROM establecimientos e
            INNER JOIN comunas c ON e.comuna_id = c.id
            LEFT JOIN asignaciones_establecimientos ae_mi
                   ON e.id = ae_mi.establecimiento_id AND ae_mi.anio = ? AND ae_mi.usuario_id = ?
            LEFT JOIN asignaciones_establecimientos ae_otro
                   ON e.id = ae_otro.establecimiento_id AND ae_otro.anio = ? AND ae_otro.usuario_id != ?
            LEFT JOIN usuarios u ON ae_otro.usuario_id = u.id
            WHERE e.activo = 1
            ORDER BY c.nombre ASC, e.nombre ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$anio, $registradorId, $anio, $registradorId]);
    $rows = $stmt->fetchAll();

    echo "Total establecimientos: " . count($rows) . "\n\n";

    $libres = array_filter($rows, fn($r) => $r['asignado_a_mi'] == 0 && empty($r['asignado_a_usuario_id']));
    $aMi = array_filter($rows, fn($r) => $r['asignado_a_mi'] == 1);
    $aOtros = array_filter($rows, fn($r) => $r['asignado_a_mi'] == 0 && !empty($r['asignado_a_usuario_id']));

    echo "Libres: " . count($libres) . "\n";
    echo "Asignados a mi (registrador $registradorId): " . count($aMi) . "\n";
    echo "Asignados a otros: " . count($aOtros) . "\n\n";

    // Mostrar algunos de cada tipo
    echo "--- Muestra asignados a otros ---\n";
    foreach (array_slice($aOtros, 0, 5) as $r) {
        echo "  {$r['codigo_establecimiento']} {$r['nombre']} -> {$r['asignado_a_nombre']}\n";
    }

    echo "\n--- Muestra libres ---\n";
    foreach (array_slice($libres, 0, 5) as $r) {
        echo "  {$r['codigo_establecimiento']} {$r['nombre']} -> LIBRE\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
