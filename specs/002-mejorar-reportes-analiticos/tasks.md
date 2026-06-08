# Tasks: Mejorar Reportes Analiticos

**Input**: Design documents from `/specs/002-mejorar-reportes-analiticos/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: No se solicitaron pruebas automatizadas ni enfoque TDD; se incluyen tareas de validacion manual y linting al final.

**Organization**: Las tareas estan agrupadas por historia de usuario para permitir implementacion y validacion independiente.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Puede ejecutarse en paralelo porque usa archivos distintos o no depende de tareas incompletas.
- **[Story]**: Historia de usuario asociada, solo en fases de historias.
- Todas las tareas incluyen rutas exactas de archivos.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Preparar el terreno sin cambiar comportamiento funcional todavia.

- [X] T001 Revisar estructura actual de reportes y documentar puntos de integracion en specs/002-mejorar-reportes-analiticos/tasks.md
- [X] T002 [P] Verificar metodos de agregacion existentes para reportes analiticos en models/Observation.php
- [X] T003 [P] Verificar flujo actual de exportacion general y especifica en api/export.php
- [X] T004 [P] Verificar estructura actual de filtros, preview e informe supervisor en views/reportes.php
- [X] T005 [P] Verificar inicializacion actual del modulo Reportes en assets/js/reportes.js

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Crear contratos internos compartidos por todas las historias.

**CRITICAL**: Ninguna historia debe implementarse antes de completar esta fase.

- [X] T006 Definir catalogo canonico de categorias analiticas y etiquetas en assets/js/reportes.js
- [X] T007 Definir validacion compartida de filtros analiticos año/trimestre/mes/comuna/establecimiento en api/reports.php
- [X] T008 Agregar normalizacion de filtros analiticos con meses derivados de trimestre en api/reports.php
- [X] T009 Agregar helper de alcance por rol para consultas analiticas reutilizables en models/Observation.php
- [X] T010 Asegurar que las respuestas analiticas usen estructura success/data/message consistente en api/reports.php
- [X] T011 Asegurar que las solicitudes AJAX de reportes usen ruta dinamica o helper compartido en assets/js/reportes.js
- [X] T012 Preparar estilos reutilizables BEM para estados analiticos sin estilos inline en assets/css/tabler-light.css

**Checkpoint**: Base lista para implementar historias de usuario.

---

## Phase 3: User Story 1 - Explorar reportes analiticos por categoria (Priority: P1) MVP

**Goal**: Mostrar cinco categorias analiticas con resumen visual y tabla, respetando el alcance del usuario autenticado.

**Independent Test**: Abrir Reportes con un año con datos y verificar que cada categoria muestra informacion grafica y tabular sin aplicar filtros secundarios.

### Implementation for User Story 1

- [X] T013 [P] [US1] Agregar estructura de pestañas o secciones para cinco categorias analiticas en views/reportes.php
- [X] T014 [P] [US1] Agregar contenedores de grafico, tabla, estado vacio y error por categoria en views/reportes.php
- [X] T015 [US1] Agregar carga de ApexCharts si no esta disponible para la vista Reportes en views/reportes.php
- [X] T016 [P] [US1] Implementar metodos agregados para errores por establecimiento, errores por serie y errores por hoja en models/Observation.php
- [X] T017 [P] [US1] Implementar metodos agregados para plazos de entrega y uso de validador en models/Observation.php
- [X] T018 [US1] Agregar caso reportes-analiticos y categorias individuales al switch de reportes en api/reports.php
- [X] T019 [US1] Construir respuesta con reportes, totales, resultados y estado por categoria en api/reports.php
- [X] T020 [US1] Implementar cliente de carga de categorias analiticas en assets/js/reportes.js
- [X] T021 [US1] Implementar renderizado ApexCharts por categoria con datos agregados en assets/js/reportes.js
- [X] T022 [US1] Implementar renderizado de tabla sincronizada por categoria en assets/js/reportes.js
- [X] T023 [US1] Implementar estados cargando, listo, vacio y error por categoria en assets/js/reportes.js
- [X] T024 [US1] Conectar cambio de categoria sin modificar filtros activos en assets/js/reportes.js
- [ ] T025 [US1] Validar manualmente la historia US1 usando pasos 1, 2, 3, 7 y 10 de specs/002-mejorar-reportes-analiticos/quickstart.md

**Checkpoint**: US1 funciona de forma independiente como MVP.

---

## Phase 4: User Story 2 - Filtrar analisis por periodo y ubicacion (Priority: P1)

**Goal**: Permitir filtros compartidos por año, trimestre, mes, comuna y establecimiento en todas las categorias analiticas.

**Independent Test**: Aplicar combinaciones de filtros y verificar que todas las categorias reflejan el mismo alcance.

### Implementation for User Story 2

- [X] T026 [P] [US2] Agregar controles de trimestre y filtros analiticos compartidos en views/reportes.php
- [X] T027 [US2] Ajustar obtencion de filtros para incluir trimestre y validar compatibilidad mes/trimestre en assets/js/reportes.js
- [X] T028 [US2] Sincronizar filtro de establecimiento con comuna seleccionada en assets/js/reportes.js
- [X] T029 [US2] Aplicar filtros compartidos a todas las llamadas de categorias analiticas en assets/js/reportes.js
- [X] T030 [US2] Aplicar filtros año/trimestre/mes/comuna/establecimiento a consultas agregadas en models/Observation.php
- [X] T031 [US2] Validar backend para rechazar mes incompatible con trimestre o establecimiento ajeno a comuna en api/reports.php
- [X] T032 [US2] Implementar accion limpiar filtros analiticos preservando año de trabajo en assets/js/reportes.js
- [ ] T033 [US2] Validar manualmente la historia US2 usando pasos 4, 5, 6 y 9 de specs/002-mejorar-reportes-analiticos/quickstart.md

**Checkpoint**: US2 funciona de forma independiente sin romper US1.

---

## Phase 5: User Story 3 - Exportar cada reporte analitico (Priority: P2)

**Goal**: Exportar individualmente la categoria visible usando los filtros activos y respetando permisos.

**Independent Test**: Seleccionar una categoria con datos, exportarla y comprobar que el archivo contiene solo el alcance filtrado.

### Implementation for User Story 3

- [X] T034 [P] [US3] Agregar boton de exportacion por categoria analitica en views/reportes.php
- [X] T035 [US3] Construir solicitud de exportacion analitica con categoria y filtros activos en assets/js/reportes.js
- [X] T036 [US3] Validar tipo_reporte analitico, formato, CSRF y permisos en api/export.php
- [X] T037 [US3] Reutilizar consultas agregadas de categorias para datos exportables en models/Observation.php
- [X] T038 [US3] Agregar formato de archivo y encabezados para exportaciones analiticas en models/Exporter.php
- [X] T039 [US3] Bloquear exportacion sin datos y mostrar mensaje en español en assets/js/reportes.js
- [ ] T040 [US3] Validar manualmente la historia US3 usando pasos 8, 9 y 10 de specs/002-mejorar-reportes-analiticos/quickstart.md

**Checkpoint**: US3 funciona de forma independiente con filtros activos.

---

## Phase 6: User Story 4 - Interpretar indicadores clave rapidamente (Priority: P3)

**Goal**: Mostrar indicadores resumidos consistentes con las tablas y graficos para apoyar lectura gerencial.

**Independent Test**: Cargar un periodo con datos y verificar que los indicadores coinciden con los resultados agregados del mismo filtro.

### Implementation for User Story 4

- [X] T041 [P] [US4] Agregar tarjetas de indicadores principales en views/reportes.php
- [X] T042 [US4] Calcular totales de observaciones, errores, fuera de plazo y sin validador en api/reports.php
- [X] T043 [US4] Obtener totales base necesarios para indicadores desde models/Observation.php
- [X] T044 [US4] Renderizar indicadores y actualizar valores al cambiar filtros en assets/js/reportes.js
- [X] T045 [US4] Mostrar estado vacio o no disponible para indicadores sin datos en assets/js/reportes.js
- [ ] T046 [US4] Validar manualmente la historia US4 usando pasos 4, 6 y 7 de specs/002-mejorar-reportes-analiticos/quickstart.md

**Checkpoint**: US4 funciona y no altera los flujos anteriores.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Cierre de calidad, documentacion y validacion transversal.

- [X] T047 [P] Actualizar manual de usuario con mockups o capturas de reportes analiticos en docs/manuales/reportes-exportacion.md
- [X] T048 [P] Revisar textos visibles, nombres de variables nuevas y mensajes en español en views/reportes.php
- [X] T049 [P] Revisar textos visibles, nombres de variables nuevas y mensajes en español en assets/js/reportes.js
- [X] T050 Revisar que no existan estilos inline nuevos para reportes analiticos en views/reportes.php
- [X] T051 Ejecutar lint PHP sobre archivos modificados api/reports.php api/export.php models/Observation.php models/Exporter.php views/reportes.php
- [ ] T052 Ejecutar validacion manual completa desde specs/002-mejorar-reportes-analiticos/quickstart.md
- [X] T053 Revisar que no se hayan creado migraciones ni cambios de esquema en config/

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Sin dependencias.
- **Foundational (Phase 2)**: Depende de Setup y bloquea todas las historias.
- **US1 (Phase 3)**: Depende de Foundational y entrega el MVP.
- **US2 (Phase 4)**: Depende de Foundational; se recomienda despues de US1 para validar filtros sobre UI existente.
- **US3 (Phase 5)**: Depende de Foundational; se recomienda despues de US1 y US2 para exportar lo visible con filtros activos.
- **US4 (Phase 6)**: Depende de Foundational; se recomienda despues de US1 y US2 para asegurar consistencia de indicadores.
- **Polish (Phase 7)**: Depende de las historias implementadas.

### User Story Dependencies

- **US1 (P1)**: Puede iniciar tras Foundational y no depende de otras historias.
- **US2 (P1)**: Puede iniciar tras Foundational, pero valida mejor cuando US1 ya tiene categorias visibles.
- **US3 (P2)**: Requiere categorias analiticas disponibles y filtros activos para cubrir el flujo completo.
- **US4 (P3)**: Requiere datos agregados de categorias y filtros para que los indicadores sean verificables.

### Within Each User Story

- Modelos antes de endpoints.
- Endpoints antes de renderizado dependiente de datos.
- Vista base antes de integracion JavaScript.
- Validacion manual al cerrar cada historia.

### Parallel Opportunities

- T002, T003, T004 y T005 pueden ejecutarse en paralelo.
- T013 y T014 pueden ejecutarse en paralelo con T016 y T017.
- T026 puede ejecutarse en paralelo con T030 si la estructura de filtros ya esta definida.
- T034 puede ejecutarse en paralelo con T037 si US1 y US2 estan completos.
- T041 puede ejecutarse en paralelo con T043 si el contrato de totales esta definido.
- T047, T048 y T049 pueden ejecutarse en paralelo durante el cierre.

---

## Parallel Example: User Story 1

```text
Task: "Agregar estructura de pestañas o secciones para cinco categorias analiticas en views/reportes.php"
Task: "Implementar metodos agregados para errores por establecimiento, errores por serie y errores por hoja en models/Observation.php"
Task: "Implementar metodos agregados para plazos de entrega y uso de validador en models/Observation.php"
```

## Parallel Example: User Story 2

```text
Task: "Agregar controles de trimestre y filtros analiticos compartidos en views/reportes.php"
Task: "Aplicar filtros año/trimestre/mes/comuna/establecimiento a consultas agregadas en models/Observation.php"
```

## Parallel Example: User Story 3

```text
Task: "Agregar boton de exportacion por categoria analitica en views/reportes.php"
Task: "Reutilizar consultas agregadas de categorias para datos exportables en models/Observation.php"
```

## Parallel Example: User Story 4

```text
Task: "Agregar tarjetas de indicadores principales en views/reportes.php"
Task: "Obtener totales base necesarios para indicadores desde models/Observation.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Completar Phase 1: Setup.
2. Completar Phase 2: Foundational.
3. Completar Phase 3: US1.
4. Detener y validar US1 con quickstart.
5. Continuar con filtros, exportacion e indicadores.

### Incremental Delivery

1. Setup + Foundational -> base compartida lista.
2. US1 -> cinco categorias con grafico y tabla.
3. US2 -> filtros compartidos y consistentes.
4. US3 -> exportacion individual por categoria.
5. US4 -> indicadores gerenciales.
6. Polish -> documentacion, linting y validacion final.

### Parallel Team Strategy

1. Completar Setup y Foundational en conjunto.
2. Separar trabajo por archivo: modelo, API, vista y JavaScript.
3. Integrar por historia y validar antes de avanzar a la siguiente.

---

## Notes

- No modificar esquema de base de datos ni crear migraciones.
- Mantener exportacion general e informe de errores para supervisor.
- Validar permisos en backend, nunca solo en frontend.
- Mantener UI, mensajes y documentacion en español.
