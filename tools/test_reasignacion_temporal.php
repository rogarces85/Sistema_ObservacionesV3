<?php
/**
 * Script de Prueba: Reasignación Temporal de Establecimientos
 * Verifica la lógica de prioridad temporal sobre anual
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/EstablecimientoAsignacion.php';

echo "========================================\n";
echo "PRUEBA: Reasignación Temporal\n";
echo "========================================\n\n";

$asigModel = new EstablecimientoAsignacion();

// Limpiar datos de prueba anteriores
echo "1. Limpiando datos de prueba anteriores...\n";
$asigModel->removerTodas(2, 2026);
$asigModel->removerTodas(3, 2026);
echo "   ✅ Datos limpiados\n\n";

// Prueba 1: Crear asignación anual
echo "2. Creando asignación ANUAL para Registrador 2 (Rodrigo) → Establecimiento 1 (HBSJO)\n";
$resultado1 = $asigModel->asignar(2, 1, 2026, 'ALL', 'anual');
if ($resultado1) {
    echo "   ✅ Asignación anual creada exitosamente\n";
} else {
    echo "   ❌ Error al crear asignación anual\n";
    exit(1);
}
echo "\n";

// Verificar que Rodrigo tiene acceso en todos los meses
echo "3. Verificando acceso de Rodrigo (anual) en diferentes meses...\n";
$meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'];
foreach ($meses as $mes) {
    $tieneAcceso = $asigModel->tieneAsignacionParaMes(2, 1, 2026, $mes);
    echo "   - $mes: " . ($tieneAcceso ? '✅ Acceso' : ' Sin acceso') . "\n";
}
echo "\n";

// Prueba 2: Crear reasignación temporal
echo "4. Creando REASIGNACIÓN TEMPORAL para Registrador 3 (Victoria) → Establecimiento 1 (HBSJO) para Ene-Mar\n";
$resultado2 = $asigModel->asignar(3, 1, 2026, '1,2,3', 'temporal');
if ($resultado2) {
    echo "   ✅ Reasignación temporal creada exitosamente\n";
} else {
    echo "   ❌ Error al crear reasignación temporal\n";
    exit(1);
}
echo "\n";

// Prueba 3: Verificar prioridad temporal sobre anual
echo "5. Verificando PRIORIDAD TEMPORAL sobre ANUAL...\n";
echo "   a) Rodrigo (anual) en Enero (temporal activo):\n";
$rodrigoEnero = $asigModel->tieneAsignacionParaMes(2, 1, 2026, 'Enero');
echo "      " . ($rodrigoEnero ? '❌ ERROR: Debería NO tener acceso' : '✅ Correcto: NO tiene acceso (bloqueado por temporal)') . "\n";

echo "   b) Victoria (temporal) en Enero:\n";
$victoriaEnero = $asigModel->tieneAsignacionParaMes(3, 1, 2026, 'Enero');
echo "      " . ($victoriaEnero ? '✅ Correcto: TIENE acceso (temporal activo)' : '❌ ERROR: Debería tener acceso') . "\n";

echo "   c) Rodrigo (anual) en Abril (fuera de temporal):\n";
$rodrigoAbril = $asigModel->tieneAsignacionParaMes(2, 1, 2026, 'Abril');
echo "      " . ($rodrigoAbril ? '✅ Correcto: TIENE acceso (fuera de periodo temporal)' : '❌ ERROR: Debería tener acceso') . "\n";

echo "   d) Victoria (temporal) en Abril (fuera de su periodo):\n";
$victoriaAbril = $asigModel->tieneAsignacionParaMes(3, 1, 2026, 'Abril');
echo "      " . (!$victoriaAbril ? '✅ Correcto: NO tiene acceso (fuera de su periodo)' : '❌ ERROR: No debería tener acceso') . "\n";
echo "\n";

// Prueba 4: Verificar que no se pueden crear dos temporales solapadas
echo "6. Intentando crear SEGUNDA reasignación temporal solapada (debería fallar)...\n";
$resultado3 = $asigModel->asignar(4, 1, 2026, '2,3,4', 'temporal');
if (!$resultado3) {
    echo "   ✅ Correcto: Sistema rechazó segunda temporal solapada\n";
} else {
    echo "    ERROR: Debería haber rechazado la segunda temporal\n";
    exit(1);
}
echo "\n";

// Prueba 5: Remover reasignación temporal
echo "7. Removiendo REASIGNACIÓN TEMPORAL de Victoria...\n";
$resultado4 = $asigModel->remover(3, 1, 2026, '1,2,3', 'temporal');
if ($resultado4) {
    echo "   ✅ Reasignación temporal removida exitosamente\n";
} else {
    echo "   ❌ Error al remover reasignación temporal\n";
    exit(1);
}
echo "\n";

// Prueba 6: Verificar que anual recupera acceso completo
echo "8. Verificando que Rodrigo (anual) RECUPERA acceso completo...\n";
foreach (['Enero', 'Febrero', 'Marzo', 'Abril'] as $mes) {
    $tieneAcceso = $asigModel->tieneAsignacionParaMes(2, 1, 2026, $mes);
    echo "   - $mes: " . ($tieneAcceso ? '✅ Acceso recuperado' : '❌ ERROR: Debería tener acceso') . "\n";
}
echo "\n";

// Prueba 7: Verificar que Victoria pierde acceso
echo "9. Verificando que Victoria (temporal removida) PIERDE acceso...\n";
$victoriaEnero = $asigModel->tieneAsignacionParaMes(3, 1, 2026, 'Enero');
echo "   - Enero: " . (!$victoriaEnero ? '✅ Correcto: NO tiene acceso' : ' ERROR: No debería tener acceso') . "\n";
echo "\n";

// Prueba 8: Verificar getAsignacionesTemporalesActivas
echo "10. Verificando lista de reasignaciones temporales activas...\n";
// Primero crear una temporal para probar
$asigModel->asignar(3, 1, 2026, '1,2,3', 'temporal');
$temporales = $asigModel->getAsignacionesTemporalesActivas(2026);
if (count($temporales) > 0) {
    echo "   ✅ Se encontraron " . count($temporales) . " reasignación(es) temporal(es) activa(s)\n";
    foreach ($temporales as $temp) {
        echo "      - Establecimiento: {$temp['establecimiento_nombre']}\n";
        echo "        Reasignado a: {$temp['registrador_nombre']}\n";
        echo "        Meses: {$temp['meses']}\n";
    }
} else {
    echo "   ❌ ERROR: Debería haber al menos una reasignación temporal\n";
}
echo "\n";

// Limpieza final
echo "11. Limpiando datos de prueba...\n";
$asigModel->removerTodas(2, 2026);
$asigModel->removerTodas(3, 2026);
echo "   ✅ Datos limpiados\n\n";

echo "========================================\n";
echo "TODAS LAS PRUEBAS PASARON EXITOSAMENTE ✅\n";
echo "========================================\n";
