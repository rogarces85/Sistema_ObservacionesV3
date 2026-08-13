<?php
/**
 * Script de Prueba: API de Asignaciones
 * Verifica los endpoints de la API con tipo_asignacion
 */

require_once __DIR__ . '/config/config.php';

echo "========================================\n";
echo "PRUEBA: API de Asignaciones\n";
echo "========================================\n\n";

// Simular sesión de supervisor
$_SESSION['logged_in'] = true;
$_SESSION['user_id'] = 1;
$_SESSION['rol'] = 'supervisor';
$_SESSION['year'] = 2026;

// Helper para hacer requests
function apiRequest($action, $data = null) {
    global $apiCalled;
    $apiCalled = true;
    
    $_SERVER['REQUEST_METHOD'] = $data ? 'POST' : 'GET';
    $_GET['action'] = $action;
    
    if ($data) {
        $_POST = $data;
        file_put_contents('php://input', json_encode($data));
    }
    
    ob_start();
    try {
        include __DIR__ . '/api/assignments.php';
        $output = ob_get_clean();
        return json_decode($output, true);
    } catch (Exception $e) {
        ob_end_clean();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Limpiar datos
echo "1. Limpiando datos de prueba...\n";
require_once __DIR__ . '/models/EstablecimientoAsignacion.php';
$asigModel = new EstablecimientoAsignacion();
$asigModel->removerTodas(2, 2026);
$asigModel->removerTodas(3, 2026);
echo "   ✅ Datos limpiados\n\n";

// Prueba 1: Crear asignación anual via API
echo "2. Probando endpoint: asignar_multiple (ANUAL)...\n";
$response = apiRequest('asignar_multiple', [
    'action' => 'asignar_multiple',
    'usuario_id' => 2,
    'establecimiento_ids' => [1, 2],
    'anio' => 2026,
    'meses' => 'ALL',
    'tipo_asignacion' => 'anual'
]);

if ($response['success']) {
    echo "   ✅ API respondió exitosamente: {$response['message']}\n";
} else {
    echo "   ❌ Error API: {$response['message']}\n";
    exit(1);
}
echo "\n";

// Prueba 2: Crear reasignación temporal via API
echo "3. Probando endpoint: asignar_multiple (TEMPORAL)...\n";
$response = apiRequest('asignar_multiple', [
    'action' => 'asignar_multiple',
    'usuario_id' => 3,
    'establecimiento_ids' => [1],
    'anio' => 2026,
    'meses' => '1,2,3',
    'tipo_asignacion' => 'temporal'
]);

if ($response['success']) {
    echo "   ✅ API respondió exitosamente: {$response['message']}\n";
} else {
    echo "   ❌ Error API: {$response['message']}\n";
    exit(1);
}
echo "\n";

// Prueba 3: Listar reasignaciones temporales
echo "4. Probando endpoint: temporales...\n";
$response = apiRequest('temporales');

if ($response['success'] && count($response['data']) > 0) {
    echo "   ✅ API retornó " . count($response['data']) . " reasignación(es) temporal(es)\n";
    foreach ($response['data'] as $temp) {
        echo "      - Establecimiento: {$temp['establecimiento_nombre']}\n";
        echo "        Reasignado a: {$temp['registrador_nombre']}\n";
        echo "        Meses: {$temp['meses']}\n";
        if ($temp['titular_anual']) {
            echo "        Titular anual: {$temp['titular_anual']['nombre_completo']}\n";
        }
    }
} else {
    echo "   ❌ Error: No se encontraron reasignaciones temporales\n";
    exit(1);
}
echo "\n";

// Prueba 4: Validar que temporal sin meses falla
echo "5. Probando validación: temporal sin meses (debería fallar)...\n";
$response = apiRequest('asignar_multiple', [
    'action' => 'asignar_multiple',
    'usuario_id' => 3,
    'establecimiento_ids' => [2],
    'anio' => 2026,
    'meses' => 'ALL',
    'tipo_asignacion' => 'temporal'
]);

if (!$response['success']) {
    echo "   ✅ Correcto: API rechazó temporal sin meses: {$response['message']}\n";
} else {
    echo "   ❌ ERROR: Debería haber rechazado temporal sin meses\n";
    exit(1);
}
echo "\n";

// Prueba 5: Remover reasignación temporal
echo "6. Probando endpoint: remover (TEMPORAL)...\n";
$response = apiRequest('remover', [
    'action' => 'remover',
    'usuario_id' => 3,
    'establecimiento_id' => 1,
    'anio' => 2026,
    'tipo_asignacion' => 'temporal'
]);

if ($response['success']) {
    echo "   ✅ API respondió exitosamente: {$response['message']}\n";
} else {
    echo "   ❌ Error API: {$response['message']}\n";
    exit(1);
}
echo "\n";

// Prueba 6: Verificar que temporales está vacío después de remover
echo "7. Verificando que no hay temporales activas después de remover...\n";
$response = apiRequest('temporales');

if ($response['success'] && count($response['data']) === 0) {
    echo "   ✅ Correcto: No hay reasignaciones temporales activas\n";
} else {
    echo "   ❌ Error: Debería estar vacío\n";
    exit(1);
}
echo "\n";

// Limpieza final
echo "8. Limpiando datos de prueba...\n";
$asigModel->removerTodas(2, 2026);
$asigModel->removerTodas(3, 2026);
echo "   ✅ Datos limpiados\n\n";

echo "========================================\n";
echo "TODAS LAS PRUEBAS DE API PASARON ✅\n";
echo "========================================\n";
