# MOD-OBS: Módulo de Observaciones (CRUD)

## User Scenarios & Testing

### US-OBS-001: Listar observaciones por año
**Prioridad: P1**

**Historia:** Como Registrador, quiero listar las observaciones filtradas por año para visualizar las incidencias registradas en los establecimientos que me fueron asignados.

**Criterios de Aceptación (Gherkin):**

```gherkin
Feature: Listar observaciones por año
  Background:
    Given el usuario está autenticado como "Registrador"

  Scenario: Listado exitoso de observaciones del año actual
    When el usuario solicita GET api/observaciones.php con anio=2026
    Then el sistema retorna código 200
    And el listado contiene solo observaciones del año 2026
    And el listado contiene solo observaciones de establecimientos asignados al usuario

  Scenario: Registrador no ve observaciones de otros registradores
    When el usuario solicita GET api/observaciones.php con anio=2026
    Then ninguna observación en el resultado tiene usuario_registro_id diferente al del usuario actual

  Scenario: Supervisor ve todas las observaciones
    Given el usuario está autenticado como "Supervisor"
    When el usuario solicita GET api/observaciones.php con anio=2026
    Then el listado contiene observaciones de todos los registradores

  Scenario: Año sin observaciones
    When el usuario solicita GET api/observaciones.php con anio=2030
    Then el sistema retorna código 200
    And el listado está vacío
```

### US-OBS-002: Obtener observación por ID
**Prioridad: P1**

```gherkin
Feature: Obtener observación por ID
  Scenario: Observación existente
    When el usuario solicita GET api/observaciones.php?id=5
    Then el sistema retorna código 200
    And el cuerpo contiene todos los campos de la observación incluyendo historial

  Scenario: Observación inexistente
    When el usuario solicita GET api/observaciones.php?id=99999
    Then el sistema retorna código 404
    And el cuerpo contiene un mensaje de error

  Scenario: ID no numérico
    When el usuario solicita GET api/observaciones.php?id=abc
    Then el sistema retorna código 400
```

### US-OBS-003: Ver historial de cambios
**Prioridad: P2**

```gherkin
Feature: Ver historial de cambios de una observación
  Scenario: Historial disponible
    When el usuario solicita GET api/observaciones.php?action=historial&id=5
    Then el sistema retorna código 200
    And el resultado contiene una lista ordenada por fecha descendente
    And cada entrada incluye estado_anterior, estado_nuevo, usuario_id, comentario

  Scenario: Observación sin historial
    When el usuario solicita GET api/observaciones.php?action=historial&id=99999
    Then el sistema retorna código 404
```

### US-OBS-004: Obtener estadísticas
**Prioridad: P2**

```gherkin
Feature: Obtener estadísticas de observaciones
  Scenario: Estadísticas generales
    When el usuario solicita GET api/observaciones.php?action=stats
    Then el sistema retorna código 200
    And el resultado incluye agrupaciones por estado, mes y tipo_error

  Scenario: Estadísticas filtradas por año
    When el usuario solicita GET api/observaciones.php?action=stats&anio=2026
    Then el resultado está limitado al año 2026
```

### US-OBS-005: Crear observación
**Prioridad: P1**

```gherkin
Feature: Crear observación
  Background:
    Given el usuario está autenticado como "Registrador"

  Scenario: Creación exitosa con todos los campos requeridos
    When el usuario envía POST api/observaciones.php con:
      | mes                | 3                            |
      | establecimiento_id | 10                           |
      | codigo_serie       | SERIE A                      |
      | codigo_hoja        | H01                          |
      | tipo_error         | ERROR                        |
      | detalle_observacion| Dato inconsistente en campo X|
      | plazo_entrega      | 2026-04-15                   |
      | usa_validador      | 1                            |
    Then el sistema retorna código 201
    And se registra el historial con estado_nuevo "pendiente"

  Scenario: Creación sin codigo_hoja para tipo_error S/OBSERVACION
    When el usuario envía POST api/observaciones.php con:
      | mes                | 3         |
      | establecimiento_id | 10        |
      | codigo_serie       | SERIE A   |
      | tipo_error         | S/OBSERVACION |
      | detalle_observacion| Todo correcto |
    Then el sistema retorna código 201
    And codigo_hoja se guarda como NULL

  Scenario: Registrador intenta crear para establecimiento no asignado
    When el usuario envía POST api/observaciones.php con establecimiento_id=999
    Then el sistema retorna código 403
    And el mensaje indica que el establecimiento no está asignado al usuario

  Scenario: Supervisor intenta crear observación
    Given el usuario está autenticado como "Supervisor"
    When el usuario envía POST api/observaciones.php con datos válidos
    Then el sistema retorna código 403

  Scenario: Mes sin asignación activa
    Given el usuario no tiene asignación para el mes 3 del año 2026
    When el usuario envía POST api/observaciones.php con mes=3&anio=2026
    Then el sistema retorna código 403

  Scenario: Validación de campos requeridos
    When el usuario envía POST api/observaciones.php sin el campo mes
    Then el sistema retorna código 400
    And el cuerpo indica que mes es requerido

  Scenario: tipo_error inválido
    When el usuario envía POST api/observaciones.php con tipo_error="INVALIDO"
    Then el sistema retorna código 400
    And el mensaje indica que tipo_error debe ser uno de los valores permitidos
```

### US-OBS-006: Actualizar observación
**Prioridad: P1**

```gherkin
Feature: Actualizar observación
  Scenario: Registrador actualiza observación propia en estado pendiente
    Given el usuario es "Registrador"
    And la observación ID=5 pertenece al usuario
    And la observación ID=5 tiene estado_actual="pendiente"
    When el usuario envía PUT api/observaciones.php?id=5 con detalle_observacion="Corregido"
    Then el sistema retorna código 200
    And se registra el cambio en el historial

  Scenario: Registrador intenta actualizar observación de otro usuario
    Given la observación ID=5 pertenece a otro registrador
    When el usuario envía PUT api/observaciones.php?id=5
    Then el sistema retorna código 403

  Scenario: Registrador intenta actualizar observación en estado no pendiente
    Given la observación ID=5 tiene estado_actual="aprobado"
    When el usuario envía PUT api/observaciones.php?id=5
    Then el sistema retorna código 403

  Scenario: Supervisor actualiza cualquier observación
    Given el usuario es "Supervisor"
    When el usuario envía PUT api/observaciones.php?id=5
    Then el sistema retorna código 200
```

### US-OBS-007: Eliminar observación
**Prioridad: P1**

```gherkin
Feature: Eliminar observación
  Scenario: Supervisor elimina observación existente
    Given el usuario está autenticado como "Supervisor"
    When el usuario envía DELETE api/observaciones.php?id=5
    Then el sistema retorna código 200
    And la observación ID=5 ya no existe en la base de datos

  Scenario: Registrador intenta eliminar observación
    Given el usuario está autenticado como "Registrador"
    When el usuario envía DELETE api/observaciones.php?id=5
    Then el sistema retorna código 403

  Scenario: Eliminar observación inexistente
    Given el usuario está autenticado como "Supervisor"
    When el usuario envía DELETE api/observaciones.php?id=99999
    Then el sistema retorna código 404
```

### US-OBS-008: Importar desde Excel
**Prioridad: P2**

```gherkin
Feature: Importar observaciones desde Excel
  Scenario: Importación exitosa con vista previa y confirmación
    Given el usuario selecciona un archivo Excel válido
    When el sistema muestra la vista previa con las observaciones detectadas
    And el usuario confirma la importación
    Then las observaciones se crean en la base de datos
    And se retorna un resumen con el total de registros importados

  Scenario: Archivo Excel con formato inválido
    Given el usuario selecciona un archivo sin las columnas requeridas
    When el sistema procesa el archivo
    Then se muestra un error indicando el formato incorrecto

  Scenario: Importación con observaciones duplicadas
    Given el archivo contiene observaciones que ya existen (misma serie+hoja+establecimiento+mes+tipo_error)
    When el sistema muestra la vista previa
    Then las duplicadas se marcan con advertencia visual
    And el usuario puede decidir si importarlas igual o saltarlas
    And el resumen final incluye el conteo de duplicados y la acción tomada
```

### US-OBS-009: Exportar observaciones
**Prioridad: P3**

```gherkin
Feature: Exportar observaciones
  Scenario: Exportar a Excel
    When el usuario solicita exportar en formato Excel
    Then el sistema descarga un archivo .xlsx con las observaciones filtradas

  Scenario: Exportar a PDF
    When el usuario solicita exportar en formato PDF
    Then el sistema descarga un archivo .pdf con las observaciones filtradas

  Scenario: Exportar a CSV
    When el usuario solicita exportar en formato CSV
    Then el sistema descarga un archivo .csv con las observaciones filtradas
```

### Casos Borde (Edge Cases)

| #  | Descripción                                                                 | Módulo     |
|----|-----------------------------------------------------------------------------|------------|
| EC-OBS-01 | Año con valor negativo o cero en el filtro de listado                     | OBS-001    |
| EC-OBS-02 | ID de observación con caracteres especiales (SQL injection)               | OBS-002    |
| EC-OBS-03 | Historial de observación eliminada                                        | OBS-003    |
| EC-OBS-04 | Estadísticas sin datos en el período solicitado                           | OBS-004    |
| EC-OBS-05 | Creación con mes fuera del rango 1-12                                     | OBS-005    |
| EC-OBS-06 | Creación con establecimiento_id = 0 o negativo                            | OBS-005    |
| EC-OBS-07 | Creación con detalle_observacion que excede la longitud máxima            | OBS-005    |
| EC-OBS-08 | Creación con código_serie no soportado                                    | OBS-005    |
| EC-OBS-09 | Actualización concurrente de la misma observación — last-write-wins, cada cambio queda en historial con timestamp | OBS-006    |
| EC-OBS-10 | Importación de archivo Excel con celdas vacías en columnas requeridas     | OBS-008    |
| EC-OBS-11 | Importación de archivo Excel protegido con contraseña                     | OBS-008    |
| EC-OBS-12 | Exportación con conjunto de resultados vacío                              | OBS-009    |
| EC-OBS-13 | Importación con 100% de registros duplicados: preview muestra todas marcadas, usuario decide | OBS-008    |

---

## Requirements

### Functional Requirements

**FR-OBS-001: Filtro por año y rol**
El sistema debe listar observaciones filtradas por año. Si el usuario es "Registrador", solo debe mostrar observaciones de establecimientos asignados a ese usuario. Si el usuario es "Supervisor", debe mostrar todas las observaciones. El listado debe estar paginado a 50 registros por página con paginación numerada.

**FR-OBS-002: Consulta por ID**
El sistema debe retornar una observación específica por su ID incluyendo todos sus campos y su historial de cambios.

**FR-OBS-003: Historial de estados**
El sistema debe mantener un registro histórico de todos los cambios de estado de cada observación, incluyendo estado anterior, estado nuevo, usuario que realizó el cambio y comentario opcional.

**FR-OBS-004: Estadísticas agregadas**
El sistema debe proporcionar estadísticas agrupadas por estado, mes y tipo_error.

**FR-OBS-005: Creación con validación de asignación**
El sistema debe permitir la creación de observaciones solo a usuarios con rol "Registrador", validando que el establecimiento y el mes estén asignados al usuario.

**FR-OBS-006: Actualización restringida por rol y estado**
El sistema debe permitir la actualización de observaciones bajo las siguientes condiciones:
- Registrador: solo observaciones propias en estado "pendiente"
- Supervisor: cualquier observación sin restricción de estado

**FR-OBS-007: Eliminación exclusiva para Supervisor**
El sistema debe permitir la eliminación de observaciones solo a usuarios con rol "Supervisor". Existen dos mecanismos:
- DELETE directo en `api/observaciones.php`: eliminación física inmediata (sin papelera).

- Acción "eliminar" en supervisión (`api/supervision.php?action=delete`): soft delete hacia papelera (observaciones_eliminadas).
**FR-OBS-008: Importación desde Excel con previsualización**
El sistema debe soportar la importación masiva de observaciones desde archivos Excel, mostrando una vista previa antes de confirmar la operación.

**FR-OBS-009: Exportación en múltiples formatos**
El sistema debe permitir exportar observaciones en formatos Excel, PDF y CSV.

**FR-OBS-010: Validación de campos obligatorios**
El sistema debe validar que los campos mes, establecimiento_id, codigo_serie, tipo_error, detalle_observacion, plazo_entrega y usa_validador sean proporcionados. codigo_hoja es obligatorio excepto cuando tipo_error es "S/OBSERVACION".

**FR-OBS-011: Validación de series soportadas**
El sistema debe validar que codigo_serie sea uno de los valores permitidos: SERIE A, BS, BM, P, ANEXO, D.

### Key Entities

**observaciones**
| Campo                   | Tipo         | Descripción                                               |
|-------------------------|--------------|-----------------------------------------------------------|
| id                      | INT (PK)     | Identificador único de la observación                     |
| usuario_registro_id     | INT (FK)     | Referencia al usuario que registró la observación         |
| establecimiento_id      | INT          | Identificador del establecimiento                         |
| comuna_id               | INT          | Identificador de la comuna                                |
| anio                    | INT          | Año de la observación                                     |
| mes                     | INT          | Mes de la observación (1-12)                              |
| codigo_serie            | VARCHAR      | Código de la serie (SERIE A, BS, BM, P, ANEXO, D)        |
| codigo_hoja             | VARCHAR      | Código de hoja (nullable si tipo_error = S/OBSERVACION)   |
| tipo_error              | VARCHAR      | Tipo de error (S/OBSERVACION, ERROR, REVISAR, F/PLAZO)   |
| detalle_observacion     | TEXT         | Descripción detallada de la observación                   |
| plazo_entrega           | DATE         | Fecha límite de entrega                                   |
| anio_rem                | INT          | Año del REM asociado                                      |
| mes_rem                 | INT          | Mes del REM asociado                                      |
| estado_actual           | VARCHAR      | Estado actual (pendiente, aprobado, error, rechazado)     |
| clasificacion           | VARCHAR      | Clasificación de la observación                           |
| fecha_creacion          | DATETIME     | Fecha de creación (DEFAULT CURRENT_TIMESTAMP)             |
| fecha_actualizacion     | DATETIME     | Fecha de última actualización (ON UPDATE CURRENT_TIMESTAMP) |
| detalle_error           | TEXT         | Detalle del error (para supervisión)                      |
| respuesta_establecimiento | TEXT       | Respuesta del establecimiento                             |
| usuario_registro_id     | INT (FK)     | Usuario registrador que creó la observación               |
| usuario_supervisor_id   | INT (FK)     | Usuario supervisor que revisó la observación              |

**historial_estados**
| Campo           | Tipo       | Descripción                                  |
|-----------------|------------|----------------------------------------------|
| id              | INT (PK)   | Identificador único del registro histórico   |
| observacion_id  | INT (FK)   | Referencia a la observación modificada       |
| usuario_id      | INT (FK)   | Referencia al usuario que realizó el cambio  |
| estado_anterior | VARCHAR    | Estado previo al cambio                      |
| estado_nuevo    | VARCHAR    | Estado después del cambio                    |
| fecha_creacion  | DATETIME   | Fecha de creación (DEFAULT CURRENT_TIMESTAMP) |
| usuario_id      | INT (FK)   | Usuario que realizó el cambio                |
| comentario      | TEXT       | Comentario opcional sobre el cambio          |
| fecha           | TIMESTAMP  | Fecha y hora del cambio                      |

---

## Success Criteria

| #   | Criterio                                                                 | Verificación                                                       |
|-----|--------------------------------------------------------------------------|--------------------------------------------------------------------|
| SC-OBS-01 | Las observaciones se listan en menos de 2 segundos para hasta 10,000 registros | Prueba de performance con base de datos poblada                    |
| SC-OBS-02 | La importación desde Excel procesa correctamente archivos de hasta 5,000 filas | Prueba de carga con archivo de 5,000 registros                     |
| SC-OBS-03 | El sistema valida correctamente los permisos de cada operación según el rol | Suite de pruebas de autorización cubriendo todos los escenarios    |
| SC-OBS-04 | La creación falla si el registrador no tiene asignación para el establecimiento y mes indicados | Prueba con usuario sin asignación                                  |
| SC-OBS-05 | La exportación genera archivos descargables en los 3 formatos solicitados | Prueba de descarga para cada formato                               |
| SC-OBS-06 | El historial de cambios se registra automáticamente en cada modificación de estado | Prueba de integración que verifica la tabla historial_estados      |

---

## Clarifications

### Session 2026-06-01

- Q: ¿La eliminación es física o soft delete? → A: Ambas conviven. DELETE en `api/observaciones.php` es físico. La acción "eliminar" en supervisión usa soft delete hacia papelera. Exclusivo para Supervisor.
- Q: ¿Cómo se manejan duplicados en importación? → A: Se detectan (misma serie+hoja+establecimiento+mes+tipo_error), se marcan en preview con advertencia, y el usuario decide si importarlas o saltarlas.
- Q: ¿El listado de observaciones debe tener paginación? → A: Sí, 50 registros por página con paginación numerada.
- Q: ¿Estrategia para actualización concurrente? → A: Last-write-wins. Cada cambio se registra en historial con timestamp para auditoría.
- Q: ¿Formato de respuesta de error de la API? → A: `{"success": false, "error": "mensaje", "code": 400}` — estructura plana y consistente en todo el sistema.

---

## Assumptions

| #   | Supuesto                                                                   |
|-----|---------------------------------------------------------------------------|
| AS-OBS-01 | Los roles de usuario (Registrador, Supervisor) se validan mediante token JWT o sesión |
| AS-OBS-02 | La asignación de establecimientos por mes y año existe previamente en el sistema |
| AS-OBS-03 | El archivo Excel de importación tiene extensión .xlsx o .xls               |
| AS-OBS-04 | Los formatos de exportación no requieren diseño personalizado por cliente  |
| AS-OBS-05 | El campo plazo_entrega se ingresa en formato YYYY-MM-DD                    |
| AS-OBS-06 | El estado inicial de una observación recién creada es "pendiente"          |
| AS-OBS-07 | Los valores de tipo_error son únicamente: S/OBSERVACION, ERROR, REVISAR, F/PLAZO |
| AS-OBS-08 | Los usuarios existen previamente en el sistema y tienen un rol asignado    |
| AS-OBS-09 | Conviven dos mecanismos: DELETE directo en `api/observaciones.php` es físico (hard delete, bypass papelera). La acción "eliminar" en supervisión usa soft delete (papelera). Ambas exclusivas para Supervisor |
