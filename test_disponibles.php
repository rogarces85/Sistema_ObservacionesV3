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

    // Simular getEstablecimientosDisponibles para registrador 2, anio 2026
    $registradorId = 2;
    $anio = 2026;

    $sql = "SELECT e.*, c.nombre as comuna_nombre,
                   CASE WHEN ae.usuario_id IS NOT NULL THEN 1 ELSE 0 END as asignado_a_mi
            FROM establecimientos e
            INNER JOIN comunas c ON e.comuna_id = c.id
            LEFT JOIN asignaciones_establecimientos ae 
                   ON e.id = ae.establecimiento_id AND ae.anio = ? AND ae.usuario_id = ?
            WHERE e.activo = 1
              AND (
                  e.id NOT IN (
                      SELECT establecimiento_id 
                      FROM asignaciones_establecimientos 
                      WHERE anio = ?
                  )
                  OR ae.usuario_id IS NOT NULL
              )
            ORDER BY c.nombre ASC, e.nombre ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$anio, $registradorId, $anio]);
    $rows = $stmt->fetchAll();
    echo "Total disponibles para registrador $registradorId en $anio: " . count($rows) . "\n\n";

    $asignadosAMi = array_filter($rows, fn($r) => $r['asignado_a_mi'] == 1);
    $libres = array_filter($rows, fn($r) => $r['asignado_a_mi'] == 0);

    echo "Asignados a mi: " . count($asignadosAMi) . "\n";
    echo "Libres: " . count($libres) . "\n\n";

    // Verificar que 123202 no aparezca
    $encontrado = array_filter($rows, fn($r) => $r['codigo_establecimiento'] == 123202);
    echo "123202 en lista: " . (count($encontrado) > 0 ? "SI (ERROR)" : "NO (OK)") . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
