# Módulo de Asignaciones (MOD-ASN)

> **Módulo ID:** MOD-ASN
> **Usuario principal:** Supervisor
> **Propósito:** Controlar qué registradores pueden registrar observaciones en cada establecimiento y mes, mediante asignaciones anuales y temporales.

---

## 1. User Scenarios & Testing

### HU-ASN-01: Asignación anual a un registrador
**Prioridad:** P1

**Historia:** Como Supervisor, quiero asignar un establecimiento a un registrador para el año completo o meses específicos, para definir su responsabilidad base.

**Escenario feliz:**
```gherkin
Dado que el supervisor está en la vista de asignaciones
  Y selecciona el año "2026"
  Y elige al registrador "Juan Pérez"
Cuando asigna el establecimiento "Hospital Base" con meses "ALL"
Entonces el sistema crea una asignación anual
  Y el establecimiento aparece en la lista del registrador
  Y los 12 meses quedan marcados como asignados
```

**Escenario: Asignación con meses específicos**
```gherkin
Dado que el supervisor selecciona meses "1,2,3" para el establecimiento "CESFAM Norte"
Cuando confirma la asignación
Entonces el sistema crea la asignación solo para los meses 1, 2 y 3
  Y los meses 4-12 permanecen sin asignar para ese registrador
```

**Escenario: Meses ya asignados (merge)**
```gherkin
Dado que existe una asignación anual para "CESFAM Norte" con meses "1,2,3"
Cuando el supervisor asigna el mismo establecimiento con meses "3,4,5"
Entonces el sistema fusiona los meses resultando en "1,2,3,4,5"
  Y no se crea un duplicado
```

**Edge cases:**
- Asignar establecimiento ya asignado con ALL → se sobrescribe completo
- Año sin establecimientos disponibles
- Registrador sin asignaciones previas muestra lista vacía
- Mes fuera de rango (0, 13) debe ser rechazado
- Mes duplicado en la selección debe normalizarse

---

### HU-ASN-02: Reasignación temporal
**Prioridad:** P1

**Historia:** Como Supervisor, quiero reasignar temporalmente un establecimiento a otro registrador para meses específicos, para cubrir ausencias o redistribuir carga.

**Escenario feliz:**
```gherkin
Dado que "CESFAM Norte" está asignado anualmente a "Juan Pérez"
Cuando el supervisor asigna temporalmente el mismo establecimiento a "María López" para el mes "6"
Entonces el sistema crea una asignación temporal
  Y "María López" puede registrar observaciones en el mes 6
  Y "Juan Pérez" conserva su asignación anual para los meses restantes
```

**Escenario: Validación de solapamiento entre temporales**
```gherkin
Dado que existe una asignación temporal para "María López" en mes "6"
Cuando el supervisor intenta asignar otro temporal a "Pedro Ruiz" para el mes "6"
Entonces el sistema rechaza la operación
  Y muestra el mensaje "El mes 6 ya tiene una asignación temporal activa"
```

**Escenario: Temporal NO solapa con anual (válido)**
```gherkin
Dado que "Juan Pérez" tiene asignación anual en "CESFAM Norte" para todos los meses
Cuando el supervisor asigna un temporal a "María López" para el mes "6"
Entonces el sistema permite la creación
  Y ambos registradores tienen asignación (anual y temporal conviven)
```

**Edge cases:**
- Temporal que abarca meses exactos de un anual existente
- Múltiples temporales en distintos meses para un mismo establecimiento
- Temporal fuera del año vigente
- Eliminar temporal y que el anual recupere la prioridad por defecto
- No se puede asignar un temporal a un registrador que ya tiene anual en esos meses (el temporal prima, pero no hay impedimento técnico)

---

### HU-ASN-03: Asignación masiva
**Prioridad:** P2

**Historia:** Como Supervisor, quiero asignar múltiples establecimientos a uno o varios registradores en una sola operación, para agilizar la configuración anual.

**Escenario feliz:**
```gherkin
Dado que el supervisor selecciona 5 establecimientos en el árbol
  Y elige al registrador "Juan Pérez"
  Y marca "Todos los meses"
Cuando confirma la asignación masiva
Entonces el sistema crea 5 asignaciones anuales en una transacción
  Y muestra resumen "5 establecimientos asignados correctamente"
```

**Edge cases:**
- Lote mixto: algunos ya asignados (merge) y otros nuevos
- Selección vacía de establecimientos → deshabilitar botón
- Error en mitad del lote → rollback completo (transaccional)
- Superar límite de tiempo de ejecución en lotes muy grandes

---

### HU-ASN-04: Copia de asignaciones entre años
**Prioridad:** P2

**Historia:** Como Supervisor, quiero copiar las asignaciones de un año anterior al año actual, para no tener que reasignar manualmente cuando la configuración es similar.

**Escenario feliz:**
```gherkin
Dado que existen asignaciones en el año "2025"
Cuando el supervisor selecciona "Copiar desde 2025" hacia "2026"
Entonces el sistema duplica todas las asignaciones de 2025 en 2026
  Y muestra "Asignaciones copiadas exitosamente"
```

**Escenario: Año destino ya tiene asignaciones**
```gherkin
Dado que el año "2026" ya tiene asignaciones existentes
Cuando el supervisor intenta copiar desde "2025"
Entonces el sistema muestra advertencia "El año 2026 ya contiene asignaciones. ¿Desea sobrescribir?"
  Y si confirma, reemplaza todas las asignaciones de 2026 con las de 2025
```

**Edge cases:**
- Año origen sin asignaciones → botón deshabilitado con tooltip
- Año destino = año origen → operación inválida
- Establecimientos que ya no existen en año destino → se omiten con advertencia
- Copia parcial (solo anuales, no temporales)

---

### HU-ASN-05: Eliminación de asignaciones
**Prioridad:** P2

**Historia:** Como Supervisor, quiero remover la asignación de un registrador de un establecimiento, total o parcialmente por meses, para corregir errores o reconfigurar.

**Escenario feliz:**
```gherkin
Dado que "Juan Pérez" tiene asignación anual en "CESFAM Norte" con meses "1,2,3,4,5"
Cuando el supervisor remueve los meses "4,5"
Entonces la asignación se actualiza a meses "1,2,3"
  Y el registrador ya no tiene acceso para los meses 4 y 5
```

**Escenario: Eliminación completa**
```gherkin
Dado que existe una asignación anual para "Juan Pérez" en "CESFAM Norte"
Cuando el supervisor remueve la asignación completa
Entonces el sistema elimina el registro
  Y el establecimiento ya no aparece en la lista del registrador
```

**Edge cases:**
- Remover todos los meses → eliminar registro automáticamente
- Remover mes de asignación temporal → eliminar el temporal si era su único mes
- Remover mes que no está asignado → operación ignorada (no error)
- No se puede remover asignación de un año ya cerrado

---

### HU-ASN-06: Gestión de registradores
**Prioridad:** P3

**Historia:** Como Supervisor, quiero ver la lista de registradores activos y sus estadísticas de asignación, para tener visibilidad de la carga de trabajo.

**Escenario feliz:**
```gherkin
Dado que hay 5 registradores activos en el sistema
Cuando el supervisor carga la página de asignaciones
Entonces ve una tarjeta por cada registrador
  Y cada tarjeta muestra el nombre y total de establecimientos asignados
```

**Edge cases:**
- Registrador sin asignaciones → tarjeta con 0 establecimientos
- Registrador inactivo → no aparece en la lista
- Mostrar distintivo visual si tiene temporales activos

---

### HU-ASN-07: Gestión de referentes por establecimiento
**Prioridad:** P3

**Historia:** Como Supervisor, quiero gestionar los referentes (contactos) de cada establecimiento directamente desde la vista de asignaciones, para mantener los datos de contacto actualizados sin cambiar de módulo.

**Escenario feliz:**
```gherkin
Dado que el supervisor está viendo los detalles de "Hospital Base"
Cuando agrega un referente con cargo "Encargado Estadísticas", nombre "Pedro", teléfono "912345678"
Entonces el referente se guarda asociado al establecimiento
  Y aparece en la lista de referentes del establecimiento
```

**Escenario: Ordenamiento por cargo**
```gherkin
Dado que el establecimiento tiene referentes con cargos "Digitador Estadísticas" y "Encargado Estadísticas"
Cuando se muestra la lista
Entonces "Encargado Estadísticas" aparece primero
  Y "Digitador Estadísticas" aparece segundo
```

**Edge cases:**
- Teléfono inválido (no numérico o muy corto) → validación en frontend
- Email con formato incorrecto → validación
- Referente duplicado (mismo cargo y nombre) → permitir, pero advertir
- Reactivar referente inactivo desde el mismo modal

---

## Clarifications

- Q: ¿Fusión cuando ALL existe vs asignación específica? → A: ALL + lista específica → la nueva lista reemplaza. La asignación explícita del supervisor tiene prioridad sobre la anterior. ALL + ALL → sin cambios. Lista + ALL → se actualiza a ALL.
- Q: ¿Transaccionalidad en asignación masiva? → A: Mantener transaccional. Asignaciones son setup anual (5-50 registros), no bulk data-entry como supervisión.
- Q: ¿Copia entre años incluye temporales? → A: Sí, ambos tipos. Es duplicación completa del setup del año. El supervisor puede limpiar temporales manualmente después si lo desea.
- Q: ¿Temporal para mismo registrador que ya tiene anual en esos meses? → A: Sí se permite. El temporal prima para permisos de registro, ambas conviven en BD.
- Q: Formato canónico de meses en VARCHAR. → A: Enteros 1-12 orden ascendente, sin espacios, separados por coma. Ej: `"1,2,3"`. ALL como texto `"ALL"`.

## 2. Requirements

### Functional Requirements

| ID | Descripción | Relacionado con |
|---|---|---|
| FR-ASN-001 | El sistema debe permitir asignar un establecimiento a un registrador para un año completo (ALL) o meses específicos (1-12). | ASN-005 |
| FR-ASN-002 | El sistema debe soportar dos tipos de asignación: `anual` (base) y `temporal` (con prioridad sobre anual). | ASN-005 |
| FR-ASN-003 | Si ya existe una asignación del mismo tipo para el mismo registrador-año-establecimiento, los meses deben fusionarse (no duplicar). | ASN-005 |
| FR-ASN-004 | El sistema debe validar que dos asignaciones temporales no se solapen en el mismo mes para el mismo establecimiento. | ASN-005 |
| FR-ASN-005 | El sistema debe permitir asignaciones temporales aunque exista una asignación anual activa en los mismos meses (no hay solapamiento). | ASN-005 |
| FR-ASN-006 | El sistema debe permitir la asignación masiva de múltiples establecimientos a uno o más registradores en una sola operación transaccional. | ASN-006 |
| FR-ASN-007 | El sistema debe permitir copiar todas las asignaciones de un año origen a un año destino. | ASN-008 |
| FR-ASN-008 | Al copiar asignaciones, si el año destino ya tiene datos, el sistema debe solicitar confirmación antes de sobrescribir. | ASN-008 |
| FR-ASN-009 | El sistema debe permitir remover una asignación completa o parcialmente por meses. | ASN-007 |
| FR-ASN-010 | Si al remover meses no queda ningún mes asignado, el registro debe eliminarse automáticamente. | ASN-007 |
| FR-ASN-011 | El sistema debe listar los registradores activos con estadísticas de asignación (total establecimientos, meses cubiertos). | ASN-001, ASN-002 |
| FR-ASN-012 | El sistema debe listar los establecimientos indicando si están asignados o no, y a quién. | ASN-003 |
| FR-ASN-013 | El sistema debe permitir gestionar (CRUD) referentes por establecimiento desde la vista de asignaciones. | — |
| FR-ASN-014 | Los referentes deben ordenarse por cargo: primero "Encargado Estadísticas", luego "Digitador Estadísticas". | — |
| FR-ASN-015 | El sistema debe mostrar los registradores con temporales activos y su respectivo titular anual. | ASN-009 |

### Key Entities

**`asignaciones_establecimientos`**
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT (PK) | Identificador único |
| usuario_id | INT (FK) | Referencia al registrador (users) |
| establecimiento_id | INT (FK) | Referencia al establecimiento |
| anio | INT | Año de la asignación |
| meses | VARCHAR | Meses asignados ("ALL" o lista "1,2,3...12") |
| tipo_asignacion | ENUM('anual','temporal') | Tipo de asignación |
| fecha_creacion | DATETIME | Fecha de creación (DEFAULT CURRENT_TIMESTAMP) |
| fecha_actualizacion | DATETIME | Fecha de última actualización (ON UPDATE CURRENT_TIMESTAMP) |

**`referentes_establecimientos`**
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT (PK) | Identificador único |
| establecimiento_id | INT (FK) | Referencia al establecimiento |
| cargo | VARCHAR | Cargo del referente |
| nombre | VARCHAR | Nombre completo |
| telefono | VARCHAR | Teléfono de contacto |
| email | VARCHAR | Correo electrónico |
| activo | TINYINT(1) | 1=activo, 0=inactivo |
| fecha_creacion | DATETIME | Fecha de creación (DEFAULT CURRENT_TIMESTAMP) |
| fecha_actualizacion | DATETIME | Fecha de última actualización (ON UPDATE CURRENT_TIMESTAMP) |

---

## 3. Success Criteria

1. Un supervisor puede completar el ciclo completo de asignación anual para 93 establecimientos en menos de 30 minutos usando asignación masiva y copia entre años.
2. La validación de solapamiento entre temporales rechaza asignaciones conflictivas en < 500ms.
3. La copia de asignaciones entre años replica correctamente el 100% de las asignaciones origen, omitiendo establecimientos eliminados con notificación clara.
4. La fusión de meses en asignaciones existentes nunca produce duplicados ni pierde meses previamente asignados.
5. La eliminación parcial de meses reduce correctamente el conjunto y elimina el registro si el resultado es vacío.
6. Los referentes se muestran siempre ordenados por cargo según la jerarquía definida.
7. La interfaz permite realizar todas las operaciones sin recargar la página (usando AJAX), con retroalimentación visual inmediata.

---

## 4. Assumptions

1. Solo el usuario con rol Supervisor tiene acceso a este módulo; los registradores no tienen ninguna vista de asignaciones.
2. El año de trabajo siempre es un número de 4 dígitos; no se soportan años negativos ni menores a 2000.
3. "ALL" como valor de meses significa los 12 meses del año; al fusionar, "ALL" prevalece sobre cualquier lista específica.
4. Una asignación temporal tiene prioridad sobre la anual para efectos de permisos de registro, pero ambas conviven en la base de datos.
5. Los registradores son usuarios del sistema con un rol específico (rol_id); la lista de activos se obtiene del filtro de rol + activo.
6. La operación de copia entre años es una duplicación exacta (incluyendo tipo de asignación y meses), no una referencia.
7. El orden de cargos para referentes es fijo: "Encargado Estadísticas" → "Digitador Estadísticas"; cualquier otro cargo se ordena alfabéticamente después.
8. La transacción de asignación masiva es atómica: si falla un elemento, falla todo el lote.
9. Los meses se almacenan y manipulan como números enteros 1-12; el sistema normaliza valores duplicados o fuera de rango antes de persistir.
