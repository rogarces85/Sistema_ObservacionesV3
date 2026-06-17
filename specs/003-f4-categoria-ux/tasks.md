# Tasks: Cerrar Gap F4 — UX de Carga y Error por Categoría

**Input**: Design documents from `/specs/003-f4-categoria-ux/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/ui-estados-carga-error.md, quickstart.md

**Tests**: No se solicitaron pruebas automatizadas ni enfoque TDD; se incluyen tareas de validación manual y linting al final.

**Organization**: Las tareas estan agrupadas por historia de usuario para permitir implementacion y validacion independiente.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Puede ejecutarse en paralelo porque usa archivos distintos o no depende de tareas incompletas.
- **[Story]**: Historia de usuario asociada, solo en fases de historias.
- Todas las tareas incluyen rutas exactas de archivos.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verificar el terreno (archivos existentes, clases Tabler disponibles, iconos) sin cambiar comportamiento todavia.

- [ ] T001 [P] Verificar estructura actual de vistas/reportes.php y la presencia de 5 paneles con data-panel-categoria en views/reportes.php
- [ ] T002 [P] Verificar que Tabler provee .spinner-border de Bootstrap 5 en assets/libs/@tabler/core/dist/css/tabler.css
- [ ] T003 [P] Verificar disponibilidad de tablerIcon('refresh') en includes/icons.php

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Crear las primitivas compartidas (estados, helpers, event delegation) que todas las historias necesitan.

**CRITICAL**: Ninguna historia debe implementarse antes de completar esta fase.

- [ ] T004 [P] Agregar clases BEM .reportes-analytics__estado--loading y .reportes-analytics__estado--error en assets/css/tabler-override.css
- [ ] T005 Refactorizar setEstadoAnalitico en assets/js/reportes.js para soportar el cuarto estado error con role=alert
- [ ] T006 [P] Crear helper setBotonReintentarHabilitado(categoria, habilitado) en assets/js/reportes.js
- [ ] T007 [P] Agregar event delegation para [data-reintentar-categoria] en la inicializacion del modulo Reportes en assets/js/reportes.js

**Checkpoint**: Base lista para implementar las 3 historias de usuario.

---

## Phase 3: User Story 1 - Ver indicador de carga por categoria (Priority: P1) 🎯 MVP

**Goal**: Cada una de las 5 categorias analiticas muestra un spinner HTML con texto "Cargando {nombre_categoria}..." en español mientras se obtienen los datos.

**Independent Test**: Abrir Reportes con un año con datos, aplicar un filtro, y verificar que cada categoria muestra spinner visible con el nombre de la categoria en español; el spinner desaparece en menos de 100ms tras la respuesta.

### Implementation for User Story 1

- [ ] T008 [US1] Crear setEstadoCargando(categoria) en assets/js/reportes.js que inyecta HTML con .spinner-border y "Cargando {titulo}..." usando CATEGORIAS_ANALITICAS[categoria].titulo
- [ ] T009 [US1] Reemplazar setEstadoAnaliticoTodos('Cargando reportes analíticos...') por loop que llama setEstadoCargando(categoria) para cada categoria en cargarReportesAnaliticos en assets/js/reportes.js
- [ ] T010 [US1] Llamar setEstadoCargando(categoria) en el flujo de cambio de categoria en seleccionarCategoria en assets/js/reportes.js
- [ ] T011 [US1] Validar manualmente que el spinner aparece en menos de 200ms tras aplicar filtro usando quickstart.md pasos 1-3 de specs/003-f4-categoria-ux/quickstart.md

**Checkpoint**: US1 funciona de forma independiente. Spinner visible con nombre de categoria en español.

---

## Phase 4: User Story 2 - Reintentar carga fallida por categoria (Priority: P1)

**Goal**: Cuando una categoria falla, el sistema muestra un mensaje de error con boton "Reintentar" funcional que reintenta la consulta de ESA categoria especifica sin afectar las demas y preservando los filtros activos.

**Independent Test**: Forzar error en una categoria, verificar que aparece boton "Reintentar"; al hacer click, la consulta se re-ejecuta con los mismos filtros y se rehabilita el boton al terminar; las demas categorias mantienen su contenido.

### Implementation for User Story 2

- [ ] T012 [P] [US2] Crear setEstadoError(categoria, mensaje) en assets/js/reportes.js que inyecta HTML con mensaje + boton Reintentar (icono ti-refresh) y data-reintentar-categoria
- [ ] T013 [US2] Crear recargarCategoria(categoria) en assets/js/reportes.js que llama setEstadoCargando, ejecuta fetchAPI con categoria especifica y filtros activos, y maneja exito/error
- [ ] T014 [US2] Reemplazar setEstadoAnalitico(categoria, 'No fue posible cargar...', 'danger') por setEstadoError(categoria) en el catch de cargarReportesAnaliticos en assets/js/reportes.js
- [ ] T015 [US2] Llamar setBotonReintentarHabilitado(categoria, false) al inicio y setBotonReintentarHabilitado(categoria, true) al final de recargarCategoria en assets/js/reportes.js
- [ ] T016 [US2] Validar manualmente que el boton Reintentar ejecuta la consulta y se deshabilita durante la misma usando quickstart.md pasos 6-12 de specs/003-f4-categoria-ux/quickstart.md

**Checkpoint**: US2 funciona de forma independiente. Reintento funcional con aislamiento de errores entre categorias.

---

## Phase 5: User Story 3 - Mensaje de error claro y recuperable (Priority: P2)

**Goal**: El mensaje de error esta en español, en lenguaje natural (no tecnico), e incluye el boton Reintentar.

**Independent Test**: Forzar 2-3 tipos diferentes de error (timeout, 500, JSON invalido) y verificar que el mensaje mostrado es siempre "No fue posible cargar esta categoria." en lenguaje natural, sin stack traces ni codigos HTTP visibles.

### Implementation for User Story 3

- [ ] T017 [US3] Definir mensaje por defecto "No fue posible cargar esta categoria." como parametro default de setEstadoError en assets/js/reportes.js
- [ ] T018 [US3] Validar manualmente que el mensaje esta en español y no muestra terminos tecnicos (error, JSON, HTTP) usando quickstart.md pasos 6-10 de specs/003-f4-categoria-ux/quickstart.md

**Checkpoint**: US3 funciona. Mensaje en lenguaje natural en español.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Cierre de calidad, documentacion, accesibilidad y validacion transversal.

- [ ] T019 [P] Ejecutar php -l sobre views/reportes.php
- [ ] T020 [P] Ejecutar node --check sobre assets/js/reportes.js
- [ ] T021 [P] Verificar que no existan estilos inline nuevos en views/reportes.php usando Select-String 'style='
- [ ] T022 [P] Verificar que las clases BEM reportes-analytics__* esten en assets/css/tabler-override.css usando Select-String
- [ ] T023 [P] Actualizar mockups en docs/manuales/reportes-exportacion.md con los nuevos estados de carga (spinner) y error (mensaje + boton Reintentar) para las 5 categorias
- [ ] T024 Validar manualmente el flujo completo de extremo a extremo con supervisor y registrador usando specs/003-f4-categoria-ux/quickstart.md (17 pasos)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Sin dependencias.
- **Foundational (Phase 2)**: Depende de Setup y bloquea todas las historias.
- **US1 (Phase 3)**: Depende de Foundational; entrega el MVP (spinner visible).
- **US2 (Phase 4)**: Depende de US1 (reusa setEstadoCargando en recargarCategoria).
- **US3 (Phase 5)**: Depende de US2 (extiende setEstadoError con texto validado).
- **Polish (Phase 6)**: Depende de las 3 historias implementadas.

### User Story Dependencies

- **US1 (P1)**: Puede iniciar tras Foundational y no depende de otras historias. Es el MVP.
- **US2 (P1)**: Requiere US1 porque recargarCategoria reusa setEstadoCargando.
- **US3 (P2)**: Requiere US2 porque valida el texto del mensaje que setEstadoError inyecta.

### Within Each User Story

- Helpers de Phase 2 antes que las historias.
- US1 spinner antes que US2 reintento (recargarCategoria invoca setEstadoCargando).
- US2 setEstadoError antes que US3 validacion de texto.
- Validacion manual al cerrar cada historia.

### Parallel Opportunities

- T001, T002 y T003 pueden ejecutarse en paralelo (distintos archivos).
- T004, T006 y T007 pueden ejecutarse en paralelo dentro de Foundational.
- T012 puede ejecutarse en paralelo con T013 (distintas funciones JS).
- T019, T020, T021, T022, T023 pueden ejecutarse en paralelo durante el cierre.

---

## Parallel Example: User Story 1

```text
Task: "Crear setEstadoCargando(categoria) en assets/js/reportes.js"
Task: "Reemplazar setEstadoAnaliticoTodos por loop de setEstadoCargando en cargarReportesAnaliticos en assets/js/reportes.js"
```

## Parallel Example: User Story 2

```text
Task: "Crear setEstadoError(categoria, mensaje) en assets/js/reportes.js"
Task: "Crear recargarCategoria(categoria) en assets/js/reportes.js"
```

## Parallel Example: Polish

```text
Task: "Actualizar mockups en docs/manuales/reportes-exportacion.md con estados de carga y error"
Task: "Verificar clases BEM en assets/css/tabler-override.css"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Completar Phase 1: Setup (verificaciones).
2. Completar Phase 2: Foundational (helpers + CSS + event delegation).
3. Completar Phase 3: US1 (spinner).
4. **Detener y validar US1 con `quickstart.md` pasos 1-3** (solo el spinner es visible).
5. Si MVP es aceptable, continuar con US2 + US3.
6. Si no, iterar sobre el spinner antes de avanzar.

### Incremental Delivery

1. Setup + Foundational → base compartida lista.
2. US1 → spinner visible con nombre de categoria en español.
3. US2 → reintento funcional con aislamiento entre categorias.
4. US3 → mensaje de error validado en lenguaje natural.
5. Polish → documentacion, linting, validacion final con navegador.

### Parallel Team Strategy

1. Completar Setup y Foundational en conjunto.
2. US1 es pequeño (~4 tareas); un solo desarrollador puede completarlo en menos de 1 hora.
3. US2 + US3 estan acoplados (~7 tareas); un desarrollador se encarga de ambos.
4. Polish (~6 tareas) puede dividirse entre dos: uno en codigo (lint, BEM) y otro en documentacion (manual).

---

## Notes

- Sin cambios de esquema de base de datos ni migraciones (Constitución VII).
- Sin nuevas dependencias Composer ni npm.
- UI, mensajes y documentacion en español (Constitución III).
- Validar accesibilidad: `role="status"` + `aria-live="polite"` para spinner, `role="alert"` para error.
- Mantener consistencia con BEM existente (`reportes-analytics__*`).
- Al implementar, usar `tablerIcon('refresh')` para el icono del boton Reintentar.
- El feature 002 (archivado) marca 5 tareas de validacion manual con "validacion parcial, gap F4 pendiente en T055/T056"; este feature (003) cierra ese gap y habilita re-ejecucion de las 5 validaciones del feature 002.

---

## Mapping a tareas archivadas (002) — cerrado del gap

| Tarea archivada 002 | Tarea en 003 | Estado al cierre del 003 |
|----|----|----|
| T055 spinner FR-010 refinado | T008, T009, T010, T011 | Reemplazada por US1 |
| T056 Reintentar FR-011 refinado | T012, T013, T014, T015, T016 | Reemplazada por US2 |
| T025, T033, T040, T046, T052 (validación parcial) | T024 (re-validación end-to-end) | Re-validación humana completa post-US3 |
