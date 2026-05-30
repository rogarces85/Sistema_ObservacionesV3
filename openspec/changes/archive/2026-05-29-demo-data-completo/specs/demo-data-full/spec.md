## ADDED Requirements

### Requirement: Asignaciones demo para el registrador

El sistema SHALL asignar establecimientos existentes al usuario `demo_registrador` para el año actual al ejecutar el seed de datos demo. Las asignaciones SHALL ser de tipo anual (`ALL` meses).

#### Scenario: Asignar establecimientos al ejecutar seed

- **WHEN** el script `seed_demo.php` se ejecuta en entorno development
- **THEN** el usuario `demo_registrador` tiene al menos 3 establecimientos asignados para el año actual
- **THEN** los establecimientos asignados son válidos (existen en la tabla `establecimientos`)

#### Scenario: Re-ejecutar seed no duplica asignaciones

- **WHEN** el script `seed_demo.php` se ejecuta dos veces
- **THEN** la cantidad de asignaciones de `demo_registrador` no se duplica

### Requirement: Observaciones demo en múltiples estados

El sistema SHALL crear observaciones demo en todos los estados del sistema (pendiente, aprobado, rechazado, error, justificado) al ejecutar el seed. Las observaciones SHALL estar asociadas al registrador demo y a establecimientos válidos.

#### Scenario: Observaciones creadas correctamente

- **WHEN** el script `seed_demo.php` se ejecuta en entorno development
- **THEN** existen observaciones para el registrador demo en el año actual
- **THEN** existe al menos una observación en cada estado: pendiente, aprobado, rechazado, error, justificado
- **THEN** las observaciones usan valores válidos de serie REM, hoja REM, plazo y validador

### Requirement: Historial de cambios para observaciones

El sistema SHALL crear entradas en `historial_estados` para las observaciones que simulan haber sido revisadas (aprobadas, rechazadas, etc.), registrando al supervisor demo como responsable del cambio.

#### Scenario: Historial generado para estados modificados

- **WHEN** el script `seed_demo.php` se ejecuta en entorno development
- **THEN** las observaciones con estado `aprobado`, `rechazado` o `justificado` tienen al menos una entrada en `historial_estados`
- **THEN** el `usuario_id` del historial corresponde al usuario `demo_supervisor`

### Requirement: Idempotencia del seed

El script SHALL ser idempotente: verificar si ya existen datos demo antes de insertar, para evitar duplicados al re-ejecutar.

#### Scenario: Re-ejecución sin duplicados

- **WHEN** el script `seed_demo.php` se ejecuta múltiples veces
- **THEN** no se crean asignaciones, observaciones ni historiales duplicados
- **THEN** los datos existentes se mantienen intactos
