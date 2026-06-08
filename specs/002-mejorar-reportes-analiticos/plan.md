# Implementation Plan: Mejorar Reportes Analiticos

**Branch**: `[001-unificar-specs]` | **Date**: 2026-06-08 | **Spec**: [spec.md](spec.md)

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

**Gates derivados de la Constitución del proyecto:**

1. **Seguridad**: PASS. La feature requiere endpoints de lectura/exportacion que deben verificar sesion y rol en backend; POST debe validar CSRF.
2. **Arquitectura**: PASS. La vista renderiza estructura, la API valida/coordina y los modelos concentran consultas PDO.
3. **Idioma**: PASS. UI, mensajes, contratos y documentacion se redactan en español.
4. **Stack**: PASS. Se usan Tabler, Tabler Icons, ApexCharts y librerias PHP ya permitidas; no se agregan dependencias.
5. **Base de Datos**: PASS. No hay cambios de esquema; se reutilizan tablas existentes.

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

1. **Seguridad**: PASS. Los contratos exigen autenticacion, control de rol y CSRF para exportacion por POST.
2. **Arquitectura**: PASS. Los contratos separan vista, API y modelo; no se propone logica de negocio en la vista.
3. **Idioma**: PASS. Los textos visibles y documentacion estan en español.
4. **Stack**: PASS. No hay nuevas librerias; los graficos usan ApexCharts ya permitido.
5. **Base de Datos**: PASS. El modelo de datos es logico y no requiere migraciones.
