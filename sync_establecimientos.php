<?php
/**
 * Script para sincronizar establecimientos y comunas en la BD remota
 */

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

    // Datos exactos proporcionados por el usuario
    $datos = [
        ['idestablec' => 123010, 'nombre' => 'Dirección Servicio Salud Osorno', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123011, 'nombre' => 'PRAIS', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123012, 'nombre' => 'Clínica Dental Móvil (Osorno)', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123030, 'nombre' => 'Departamento de Atención Integral Funcionarios', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123100, 'nombre' => 'Hospital Base San José de Osorno', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123101, 'nombre' => 'Hospital de Purranque Dr. Juan Hepp Dubiau', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123102, 'nombre' => 'Hospital de Río Negro', 'id_comuna' => 10305, 'nombre_comuna' => 'RIO NEGRO'],
        ['idestablec' => 123103, 'nombre' => 'Hospital de Puerto Octay', 'id_comuna' => 10302, 'nombre_comuna' => 'PUERTO OCTAY'],
        ['idestablec' => 123104, 'nombre' => 'Hospital Futa Sruka Lawenche Kunko Mapu Mo', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
        ['idestablec' => 123105, 'nombre' => 'Hospital Pu Mulen Quilacahuín', 'id_comuna' => 10307, 'nombre_comuna' => 'SAN PABLO'],
        ['idestablec' => 123201, 'nombre' => 'Hospital Misión San Juan de la Costa', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
        ['idestablec' => 123202, 'nombre' => 'Hospital Del Perpetuo Socorro de Quilacahuín (San Pablo)', 'id_comuna' => 10307, 'nombre_comuna' => 'SAN PABLO'],
        ['idestablec' => 123203, 'nombre' => 'Clinica Alemana Osorno', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123207, 'nombre' => 'Centro de Rehabilitación de Minusválidos', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123300, 'nombre' => 'Centro de Salud Familiar Dr. Pedro Jáuregui', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123301, 'nombre' => 'Centro de Salud Familiar Dr. Marcelo Lopetegui Adams', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123302, 'nombre' => 'Centro de Salud Familiar Ovejería', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123303, 'nombre' => 'Centro de Salud Familiar Rahue Alto', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123304, 'nombre' => 'Centro de Salud Familiar Entre Lagos', 'id_comuna' => 10304, 'nombre_comuna' => 'PUYEHUE'],
        ['idestablec' => 123305, 'nombre' => 'Centro de Salud Familiar San Pablo', 'id_comuna' => 10307, 'nombre_comuna' => 'SAN PABLO'],
        ['idestablec' => 123306, 'nombre' => 'Centro de Salud Familiar Pampa Alegre', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123307, 'nombre' => 'Centro de Salud Familiar Purranque', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123309, 'nombre' => 'Centro de Salud Familiar Practicante Pablo Araya', 'id_comuna' => 10305, 'nombre_comuna' => 'RIO NEGRO'],
        ['idestablec' => 123310, 'nombre' => 'Centro de Salud Familiar Quinto Centenario', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123311, 'nombre' => 'Centro de Salud Familiar Bahía Mansa', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
        ['idestablec' => 123312, 'nombre' => 'Centro de Salud Familiar Puaucho', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
        ['idestablec' => 123402, 'nombre' => 'Posta de Salud Rural Cuinco', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
        ['idestablec' => 123404, 'nombre' => 'Posta de Salud Rural Pichi Damas', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123406, 'nombre' => 'Posta de Salud Rural Puyehue', 'id_comuna' => 10304, 'nombre_comuna' => 'PUYEHUE'],
        ['idestablec' => 123407, 'nombre' => 'Posta de Salud Rural Desagüe Rupanco', 'id_comuna' => 10304, 'nombre_comuna' => 'PUYEHUE'],
        ['idestablec' => 123408, 'nombre' => 'Posta de Salud Rural Ñadi Pichi-Damas', 'id_comuna' => 10304, 'nombre_comuna' => 'PUYEHUE'],
        ['idestablec' => 123410, 'nombre' => 'Posta de Salud Rural Tres Esteros', 'id_comuna' => 10305, 'nombre_comuna' => 'RIO NEGRO'],
        ['idestablec' => 123411, 'nombre' => 'Centro Comunitario de Salud Familiar Corte Alto', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123412, 'nombre' => 'Posta de Salud Rural Crucero ( Purranque)', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123413, 'nombre' => 'Posta de Salud Rural Coligual', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123414, 'nombre' => 'Posta de Salud Rural Hueyusca', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123415, 'nombre' => 'Posta de Salud Rural Concordia', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123416, 'nombre' => 'Posta de Salud Rural Colonia Ponce', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123417, 'nombre' => 'Posta de Salud Rural La Naranja', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123419, 'nombre' => 'Posta de Salud Rural San Pedro de Purranque', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123420, 'nombre' => 'Posta de Salud Rural Collihuinco', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 123422, 'nombre' => 'Posta de Salud Rural Rupanco', 'id_comuna' => 10302, 'nombre_comuna' => 'PUERTO OCTAY'],
        ['idestablec' => 123423, 'nombre' => 'Posta de Salud Rural Cascadas', 'id_comuna' => 10302, 'nombre_comuna' => 'PUERTO OCTAY'],
        ['idestablec' => 123424, 'nombre' => 'Posta de Salud Rural Piedras Negras', 'id_comuna' => 10302, 'nombre_comuna' => 'PUERTO OCTAY'],
        ['idestablec' => 123425, 'nombre' => 'Posta de Salud Rural Cancura', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123426, 'nombre' => 'Posta de Salud Rural Pellinada', 'id_comuna' => 10302, 'nombre_comuna' => 'PUERTO OCTAY'],
        ['idestablec' => 123427, 'nombre' => 'Posta de Salud Rural La Calo', 'id_comuna' => 10302, 'nombre_comuna' => 'PUERTO OCTAY'],
        ['idestablec' => 123428, 'nombre' => 'Posta de Salud Rural Coihueco (Puerto Octay)', 'id_comuna' => 10302, 'nombre_comuna' => 'PUERTO OCTAY'],
        ['idestablec' => 123430, 'nombre' => 'Posta de Salud Rural Purrehuín', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
        ['idestablec' => 123431, 'nombre' => 'Posta de Salud Rural Aleucapi', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
        ['idestablec' => 123432, 'nombre' => 'Posta de Salud Rural La Poza', 'id_comuna' => 10307, 'nombre_comuna' => 'SAN PABLO'],
        ['idestablec' => 123434, 'nombre' => 'Posta de Salud Rural Huilma', 'id_comuna' => 10305, 'nombre_comuna' => 'RIO NEGRO'],
        ['idestablec' => 123435, 'nombre' => 'Posta de Salud Rural Pucopio', 'id_comuna' => 10307, 'nombre_comuna' => 'SAN PABLO'],
        ['idestablec' => 123436, 'nombre' => 'Posta de Salud Rural Chanco ( San Pablo )', 'id_comuna' => 10307, 'nombre_comuna' => 'SAN PABLO'],
        ['idestablec' => 123437, 'nombre' => 'Posta de Salud Rural Currimáhuida', 'id_comuna' => 10307, 'nombre_comuna' => 'SAN PABLO'],
        ['idestablec' => 123700, 'nombre' => 'Centro Comunitario de Salud Familiar Murrinumo', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123701, 'nombre' => 'Centro Comunitario de Salud Familiar Manuel Rodríguez', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123705, 'nombre' => 'Centro Comunitario de Salud Familiar El Encanto', 'id_comuna' => 10304, 'nombre_comuna' => 'PUYEHUE'],
        ['idestablec' => 123709, 'nombre' => 'Centro Comunitario de Salud Familiar Riachuelo', 'id_comuna' => 10305, 'nombre_comuna' => 'RIO NEGRO'],
        ['idestablec' => 123800, 'nombre' => 'SAPU Dr. Pedro Jáuregui', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 123801, 'nombre' => 'SAPU Rahue Alto', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 200085, 'nombre' => 'SAPU Dr. Marcelo Lopetegui Adams', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 200209, 'nombre' => 'COSAM Rahue', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 200248, 'nombre' => 'CDR de Adultos Mayores con Demencia', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 200445, 'nombre' => 'COSAM Oriente', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 200455, 'nombre' => 'Centro Comunitario de Salud Familiar Barrio Estación', 'id_comuna' => 10303, 'nombre_comuna' => 'PURRANQUE'],
        ['idestablec' => 200477, 'nombre' => 'Unidad de Memoria AYEKAN', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 200490, 'nombre' => 'Posta de Salud Rural Chamilco', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
        ['idestablec' => 200539, 'nombre' => 'Centro Referencia Diagnóstico Médico Osorno', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 200556, 'nombre' => 'Hospital Digital', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 200747, 'nombre' => 'SAPU Entre Lagos', 'id_comuna' => 10304, 'nombre_comuna' => 'PUYEHUE'],
        ['idestablec' => 200748, 'nombre' => 'SUR San Pablo', 'id_comuna' => 10307, 'nombre_comuna' => 'SAN PABLO'],
        ['idestablec' => 200749, 'nombre' => 'SUR Bahía Mansa', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
        ['idestablec' => 200750, 'nombre' => 'SUR Puaucho', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
        ['idestablec' => 201055, 'nombre' => 'Terapéutica Peulla Ambulatoria', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 201056, 'nombre' => 'Terapéutica Peulla Residencial', 'id_comuna' => 10301, 'nombre_comuna' => 'OSORNO'],
        ['idestablec' => 201483, 'nombre' => 'Centro Comunitario de Salud Familiar Las Cascadas', 'id_comuna' => 10302, 'nombre_comuna' => 'PUERTO OCTAY'],
        ['idestablec' => 201667, 'nombre' => 'Posta de Salud Rural Chan Chan Río Negro', 'id_comuna' => 10305, 'nombre_comuna' => 'RIO NEGRO'],
        ['idestablec' => 202043, 'nombre' => 'Posta de Salud Rural Pucatrihue', 'id_comuna' => 10306, 'nombre_comuna' => 'SAN JUAN DE LA COSTA'],
    ];

    // 1. Sincronizar comunas
    $comunasUnicas = [];
    foreach ($datos as $d) {
        $comunasUnicas[$d['id_comuna']] = $d['nombre_comuna'];
    }

    $stmtInsertComuna = $pdo->prepare("INSERT INTO comunas (codigo_comuna, nombre) VALUES (?, ?) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)");
    foreach ($comunasUnicas as $codigo => $nombre) {
        $stmtInsertComuna->execute([$codigo, $nombre]);
    }
    echo "Comunas sincronizadas.\n";

    // 2. Obtener mapeo codigo_comuna -> id interno
    $mapaComunas = [];
    $rows = $pdo->query("SELECT id, codigo_comuna FROM comunas")->fetchAll();
    foreach ($rows as $r) {
        $mapaComunas[$r['codigo_comuna']] = $r['id'];
    }

    // 3. Sincronizar establecimientos
    $stmtInsertEst = $pdo->prepare("INSERT INTO establecimientos (codigo_establecimiento, nombre, nombre_corto, comuna_id, activo) VALUES (?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), nombre_corto = VALUES(nombre_corto), comuna_id = VALUES(comuna_id), activo = 1");

    $insertados = 0;
    $actualizados = 0;
    foreach ($datos as $d) {
        $codigo = $d['idestablec'];
        $nombre = $d['nombre'];
        $codigoComuna = $d['id_comuna'];
        $comunaId = $mapaComunas[$codigoComuna] ?? null;

        if (!$comunaId) {
            echo "AVISO: No se encontró comuna para código $codigoComuna. Saltando $codigo.\n";
            continue;
        }

        $nombreCorto = mb_substr($nombre, 0, 50);
        $stmtInsertEst->execute([$codigo, $nombre, $nombreCorto, $comunaId]);

        if ($stmtInsertEst->rowCount() > 0) {
            // No podemos distinguir insert vs update fácilmente con rowCount en MySQL ON DUPLICATE KEY
            // Simplemente contamos operaciones exitosas
            $actualizados++;
        }
    }

    echo "Procesados $actualizados establecimientos.\n";

    // 4. (Opcional) Desactivar establecimientos que NO están en la lista
    $codigosEnLista = array_map(function($d){ return $d['idestablec']; }, $datos);
    $placeholders = implode(',', array_fill(0, count($codigosEnLista), '?'));
    $stmtDesactivar = $pdo->prepare("UPDATE establecimientos SET activo = 0 WHERE codigo_establecimiento NOT IN ($placeholders)");
    $stmtDesactivar->execute($codigosEnLista);
    $desactivados = $stmtDesactivar->rowCount();
    echo "Desactivados $desactivados establecimientos que no están en la lista oficial.\n";

    // 5. Resumen final
    $total = $pdo->query("SELECT COUNT(*) FROM establecimientos WHERE activo = 1")->fetchColumn();
    echo "Total establecimientos activos: $total\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
