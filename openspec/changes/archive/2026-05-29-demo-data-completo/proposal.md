## Why

Los usuarios demo creados en `seed_demo.php` existen pero no tienen datos asociados: no hay establecimientos asignados al registrador, no hay observaciones de ejemplo, ni historial de estados. Para explorar todas las funciones del sistema (dashboard, supervisión, reportes, gráficos) se necesita un conjunto completo de datos demo que permita visualizar cada pantalla con información realista.

## What Changes

- Extender `seed_demo.php` para que también genere datos demo completos: asignaciones, observaciones en múltiples estados, e historial de cambios
- Los datos serán autocontenidos (detectan si ya existen y se actualizan)
- Cubren las tablas: `asignaciones_establecimientos`, `observaciones`, `historial_estados`
- Usan establacimientos y comunas ya existentes del seed inicial (`config/init_db.sql`)
- Solo ejecutable en entorno development (misma protección que seed_demo.php)
- **BREAKING**: Ninguno. Solo agrega datos, no modifica esquemas.

## Capabilities

### New Capabilities
- `demo-data-full`: Conjunto completo de datos demo que permite probar todas las funcionalidades del sistema sin tener que ingresar datos manualmente.

### Modified Capabilities
- Ninguna.

## Impact

- `seed_demo.php` — Modificado: se agregará lógica de seed de datos demo (asignaciones, observaciones, historial)
- No afecta API, vistas, modelos, ni base de datos existente
