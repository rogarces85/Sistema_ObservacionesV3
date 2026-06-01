# Módulo de Establecimientos y Referentes (MOD-LOC)

> **Módulo ID:** MOD-LOC
> **Usuario principal:** Supervisor (CRUD + toggle); Registrador (solo lectura vía filtros)
> **Propósito:** Gestionar el catálogo de comunas y establecimientos de salud del SSO, manteniendo actualizada la base donde se registran observaciones.

---

## 1. User Scenarios & Testing

### HU-LOC-01: Listado y filtro de establecimientos
**Prioridad:** P1

**Historia:** Como Supervisor, quiero ver el listado completo de establecimientos con su estado activo/inactivo y estadísticas, para tener visibilidad del catálogo.

**Escenario feliz:**
```gherkin
Dado que existen 93 establecimientos en el sistema
Cuando el supervisor carga la página de establecimientos
Entonces ve una tabla con todos los establecimientos
  Y cada fila muestra: código, nombre, comuna, estado (activo/inactivo)
  Y ve el total "93 establecimientos"
  Y ve el desglose "80 activos, 13 inactivos"
```

**Escenario: Filtro por comuna**
```gherkin
Dado que el supervisor selecciona la comuna "Osorno" en el filtro
Cuando se actualiza la lista
Entonces solo se muestran los establecimientos pertenecientes a "Osorno"
  Y el contador refleja el total filtrado
```

**Edge cases:**
- Filtro sin resultados → mensaje "No se encontraron establecimientos"
- Comuna sin establecimientos activos → mostrar 0 en el listado
- Búsqueda por nombre parcial debe funcionar (backend LIKE)
- Cambio rápido de filtros no debe producir race conditions
- Listado ordenado por código DEIS ascendente por defecto; columnas sorteables por clic en encabezado

---

### HU-LOC-02: Creación de establecimiento
**Prioridad:** P1

**Historia:** Como Supervisor, quiero crear un nuevo establecimiento con código, nombre, nombre corto y comuna, para incorporar nuevos centros de salud al sistema.

**Escenario feliz:**
```gherkin
Dado que el supervisor abre el modal de creación
  Y completa código "12345", nombre "CESFAM Nuevo", nombre corto "CESFAM N.", comuna "Osorno"
Cuando confirma la creación
Entonces el establecimiento se guarda con activo = 1 por defecto
  Y aparece en el listado
  Y se muestra mensaje "Establecimiento creado exitosamente"
```

**Escenario: Código duplicado**
```gherkin
Dado que ya existe un establecimiento con código "12345"
Cuando el supervisor intenta crear otro con el mismo código
Entonces el sistema rechaza la operación
  Y muestra "El código de establecimiento ya existe"
```

**Escenario: Campos obligatorios vacíos**
```gherkin
Dado que el supervisor intenta crear sin completar "nombre"
Cuando envía el formulario
Entonces el sistema muestra validación "El nombre es obligatorio"
  Y no se crea el registro
```

**Edge cases:**
- Código con caracteres especiales → validar formato alfanumérico
- Nombre muy largo (>255 caracteres) → truncar o rechazar
- Comuna seleccionada no existe (ID inválido) → error FK
- Crear establecimiento con nombre corto igual a nombre completo → permitido
- nombre_corto vacío → permitido, se usa nombre completo truncado como fallback

---

### HU-LOC-03: Actualización de establecimiento
**Prioridad:** P2

**Historia:** Como Supervisor, quiero editar los datos de un establecimiento existente, para corregir información desactualizada.

**Escenario feliz:**
```gherkin
Dado que existe el establecimiento "CESFAM Antiguo" con código "11111"
Cuando el supervisor edita el nombre a "CESFAM Renovado"
Entonces el sistema actualiza el nombre
  Y el listado refleja el cambio
```

**Escenario: Cambio de código a uno ya existente**
```gherkin
Dado que existe "CESFAM A" con código "11111" y "CESFAM B" con código "22222"
Cuando el supervisor intenta cambiar el código de "CESFAM B" a "11111"
Entonces el sistema rechaza la operación
  Y muestra "El código de establecimiento ya está en uso"
```

**Edge cases:**
- Editar establecimiento y no cambiar ningún campo → operación permitida sin cambios
- Cambiar comuna a una inexistente → error FK
- Establecimiento inactivo: se puede editar nombre, nombre_corto y comuna; NO se puede cambiar código ni toggle activo/inactivo

---

### HU-LOC-04: Activar/desactivar establecimiento
**Prioridad:** P2

**Historia:** Como Supervisor, quiero activar o desactivar establecimientos, para inhabilitar centros que ya no están operativos sin perder su historial.

**Escenario feliz:**
```gherkin
Dado que el establecimiento "CESFAM Cerrado" está activo
Cuando el supervisor hace clic en "Desactivar"
Entonces el sistema cambia activo a 0
  Y el establecimiento aparece como inactivo en el listado
  Y ya no está disponible para nuevas asignaciones
```

**Escenario: Reactivar establecimiento**
```gherkin
Dado que el establecimiento "CESFAM Cerrado" está inactivo
Cuando el supervisor hace clic en "Activar"
Entonces el sistema cambia activo a 1
  Y el establecimiento vuelve a estar disponible
```

**Edge cases:**
- Desactivar establecimiento con asignaciones activas → las asignaciones se mantienen (histórico), no aparece en nuevas selecciones, y se bloquean nuevas observaciones con error "Establecimiento inactivo"
- Desactivar establecimiento con referentes → referentes se mantienen, no se afectan
- Toggle rápido (doble clic) → prevenir doble envío con debounce

---

### HU-LOC-05: Gestión de referentes
**Prioridad:** P2

**Historia:** Como Supervisor, quiero gestionar los referentes (contactos) de cada establecimiento, para mantener una lista de personas de contacto por cargo.

**Escenario feliz:**
```gherkin
Dado que el supervisor abre la gestión de referentes de "Hospital Base"
Cuando agrega un referente con:
  | cargo | Encargado Estadísticas |
  | nombre | Juan Pérez |
  | teléfono | 912345678 |
  | email | juan@hospital.cl |
Entonces el referente se guarda como activo
  Y aparece en la lista ordenada por cargo
```

**Escenario: Editar referente existente**
```gherkin
Dado que existe un referente "Juan Pérez" para "Hospital Base"
Cuando el supervisor cambia su teléfono a "987654321"
Entonces el sistema actualiza el teléfono
  Y el resto de datos permanece igual
```

**Escenario: Desactivar referente**
```gherkin
Dado que "Juan Pérez" es referente activo de "Hospital Base"
Cuando el supervisor lo desactiva
Entonces el referente queda con activo = 0
  Y ya no aparece en la lista principal (se puede filtrar para ver inactivos)
```

**Edge cases:**
- Email con formato inválido → validación en frontend y backend
- Teléfono con caracteres no numéricos → sanitizar o rechazar
- Dos referentes con mismo cargo y nombre → permitido (no hay UK por cargo+nombre)
- Eliminar (hard delete) vs desactivar → solo desactivar lógicamente
- Referente sin email → campo opcional
- Orden: "Encargado Estadísticas" aparece antes que "Digitador Estadísticas"

---

### HU-LOC-06: Consulta de establecimientos (Registrador)
**Prioridad:** P3

**Historia:** Como Registrador, quiero consultar los establecimientos disponibles filtrados por comuna, para saber en cuáles puedo registrar observaciones.

**Escenario feliz:**
```gherkin
Dado que un registrador accede al módulo de establecimientos
Cuando selecciona la comuna "Osorno"
Entonces ve solo los establecimientos activos de esa comuna
  Y NO ve la opción de crear, editar o desactivar
```

**Edge cases:**
- Registrador sin establecimientos en su comuna filtrada → lista vacía
- Registrador ve mismo listado que supervisor pero sin botones de acción
- Intentar acceder a endpoints de creación por API → 403 Forbidden

---

## Clarifications

### Session 2026-06-01

- Q: ¿nombre_corto es obligatorio u opcional? → A: Opcional. Si no se provee, se usa nombre completo truncado como fallback visual.
- Q: ¿Se permite editar un establecimiento inactivo? → A: Sí, edición de datos (nombre, nombre_corto, comuna) permitida. Solo el código y el toggle activo/inactivo se bloquean mientras está inactivo.
- Q: ¿Puede un registrador registrar observaciones en un establecimiento desactivado? → A: No. El backend bloquea nuevas observaciones si el establecimiento está inactivo, incluso con asignación previa. Las asignaciones se conservan para histórico.
- Q: ¿Orden por defecto del listado? → A: Por código DEIS ascendente. Las columnas deben ser sorteables por clic en encabezado.
- Q: ¿Existe módulo separado para gestionar comunas? → A: No. Comunas son datos semilla fijos insertados vía migración SQL. No hay UI para CRUD de comunas. Cambios requieren migración.

## 2. Requirements

### Functional Requirements

| ID | Descripción | Relacionado con |
|---|---|---|
| FR-LOC-001 | El sistema debe listar todas las comunas del SSO con su código DEIS. | LOC-001 |
| FR-LOC-002 | El sistema debe listar establecimientos con filtro por `comuna_id` o `comuna_nombre`. | LOC-002 |
| FR-LOC-003 | El sistema debe listar todos los establecimientos incluyendo inactivos (solo supervisor). | LOC-003 |
| FR-LOC-004 | El sistema debe permitir crear un establecimiento con: código, nombre, nombre corto y comuna. | LOC-004 |
| FR-LOC-005 | El sistema debe validar que `codigo_establecimiento` sea único antes de crear o actualizar. | LOC-004, LOC-005 |
| FR-LOC-006 | El sistema debe permitir actualizar nombre, nombre corto y comuna de un establecimiento. | LOC-005 |
| FR-LOC-007 | El sistema debe permitir activar/desactivar un establecimiento (toggle). | LOC-006 |
| FR-LOC-008 | Al desactivar un establecimiento: las asignaciones existentes se conservan (histórico), el establecimiento no aparece en nuevas selecciones, y el backend bloquea nuevas observaciones incluso para asignaciones previas. | LOC-006 |
| FR-LOC-009 | El sistema debe permitir CRUD completo de referentes por establecimiento (cargo, nombre, teléfono, email). | — |
| FR-LOC-010 | Los referentes deben listarse ordenados por cargo según jerarquía definida (Encargado Estadísticas → Digitador Estadísticas). | — |
| FR-LOC-011 | El sistema debe validar formato de email y teléfono en frontend y backend para referentes. | — |
| FR-LOC-012 | Los registradores solo pueden ver establecimientos activos mediante filtros, sin acceso a operaciones de escritura. | LOC-002 |
| FR-LOC-013 | El sistema debe mostrar estadísticas: total establecimientos, activos e inactivos. | LOC-002 |
| FR-LOC-014 | El sistema debe rechazar con HTTP 403 cualquier intento de un registrador de acceder a endpoints de escritura. | LOC-004, LOC-005, LOC-006 |

### Key Entities

**`comunas`**
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT (PK) | Identificador único |
| codigo_comuna | VARCHAR | Código DEIS de la comuna |
| nombre | VARCHAR | Nombre de la comuna |
| fecha_creacion | DATETIME | Fecha de creación (DEFAULT CURRENT_TIMESTAMP) |
| fecha_actualizacion | DATETIME | Fecha de última actualización (ON UPDATE CURRENT_TIMESTAMP) |

**`establecimientos`**
| Campo | Tipo | Descripción |
|---|---|---|
| id | INT (PK) | Identificador único |
| codigo_establecimiento | VARCHAR (UNIQUE) | Código del establecimiento |
| nombre | VARCHAR | Nombre completo |
| nombre_corto | VARCHAR | Nombre abreviado (opcional; si es NULL se usa nombre truncado) |
| comuna_id | INT (FK) | Referencia a la comuna |
| activo | TINYINT(1) | 1=activo, 0=inactivo |
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
| fecha_creacion | DATETIME | Fecha de creación (DEFAULT CURRENT_TIMESTAMP) |
| fecha_actualizacion | DATETIME | Fecha de última actualización (ON UPDATE CURRENT_TIMESTAMP) |

---

## 3. Success Criteria

1. El catálogo contiene exactamente 7 comunas y 93 establecimientos coincidiendo con el DEIS del SSO.
2. Un supervisor puede crear, editar, activar/desactivar un establecimiento en menos de 3 clics desde el listado, sin recargar la página.
3. La validación de código duplicado rechaza la operación en < 300ms y muestra el mensaje de error en el modal.
4. El toggle activo/inactivo se refleja en la UI en menos de 500ms sin recargar la página y persiste al recargar.
5. Los referentes se muestran siempre ordenados por cargo (Encargado Estadísticas → Digitador Estadísticas) independientemente del orden de creación.
6. Un registrador no puede acceder a ninguna operación de escritura; cualquier intento directo por API recibe 403.
7. Los filtros por comuna y nombre responden en < 1s para 93 registros.
8. La desactivación de un establecimiento no afecta asignaciones ni referentes existentes (solo oculta el establecimiento en selecciones futuras).

---

## 4. Assumptions

1. Existen 7 comunas como datos semilla fijos insertados vía migración SQL (códigos DEIS). No existe UI para CRUD de comunas en ningún módulo. Agregar o modificar comunas requiere migración SQL.
2. Existen 93 establecimientos de salud iniciales; el catálogo puede crecer pero no disminuir (los establecimientos se desactivan, no se eliminan).
3. No se permite eliminar (hard delete) establecimientos ni comunas; solo desactivación lógica.
4. Los códigos `codigo_establecimiento` son únicos a nivel global (no por comuna).
5. El orden de cargos para referentes es: "Encargado Estadísticas" primero, "Digitador Estadísticas" segundo; cualquier otro cargo se ordena alfabéticamente después de estos dos.
6. Los registradores ven el mismo listado que el supervisor pero sin botones de acción y solo con establecimientos activos.
7. El email del referente es opcional; el teléfono es opcional pero al menos uno debe estar presente.
8. La comuna_id es obligatoria y debe existir en la tabla `comunas` (integridad referencial).
9. Los cambios en establecimientos no afectan retrospectivamente observaciones ya registradas; solo afectan selecciones futuras.
