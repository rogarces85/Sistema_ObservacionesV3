# Implementation Plan: Mejorar Reportes Analiticos

**Branch**: `[002-mejorar-reportes-analiticos]` | **Date**: 2026-06-08 | **Last re-validated**: 2026-06-17 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/002-mejorar-reportes-analiticos/spec.md`

## Summary

Mejorar la seccion de reportes para convertirla en una vista analitica con cinco categorias independientes, filtros compartidos, resumen visual, tabla sincronizada y exportacion individual por categoria. La implementacion se apoyara en la arquitectura actual Vista -> API -> Modelo, reutilizando datos existentes y manteniendo exportaciones actuales e informe exclusivo de supervisores.

## Technical Context

**Language/Version**: PHP 7.4+, JavaScript ES6+, HTML5, CSS3

**Primary Dependencies**: Tabler Core 1.4, Tabler Icons, ApexCharts 3.45, PhpSpreadsheet 5.4, TCPDF 6.10

**Storage**: MySQL 5.7+ existente con PDO Singleton; sin cambios de esquema

**Testing**: Validacion manual funcional en XAMPP, revision de permisos por rol, pruebas de endpoints JSON y exportaciones existentes

**Target Platform**: Apache en XAMPP, navegador web de escritorio y movil

**Project Type**: Aplicacion web PHP monolitica con vistas, APIs JSON y modelos PDO

**Performance Goals**: Carga de categorias analiticas en menos de 5 segundos para volumenes normales; identificacion del top 5 en menos de 30 segundos; exportacion individual en menos de 3 clics

**Constraints**: No modificar esquema ni migraciones; sin dependencias nuevas; UI en español; rutas dinamicas; permisos validados en backend; consultas preparadas PDO

**Scale/Scope**: Cinco categorias analiticas, filtros compartidos por año/trimestre/mes/comuna/establecimiento, tablas navegables y exportacion por categoria

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

**Gates derivados de la Constitución v1.1.0 (2026-06-17) del proyecto:**

1. **I. Seguridad**: PASS. La feature requiere endpoints de lectura/exportacion que deben verificar sesion y rol en backend; POST debe validar CSRF.
2. **II. Arquitectura**: PASS. La vista renderiza estructura, la API valida/coordina y los modelos concentran consultas PDO.
3. **III. Idioma**: PASS. UI, mensajes, contratos y documentacion se redactan en español.
4. **IV. Stack (Tabler + ApexCharts)**: PASS. Se usan Tabler, Tabler Icons, ApexCharts y librerias PHP ya permitidas; no se agregan dependencias. CSS personalizado solo en `assets/css/tabler-override.css`.
5. **V. Mantenibilidad y convenciones**: PASS. Commits con prefijo conventional; modularidad JS en `assets/js/reportes.js`; migraciones no requeridas.
6. **VI. Trazabilidad documental**: PASS. Manual del modulo afectado en `docs/manuales/reportes-exportacion.md`; spec en `specs/002-mejorar-reportes-analiticos/spec.md`; cambio bajo `openspec/changes/` (futuro archivado).
7. **VII. Sistema desde 0 (BD intocable)**: PASS. Assumption explicita: sin cambios de esquema; verificado en T053.
8. **Stack Tecnologico**: PASS. Sin nuevas dependencias; versiones declaradas en `composer.json` y `package.json`.
9. **Gobernanza (ciclo SpecKit)**: PASS. Producido en el orden correcto: `discover` (README v2.3.0) → `constitution` (v1.1.0) → `specify` → `plan` (este documento).

## Project Structure

### Documentation (this feature)

```text
specs/002-mejorar-reportes-analiticos/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── reportes-analiticos.openapi.yaml
│   └── ui-reportes-analiticos.md
└── tasks.md
```

### Source Code (repository root)

```text
views/
└── reportes.php

assets/js/
└── reportes.js

assets/css/
└── tabler-override.css

api/
├── reports.php
└── export.php

models/
└── Observation.php

docs/manuales/
└── reportes-exportacion.md
```

**Structure Decision**: Mantener la estructura monolitica actual. La mejora toca la vista de reportes, el modulo JavaScript de reportes, endpoints JSON/exportacion y consultas agregadas en el modelo de observaciones. El manual de usuario se actualizara en `docs/manuales/` durante la implementacion.

## Complexity Tracking

No hay violaciones constitucionales ni complejidad excepcional que justificar.

## Phase 0: Research

Ver [research.md](research.md). Todas las decisiones relevantes quedan resueltas sin pendientes de aclaracion.

## Phase 1: Design & Contracts

Ver [data-model.md](data-model.md), [contracts/reportes-analiticos.openapi.yaml](contracts/reportes-analiticos.openapi.yaml), [contracts/ui-reportes-analiticos.md](contracts/ui-reportes-analiticos.md) y [quickstart.md](quickstart.md).

## Constitution Check Post-Design

1. **I. Seguridad**: PASS. Los contratos exigen autenticacion, control de rol y CSRF para exportacion por POST.
2. **II. Arquitectura**: PASS. Los contratos separan vista, API y modelo; no se propone logica de negocio en la vista.
3. **III. Idioma**: PASS. Los textos visibles y documentacion estan en español.
4. **IV. Stack (Tabler + ApexCharts)**: PASS. Sin nuevas librerias; los graficos usan ApexCharts ya permitido; CSS override en `tabler-override.css`.
5. **V. Mantenibilidad**: PASS. Sin duplicacion de codigo; la consulta reutilizable de T009 evita divergencia entre reportes.
6. **VI. Trazabilidad documental**: PASS. T047 actualiza el manual con mockups de las cinco categorias.
7. **VII. Sistema desde 0 (BD intocable)**: PASS. T053 verifica que no se crearon archivos en `config/` que alteren esquema.
8. **Stack Tecnologico**: PASS. Endpoints documentados en `contracts/reportes-analiticos.openapi.yaml`; UI en `contracts/ui-reportes-analiticos.md`.
9. **Gobernanza (ciclo SpecKit)**: PASS. Artefactos completos: `spec.md`, `plan.md`, `research.md`, `data-model.md`, `contracts/`, `quickstart.md`, `tasks.md`.
