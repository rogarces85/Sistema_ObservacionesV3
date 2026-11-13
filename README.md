# Sistema de Observaciones REM

> **Versión actual:** v2.1.0 (main) — [Changelog](02-DOCS/wiki/sistema/changelog.md)

Sistema de gestión y registro de observaciones del **Resumen Estadístico Mensual (REM)** para establecimientos de salud del **Servicio de Salud Osorno**, Chile.

- **Roles:** `registrador` (crea/edita sus observaciones) y `supervisor` (revisa, aprueba, gestiona usuarios/asignaciones/reportes)
- **Stack:** PHP 8.2, Apache/XAMPP, MySQL/MariaDB, Tabler 1.4, Composer (`phpspreadsheet`, `tcpdf`)
- **Estado:** En producción con datos reales ⚠️

---

## 🚀 Inicio rápido

```bash
# 1. Clonar
git clone https://github.com/rogarces85/Sistema_ObservacionesV3.git
cd Sistema_ObservacionesV3

# 2. Dependencias
composer install

# 3. Configurar BD (crear config/.env.local.php — nunca se sube a GitHub)
cp config/.env.local.php.example config/.env.local.php
# Editar con tus credenciales

# 4. Aplicar esquema SQL (ver docs/spec/ o README completo)
# 5. Configurar Apache/XAMPP apuntando a la raíz del proyecto
```

---

## 📚 Documentación

| Qué buscas | Dónde está |
|------------|------------|
| **Entender el sistema completo** | [`02-DOCS/wiki/sistema/resumen.md`](02-DOCS/wiki/sistema/resumen.md) |
| **Arquitectura, reglas, BD, API, instalación** | [`02-DOCS/wiki/sistema/resumen.md`](02-DOCS/wiki/sistema/resumen.md) |
| **Historial de cambios** | [`02-DOCS/wiki/sistema/changelog.md`](02-DOCS/wiki/sistema/changelog.md) |
| **Despliegue a producción** | [`02-DOCS/wiki/operaciones/deploy.md`](02-DOCS/wiki/operaciones/deploy.md) |
| **Operaciones y monitoreo** | [`02-DOCS/wiki/operaciones/runbook.md`](02-DOCS/wiki/operaciones/runbook.md) |
| **Seguridad y compliance** | [`02-DOCS/wiki/seguridad/politica.md`](02-DOCS/wiki/seguridad/politica.md) |
| **Diseño visual (DEIS)** | [`02-DOCS/wiki/diseno/diseno.md`](02-DOCS/wiki/diseno/diseno.md) |
| **Especificación de producto** | [`02-DOCS/wiki/producto/producto.md`](02-DOCS/wiki/producto/producto.md) |
| **Contrato API (OpenAPI)** | [`02-DOCS/wiki/api/openapi.md`](02-DOCS/wiki/api/openapi.md) |
| **Línea base autoritativa** | [`docs/spec/`](docs/spec/) — BASELINE, VERDAD-ACTUAL, openapi.yaml, AUDITORIA |

> **Entrada única a la wiki:** [`02-DOCS/wiki/index.md`](02-DOCS/wiki/index.md)

---

## ⚠️ Advertencias críticas

1. **Datos reales en producción** — Nunca operar la BD sin respaldo verificado previo. No hay migraciones automatizadas ni tests.
2. **`config/.env.local.php` nunca se sube** — Credenciales de BD. Está en `.gitignore`.
3. **CSRF obligatorio** — Todo request mutante desde navegador requiere `X-CSRF-Token` header o campo `csrf_token`.
4. **Workflow `branches`** — Rama `main` cerrada para commits directos. Usar ramas y PR.
5. **`deploy/migrate.sh` roto** — No usar para instalaciones nuevas.

---

## 🔧 rsc-harness (agente)

Proyecto equipado con **rsc-harness v4.1.5** (target `deepseek`, workflow `branches`).

```bash
npx @ericrisco/rsc doctor     # salud del harness
npx @ericrisco/rsc list       # skills instaladas
npx @ericrisco/rsc add <id>   # instalar skill adicional
```

Configuración en `.rsc.json`, `AGENTS.md`, `02-DOCS/wiki/harness/`.

---

## 📁 Estructura clave

```
api/             # 15 endpoints JSON
models/          # Modelos PHP (Database singleton)
views/           # 12 vistas PHP
config/          # Config, constantes, migraciones SQL
assets/          # CSS/JS (Tabler, Chart.js, tokens)
specs/           # Especificaciones features (001, 002)
tools/           # Scripts manuales
docs/spec/       # Línea base autoritativa (no tocar sin validar)
02-DOCS/wiki/    # Documentación consolidada (esta wiki)
01-TOOLS/        # Conexiones herramientas externas
.rsc/            # Motor rsc-harness (estado máquina)
```

---

## 🔗 Repositorio

**GitHub:** https://github.com/rogarces85/Sistema_ObservacionesV3  
**Rama:** `main` (proyecto madre)  
**Último tag:** `v2.1.0` (2026-06-24)

---

*Documentación completa en [`02-DOCS/wiki/`](02-DOCS/wiki/). Línea base técnica en [`docs/spec/`](docs/spec/).*