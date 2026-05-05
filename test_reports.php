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

    // Simular reportes para supervisor (sin filtro de usuario)
    $year = 2026;

    echo "=== Reporte por Mes ===\n";
    $stmt = $pdo->prepare("SELECT o.mes, COUNT(*) as total FROM observaciones o WHERE o.anio = ? GROUP BY o.mes ORDER BY FIELD(o.mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre')");
    $stmt->execute([$year]);
    foreach ($stmt->fetchAll() as $r) echo "  {$r['mes']}: {$r['total']}\n";

    echo "\n=== Reporte por Comuna ===\n";
    $stmt = $pdo->prepare("SELECT c.nombre, COUNT(*) as total FROM observaciones o INNER JOIN establecimientos e ON o.establecimiento_id = e.id INNER JOIN comunas c ON e.comuna_id = c.id WHERE o.anio = ? GROUP BY c.nombre ORDER BY total DESC");
    $stmt->execute([$year]);
    foreach ($stmt->fetchAll() as $r) echo "  {$r['nombre']}: {$r['total']}\n";

    echo "\n=== Reporte por Plazo ===\n";
    $stmt = $pdo->prepare("SELECT o.plazo_entrega, COUNT(*) as total FROM observaciones o WHERE o.anio = ? AND o.plazo_entrega != '' GROUP BY o.plazo_entrega");
    $stmt->execute([$year]);
    foreach ($stmt->fetchAll() as $r) echo "  {$r['plazo_entrega']}: {$r['total']}\n";

    echo "\n=== Reporte por Validador ===\n";
    $stmt = $pdo->prepare("SELECT o.usa_validador, COUNT(*) as total FROM observaciones o WHERE o.anio = ? AND o.usa_validador != '' GROUP BY o.usa_validador");
    $stmt->execute([$year]);
    foreach ($stmt->fetchAll() as $r) echo "  {$r['usa_validador']}: {$r['total']}\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
