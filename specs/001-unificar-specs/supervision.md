# MOD-SUP: Módulo de Supervisión

## User Scenarios & Testing

### US-SUP-001: Vista filtrada de observaciones
**Prioridad: P1**

**Historia:** Como Supervisor, necesito revisar las observaciones aplicando filtros combinados para encontrar rápidamente los registros que requieren mi atención.

**Criterios de Aceptación (Gherkin):**

```gherkin
Feature: Vista filtrada de observaciones para supervisión
  Background:
    Given el usuario está autenticado como "Supervisor"

  Scenario: Listado con filtros básicos
    When el usuario solicita GET api/supervision.php?action=get_filtered
      | parametro | valor |
      |-----------|-------|
      | anio      | 2026  |
      | mes       | 3     |
    Then el sistema retorna código 200
    And el resultado contiene observaciones filtradas por año 2026 y mes 3

  Scenario: Filtro por estado
    When el usuario solicita GET api/supervision.php?action=get_filtered&estado=pendiente
    Then solo se muestran observaciones con estado_actual="pendiente"

  Scenario: Filtro por establecimiento
    When el usuario solicita GET api/supervision.php?action=get_filtered&establecimiento_id=10
    Then solo se muestran observaciones del establecimiento ID=10

  Scenario: Filtro por registrador
    When el usuario solicita GET api/supervision.php?action=get_filtered&usuario_registro_id=5
    Then solo se muestran observaciones creadas por el usuario ID=5

  Scenario: Búsqueda por texto
    When el usuario solicita GET api/supervision.php?action=get_filtered&search=inconsistente
    Then el resultado contiene observaciones cuyo detalle_observacion contiene "inconsistente"

  Scenario: Paginación
    When el usuario solicita GET api/supervision.php?action=get_filtered&pagina=1&limite=20
    Then el resultado incluye metadatos de paginación (total, página, límite, total_páginas)
    And el listado contiene máximo 20 observaciones

  Scenario: Combinación de todos los filtros
    When el usuario solicita GET api/supervision.php?action=get_filtered
      | parametro           | valor      |
      |---------------------|------------|
      | anio                | 2026       |
      | mes                 | 3          |
      | estado              | pendiente  |
      | establecimiento_id  | 10         |
      | usuario_registro_id | 5          |
      | search              | error      |
      | pagina              | 1          |
      | limite              | 10         |
    Then el sistema aplica todos los filtros en conjunto
    And retorna los resultados paginados
```

### US-SUP-002: Aprobar observación
**Prioridad: P1**

```gherkin
Feature: Aprobar observación
  Background:
    Given el usuario está autenticado como "Supervisor"

  Scenario: Aprobar como "Sin Observación"
    Given la observación ID=5 tiene estado_actual="pendiente"
    When el usuario envía POST api/supervision.php?action=approve con:
      | id                | 5              |
      | estado_resultante | sin_observacion|
      | clasificacion     | Corregido      |
      | detalle_error     |                |
    Then el sistema retorna código 200
    And la observación ID=5 queda con estado="aprobado" y tipo_error="S/OBSERVACION"
    And se registra el historial con estado_nuevo="aprobado"

  Scenario: Aprobar como "Error"
    Given la observación ID=6 tiene estado_actual="pendiente"
    When el usuario envía POST api/supervision.php?action=approve con:
      | id                | 6        |
      | estado_resultante | error    |
      | clasificacion     | Error    |
      | detalle_error     | El dato no coincide con el REM |
    Then el sistema retorna código 200
    And la observación ID=6 queda con estado="error" y tipo_error="ERROR"
    And se registra el historial con estado_nuevo="error"

  Scenario: Estado resultante inválido
    When el usuario envía POST api/supervision.php?action=approve con estado_resultante="invalido"
    Then el sistema retorna código 400

  Scenario: Aprobar observación inexistente
    When el usuario envía POST api/supervision.php?action=approve con id=99999
    Then el sistema retorna código 404

  Scenario: Registrador intenta aprobar
    Given el usuario está autenticado como "Registrador"
    When el usuario envía POST api/supervision.php?action=approve
    Then el sistema retorna código 403
```

### US-SUP-003: Cancelar observación
**Prioridad: P1**

```gherkin
Feature: Cancelar observación
  Background:
    Given el usuario está autenticado como "Supervisor"

  Scenario: Cancelación exitosa
    Given la observación ID=5 existe y no está rechazada
    When el usuario envía POST api/supervision.php?action=cancel con id=5
    Then el sistema retorna código 200
    And la observación ID=5 queda con estado="rechazado"

  Scenario: Cancelar observación ya rechazada
    Given la observación ID=5 tiene estado="rechazado"
    When el usuario envía POST api/supervision.php?action=cancel con id=5
    Then el sistema retorna código 400
    And el mensaje indica que la observación ya está rechazada
```

### US-SUP-004: Eliminación blanda (mover a papelera)
**Prioridad: P2**

```gherkin
Feature: Eliminar observación (soft delete)
  Background:
    Given el usuario está autenticado como "Supervisor"

  Scenario: Mover a papelera exitosamente
    When el usuario envía POST api/supervision.php?action=delete con id=5
    Then el sistema retorna código 200
    And la observación ID=5 queda marcada como eliminada (soft delete)

  Scenario: Eliminar observación ya eliminada
    Given la observación ID=5 ya está en papelera
    When el usuario envía POST api/supervision.php?action=delete con id=5
    Then el sistema retorna código 400
```

### US-SUP-005: Cambio genérico de estado
**Prioridad: P2**

```gherkin
Feature: Cambio genérico de estado
  Background:
    Given el usuario está autenticado como "Supervisor"

  Scenario: Cambio de estado exitoso
    When el usuario envía POST api/supervision.php?action=update_status con:
      | id          | 5          |
      | nuevo_estado| pendiente  |
      | comentario  | Reabierto para revisión |
    Then el sistema retorna código 200
    And la observación ID=5 queda con estado_actual="pendiente"
    And se registra el cambio en el historial

  Scenario: Estado nuevo inválido
    When el usuario envía POST api/supervision.php?action=update_status con nuevo_estado="inexistente"
    Then el sistema retorna código 400
```

### US-SUP-006: Detalle completo con historial
**Prioridad: P1**

```gherkin
Feature: Detalle completo de observación con historial
  Background:
    Given el usuario está autenticado como "Supervisor"

  Scenario: Detalle completo de observación existente
    When el usuario solicita GET api/supervision.php?action=get_detail&id=5
    Then el sistema retorna código 200
    And el resultado incluye todos los campos de la observación
    And el resultado incluye el historial completo de cambios
    And el resultado incluye la serie REM y hoja REM asociadas
    And el resultado incluye la respuesta del establecimiento

  Scenario: Detalle de observación inexistente
    When el usuario solicita GET api/supervision.php?action=get_detail&id=99999
    Then el sistema retorna código 404
```

### US-SUP-007: Operaciones masivas
**Prioridad: P2**

```gherkin
Feature: Operaciones masivas sobre observaciones
  Background:
    Given el usuario está autenticado como "Supervisor"

  Scenario: Aprobación masiva
    When el usuario envía POST api/supervision.php?action=approve con:
      | ids              | [5, 6, 7]     |
      | estado_resultante| sin_observacion|
      | clasificacion    | Corregido      |
    Then el sistema retorna código 200
    And todas las observaciones {5, 6, 7} quedan con estado="aprobado"
    And el resultado incluye un resumen: 3 exitosas, 0 fallidas

  Scenario: Cancelación masiva
    When el usuario envía POST api/supervision.php?action=cancel con ids=[5, 6, 7]
    Then todas las observaciones {5, 6, 7} quedan con estado="rechazado"

  Scenario: Eliminación masiva
    When el usuario envía POST api/supervision.php?action=delete con ids=[5, 6, 7]
    Then todas las observaciones {5, 6, 7} quedan marcadas como eliminadas

  Scenario: Operación masiva parcialmente fallida
    Given la observación ID=5 existe e ID=99999 no existe
    When el usuario envía POST api/supervision.php?action=approve con ids=[5, 99999]
    Then la observación ID=5 se procesa exitosamente
    And el resumen indica 1 exitosa, 1 fallida
    And se retorna el detalle del error para ID=99999
```

### Casos Borde (Edge Cases)

| #  | Descripción                                                                  | Módulo     |
|----|------------------------------------------------------------------------------|------------|
| EC-SUP-01 | Parámetros de paginación con valores negativos o cero                       | SUP-001    |
| EC-SUP-02 | Búsqueda por texto con caracteres especiales (regex injection)              | SUP-001    |
| EC-SUP-03 | Aprobar observación ya aprobada                                             | SUP-002    |
| EC-SUP-04 | Aprobar con clasificación vacía cuando estado_resultante es "error"         | SUP-002    |
| EC-SUP-05 | Cancelar observación ya cancelada                                           | SUP-003    |
| EC-SUP-06 | Operación masiva con lista de IDs vacía                                     | SUP-007    |
| EC-SUP-07 | Operación masiva con IDs duplicados en la lista                             | SUP-007    |
| EC-SUP-08 | Acceso a detalle de observación sin permisos de supervisor                  | SUP-006    |
| EC-SUP-09 | Cambio de estado a un valor no reconocido (no está en la lista de estados válidos) | SUP-005    |

---

## Requirements

### Functional Requirements

**FR-SUP-001: Vista filtrada con paginación**
El sistema debe proporcionar una vista de observaciones filtrable por año, mes, estado, establecimiento, registrador, búsqueda por texto, con paginación configurable. Valor por defecto: 50 registros por página. Solo accesible para usuarios con rol "Supervisor".

**FR-SUP-002: Aprobación de observación**
El sistema debe permitir al Supervisor aprobar una observación mediante dos modalidades:
- **Sin Observación**: estado_resultante = "sin_observacion" → estado pasa a "aprobado", tipo_error pasa a "S/OBSERVACION"
- **Error**: estado_resultante = "error" → estado pasa a "error", tipo_error pasa a "ERROR"
clasificacion y detalle_error son opcionales en ambas modalidades. Si no se envían, se guardan como NULL.

**FR-SUP-003: Cancelación de observación**
El sistema debe permitir al Supervisor cancelar una observación cambiando su estado a "rechazado".

**FR-SUP-004: Eliminación blanda (soft delete)**
El sistema debe permitir al Supervisor marcar una observación como eliminada sin borrarla físicamente de la base de datos.

**FR-SUP-005: Cambio genérico de estado**
El sistema debe permitir al Supervisor cambiar el estado de una observación a cualquier estado válido sin restricciones de máquina de estados (transición libre), registrando el cambio en el historial con un comentario opcional.

**FR-SUP-006: Detalle completo con historial**
El sistema debe proporcionar una vista detallada de una observación que incluya todos sus campos, la serie REM, la hoja REM, la respuesta del establecimiento y el historial completo de cambios de estado.

**FR-SUP-007: Operaciones masivas**
El sistema debe soportar operaciones de aprobación, cancelación y eliminación de múltiples observaciones simultáneamente, retornando un resumen con el conteo de operaciones exitosas y fallidas.

**FR-SUP-008: Clasificaciones disponibles**
El sistema debe soportar las siguientes clasificaciones al aprobar una observación: Corregido, Error, Sin respuesta del Establecimiento, Respuesta incorrecta de Establecimiento.

**FR-SUP-009: Restricción de acceso por rol**
Todas las operaciones del módulo de supervisión deben estar restringidas exclusivamente a usuarios con rol "Supervisor". Cualquier intento de acceso por parte de un "Registrador" debe ser rechazado con código 403.

### Key Entities

Las entidades principales son compartidas con el módulo MOD-OBS. Este módulo opera sobre:

**observaciones** (campos adicionales utilizados en supervisión)
| Campo                     | Tipo       | Uso en supervisión                                      |
|---------------------------|------------|----------------------------------------------------------|
| id                        | INT (PK)   | Identificador de la observación a supervisar             |
| estado_actual             | VARCHAR    | Estado modificable por el supervisor                     |
| tipo_error                | VARCHAR    | Se actualiza según la acción de aprobación               |
| clasificacion             | VARCHAR    | Asignada por el supervisor al aprobar                    |
| detalle_error             | TEXT       | Descripción del error asignada por el supervisor         |
| respuesta_establecimiento | TEXT       | Respuesta del establecimiento visible en el detalle      |
| usuario_supervisor_id     | INT (FK)   | Supervisor que realizó la última acción                  |
| _soft_delete              | —          | No hay campo BOOLEAN. Soft delete mueve el registro a `observaciones_eliminadas` |
| fecha_creacion            | DATETIME   | Fecha de creación (DEFAULT CURRENT_TIMESTAMP)                      |
| fecha_actualizacion       | DATETIME   | Fecha de última actualización (ON UPDATE CURRENT_TIMESTAMP)        |

**historial_estados**
| Campo           | Tipo       | Descripción                                       |
|-----------------|------------|---------------------------------------------------|
| id              | INT (PK)   | Identificador único                               |
| observacion_id  | INT (FK)   | Referencia a la observación supervisada           |
| estado_anterior | VARCHAR    | Estado previo al cambio                           |
| estado_nuevo    | VARCHAR    | Estado resultante                                 |
| usuario_id      | INT (FK)   | Supervisor que realizó la acción                  |
| comentario      | TEXT       | Comentario del supervisor                         |
| fecha_creacion  | DATETIME   | Fecha de creación (DEFAULT CURRENT_TIMESTAMP)     |

---

## Success Criteria

| #   | Criterio                                                                  | Verificación                                                         |
|-----|---------------------------------------------------------------------------|----------------------------------------------------------------------|
| SC-SUP-01 | La vista filtrada con todos los filtros combinados responde en menos de 3 segundos para 50,000 registros | Prueba de performance con base de datos poblada                     |
| SC-SUP-02 | Las operaciones masivas procesan hasta 100 observaciones en menos de 5 segundos | Prueba de carga con lote de 100 IDs                                  |
| SC-SUP-03 | El soft delete no elimina físicamente el registro de la base de datos     | Verificación directa en base de datos después de la operación        |
| SC-SUP-04 | El detalle completo incluye todos los campos requeridos más el historial  | Prueba de integración que valida la estructura completa de la respuesta|
| SC-SUP-05 | El sistema rechaza cualquier operación de un usuario sin rol Supervisor   | Suite de pruebas de autorización cubriendo todos los endpoints       |
| SC-SUP-06 | El historial registra correctamente cada cambio con todos sus campos      | Prueba de integración que verifica la tabla historial_estados después de cada operación |

---

## Clarifications

### Session 2026-06-01

- Q: ¿Soft delete con campo BOOLEAN o tabla separada? → A: Tabla separada `observaciones_eliminadas`. No existe campo BOOLEAN en `observaciones`.
- Q: ¿clasificacion y detalle_error son obligatorios? → A: Opcionales en ambas modalidades. Si no se envían, se guardan como NULL.
- Q: ¿Transaccionalidad en operaciones masivas? → A: No transaccional. Resumen detallado con IDs exitosos y fallidos para reintento.
- Q: ¿Máquina de estados con restricciones? → A: Sin restricciones. Transición libre entre cualquier estado vía update_status.
- Q: ¿Tamaño de página por defecto en supervisión? → A: 50 registros por página, consistente con el módulo de observaciones.

---

## Assumptions

| #   | Supuesto                                                                   |
|-----|---------------------------------------------------------------------------|
| AS-SUP-01 | El soft delete se implementa moviendo la observación a la tabla separada `observaciones_eliminadas` con copia completa de datos + motivo de eliminación. No existe campo BOOLEAN en `observaciones` |
| AS-SUP-02 | Las operaciones masivas no son transaccionales. Cada observación se procesa individualmente y se retorna un resumen con IDs exitosos y fallidos para que el supervisor pueda reintentar |
| AS-SUP-03 | El módulo de supervisión es exclusivo para usuarios con rol "Supervisor"  |
| AS-SUP-04 | La autenticación y autorización se manejan mediante token JWT o sesión    |
| AS-SUP-05 | Las clasificaciones disponibles son fijas: Corregido, Error, Sin respuesta del Establecimiento, Respuesta incorrecta de Establecimiento |
| AS-SUP-06 | Las observaciones movidas a `observaciones_eliminadas` no aparecen en los listados normales de supervisión. Solo se accede a ellas desde el módulo de Papelera |
| AS-SUP-07 | Los valores de estado_resultante en aprobación son únicamente "sin_observacion" y "error" |
| AS-SUP-08 | La respuesta del establecimiento se captura por separado y se asocia a la observación |
