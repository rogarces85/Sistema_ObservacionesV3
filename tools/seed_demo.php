<?php
/**
 * Seed de Datos Demo
 * Crea o restablece usuarios, asignaciones, observaciones e historial
 * para entornos de desarrollo/demo.
 * 
 * Uso: php seed_demo.php
 * 
 * ADVERTENCIA: Solo funciona en entorno 'development'.
 * No ejecutar en producción.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/models/Database.php';

// ─── Guard: solo en development ─────────────────────────────
if (ENVIRONMENT !== 'development') {
    fwrite(STDERR, "ERROR: Este script solo puede ejecutarse en entorno 'development'.\n");
    fwrite(STDERR, "Entorno actual: " . ENVIRONMENT . "\n");
    exit(1);
}

$db = Database::getInstance();
$currentYear = date('Y');

// ─── Helpers ────────────────────────────────────────────────
function getUserIdByUsername($db, $username) {
    $row = $db->queryOne("SELECT id FROM usuarios WHERE username = ?", [$username]);
    return $row ? (int)$row['id'] : null;
}

function getEstablecimientoIdByCodigo($db, $codigo) {
    $row = $db->queryOne("SELECT id FROM establecimientos WHERE codigo_establecimiento = ?", [$codigo]);
    return $row ? (int)$row['id'] : null;
}

// ─── 1. Usuarios demo ───────────────────────────────────────
function seedUsuarios($db) {
    $demoUsers = [
        ['username' => 'demo_registrador', 'password' => 'Demo1234', 'nombre_completo' => 'Demo Registrador', 'rol' => 'registrador'],
        ['username' => 'demo_supervisor',  'password' => 'Demo1234', 'nombre_completo' => 'Demo Supervisor',  'rol' => 'supervisor'],
    ];

    $created = 0;
    $updated = 0;

    foreach ($demoUsers as $user) {
        $existing = $db->queryOne("SELECT id FROM usuarios WHERE username = ?", [$user['username']]);
        if ($existing) {
            $hash = password_hash($user['password'], PASSWORD_DEFAULT);
            $db->execute(
                "UPDATE usuarios SET password_hash = ?, nombre_completo = ?, rol = ?, activo = 1, fecha_actualizacion = NOW() WHERE id = ?",
                [$hash, $user['nombre_completo'], $user['rol'], $existing['id']]
            );
            $updated++;
            echo "✓ Actualizado: {$user['username']}\n";
        } else {
            $hash = password_hash($user['password'], PASSWORD_DEFAULT);
            $db->execute(
                "INSERT INTO usuarios (username, password_hash, nombre_completo, rol, activo) VALUES (?, ?, ?, ?, 1)",
                [$user['username'], $hash, $user['nombre_completo'], $user['rol']]
            );
            $created++;
            echo "✓ Creado: {$user['username']}\n";
        }
    }

    echo "  Usuarios: {$created} creados, {$updated} actualizados\n";
}

// ─── 2. Asignaciones demo ───────────────────────────────────
function seedAsignaciones($db, $currentYear) {
    $registradorId = getUserIdByUsername($db, 'demo_registrador');
    if (!$registradorId) {
        echo "  ✗ Saltando asignaciones: demo_registrador no encontrado\n";
        return;
    }

    $codigosEstablecimiento = [101, 102, 201];
    $asignadas = 0;
    $yaExistentes = 0;

    foreach ($codigosEstablecimiento as $codigo) {
        $estId = getEstablecimientoIdByCodigo($db, $codigo);
        if (!$estId) {
            echo "  ✗ Establecimiento código {$codigo} no encontrado\n";
            continue;
        }

        $existe = $db->queryOne(
            "SELECT id FROM asignaciones_establecimientos WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ?",
            [$registradorId, $estId, $currentYear]
        );

        if ($existe) {
            $yaExistentes++;
        } else {
            $db->execute(
                "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio, meses, tipo_asignacion) VALUES (?, ?, ?, 'ALL', 'anual')",
                [$registradorId, $estId, $currentYear]
            );
            $asignadas++;
        }
    }

    echo "  Asignaciones: {$asignadas} creadas, {$yaExistentes} ya existentes\n";
}

// ─── 3. Observaciones demo ──────────────────────────────────
function seedObservaciones($db, $currentYear) {
    global $SERIES_REM, $MESES;

    $registradorId = getUserIdByUsername($db, 'demo_registrador');
    $supervisorId = getUserIdByUsername($db, 'demo_supervisor');
    if (!$registradorId || !$supervisorId) {
        echo "  ✗ Saltando observaciones: usuarios demo no encontrados\n";
        return;
    }

    $asignaciones = $db->query(
        "SELECT establecimiento_id FROM asignaciones_establecimientos WHERE usuario_id = ? AND anio = ? LIMIT 3",
        [$registradorId, $currentYear]
    );
    if (empty($asignaciones)) {
        echo "  ✗ Saltando observaciones: no hay establecimientos asignados\n";
        return;
    }

    $existentes = $db->queryOne(
        "SELECT COUNT(*) as total FROM observaciones WHERE usuario_registro_id = ? AND anio = ?",
        [$registradorId, $currentYear]
    );
    if ($existentes && $existentes['total'] > 0) {
        echo "  Observaciones: {$existentes['total']} ya existentes, se saltó creación\n";
        return;
    }

    $serieA = $SERIES_REM[0];

    $observaciones = [
        [
            'estado_actual' => ESTADO_PENDIENTE,
            'mes' => 'Enero',
            'tipo_error' => 'ERROR',
            'codigo_serie' => 'SERIE A',
            'codigo_hoja' => 'A01',
            'detalle' => 'Dato de consultas médicas no coincide con el consolidado mensual. Se registraron 120 atenciones cuando el libro clínico muestra 145.',
            'plazo_entrega' => PLAZO_DENTRO,
            'usa_validador' => USA_VALIDADOR_SI,
            'respuesta' => null,
            'clasificacion' => null,
        ],
        [
            'estado_actual' => ESTADO_APROBADO,
            'mes' => 'Febrero',
            'tipo_error' => 'S/OBSERVACION',
            'codigo_serie' => '',
            'codigo_hoja' => '',
            'detalle' => 'Sin novedades en el periodo. Todos los datos fueron registrados correctamente.',
            'plazo_entrega' => PLAZO_DENTRO,
            'usa_validador' => USA_VALIDADOR_NO,
            'respuesta' => 'Se corrigió el valor en el sistema.',
            'clasificacion' => 'Corregido por el establecimiento',
        ],
        [
            'estado_actual' => ESTADO_RECHAZADO,
            'mes' => 'Marzo',
            'tipo_error' => 'ERROR',
            'codigo_serie' => 'SERIE BS',
            'codigo_hoja' => 'B',
            'detalle' => 'El total de la hoja B no cuadra con la suma de sus componentes. Diferencia de 23 unidades.',
            'plazo_entrega' => PLAZO_FUERA,
            'usa_validador' => USA_VALIDADOR_SI,
            'respuesta' => 'Se reenviará corregido.',
            'clasificacion' => 'Rechazado - debe reingresar con datos corregidos',
        ],
        [
            'estado_actual' => ESTADO_ERROR,
            'mes' => 'Abril',
            'tipo_error' => 'REVISAR',
            'codigo_serie' => 'SERIE P',
            'codigo_hoja' => 'P01',
            'detalle' => 'La hoja P01 presenta valores inconsistentes con el periodo anterior. Se requiere revisión del establecimiento.',
            'plazo_entrega' => PLAZO_DENTRO,
            'usa_validador' => USA_VALIDADOR_SI,
            'respuesta' => null,
            'clasificacion' => null,
        ],
        [
            'estado_actual' => ESTADO_JUSTIFICADO,
            'mes' => 'Mayo',
            'tipo_error' => 'F/PLAZO',
            'codigo_serie' => 'SERIE A',
            'codigo_hoja' => 'A03',
            'detalle' => 'La información fue enviada fuera de plazo por problemas técnicos en la conexión del establecimiento.',
            'plazo_entrega' => PLAZO_FUERA,
            'usa_validador' => USA_VALIDADOR_NO,
            'respuesta' => 'Problemas de conectividad informados a la red asistencial.',
            'clasificacion' => 'Justificado por causa técnica',
        ],
    ];

    $establecimientoIds = array_column($asignaciones, 'establecimiento_id');
    $creadas = 0;

    foreach ($observaciones as $i => $obs) {
        $estId = $establecimientoIds[$i % count($establecimientoIds)];

        $params = [
            $currentYear, $obs['mes'], $estId, $obs['codigo_serie'], $obs['codigo_hoja'],
            $obs['tipo_error'], $obs['detalle'], $obs['plazo_entrega'], $obs['usa_validador'],
            $registradorId, $obs['estado_actual'], $obs['clasificacion'],
            $obs['estado_actual'] !== ESTADO_PENDIENTE ? $supervisorId : null,
            $obs['estado_actual'] !== ESTADO_PENDIENTE ? date('Y-m-d H:i:s') : null,
        ];

        $db->execute(
            "INSERT INTO observaciones 
            (anio, mes, establecimiento_id, codigo_serie, codigo_hoja, tipo_error, 
             detalle_observacion, plazo_entrega, usa_validador, usuario_registro_id, 
             estado_actual, clasificacion, usuario_supervisor_id, fecha_revision)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            $params
        );

        $obsId = $db->lastInsertId();
        $creadas++;

        // ─── 4. Historial para observaciones revisadas ────────────
        if ($obs['estado_actual'] !== ESTADO_PENDIENTE) {
            $db->execute(
                "INSERT INTO historial_estados (observacion_id, estado_anterior, estado_nuevo, usuario_id, comentario)
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $obsId,
                    ESTADO_PENDIENTE,
                    $obs['estado_actual'],
                    $supervisorId,
                    $obs['clasificacion'] ?? 'Cambio de estado',
                ]
            );
        }
    }

    echo "  Observaciones: {$creadas} creadas\n";
}

// ─── Main ───────────────────────────────────────────────────
try {
    echo "=== Seed de Datos Demo ===\n\n";

    echo "--- Usuarios ---\n";
    seedUsuarios($db);

    echo "\n--- Asignaciones ---\n";
    seedAsignaciones($db, $currentYear);

    echo "\n--- Observaciones ---\n";
    seedObservaciones($db, $currentYear);

    echo "\n=== Seed completado ===\n";
    exit(0);

} catch (Exception $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
