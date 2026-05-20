<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/EstablecimientoAsignacion.php';

echo "Verificación de Métodos del Modelo\n";
echo "===================================\n\n";

$model = new EstablecimientoAsignacion();
$methods = get_class_methods($model);

echo "Métodos públicos disponibles:\n";
foreach ($methods as $method) {
    echo "  - $method\n";
}

echo "\n✅ Modelo cargado correctamente\n";

// Verificar que los métodos nuevos existen
$newMethods = ['getAsignacionesTemporalesActivas', 'getTitularAnual'];
foreach ($newMethods as $newMethod) {
    if (method_exists($model, $newMethod)) {
        echo "✅ Método '$newMethod' existe\n";
    } else {
        echo "❌ Método '$newMethod' NO existe\n";
    }
}
