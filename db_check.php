<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $tablesStmt = $pdo->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    $report = ['tables' => $tables];

    if (in_array('registros_rem', $tables)) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM registros_rem");
        $report['registros_rem_count'] = $countStmt->fetchColumn();

        $recentStmt = $pdo->query("SELECT * FROM registros_rem ORDER BY id DESC LIMIT 5");
        $report['registros_rem_recent'] = $recentStmt->fetchAll();
    }

    if (in_array('observaciones', $tables)) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM observaciones");
        $report['observaciones_count'] = $countStmt->fetchColumn();

        $recentStmt = $pdo->query("SELECT * FROM observaciones ORDER BY id DESC LIMIT 5");
        $report['observaciones_recent'] = $recentStmt->fetchAll();
    }
    
    if (in_array('archivos_cargados', $tables)) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM archivos_cargados");
        $report['archivos_cargados_count'] = $countStmt->fetchColumn();

        $recentStmt = $pdo->query("SELECT * FROM archivos_cargados ORDER BY id DESC LIMIT 5");
        $report['archivos_cargados_recent'] = $recentStmt->fetchAll();
    }

    if (in_array('establecimientos', $tables)) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM establecimientos");
        $report['establecimientos_count'] = $countStmt->fetchColumn();
    }

    if (in_array('logs', $tables)) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM logs");
        $report['logs_count'] = $countStmt->fetchColumn();

        $recentStmt = $pdo->query("SELECT * FROM logs ORDER BY id DESC LIMIT 5");
        $report['logs_recent'] = $recentStmt->fetchAll();
    }

    if (in_array('historial_estados', $tables)) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM historial_estados");
        $report['historial_estados_count'] = $countStmt->fetchColumn();

        $recentStmt = $pdo->query("SELECT * FROM historial_estados ORDER BY id DESC LIMIT 5");
        $report['historial_estados_recent'] = $recentStmt->fetchAll();
    }

    echo json_encode(['status' => 'success', 'data' => $report], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
