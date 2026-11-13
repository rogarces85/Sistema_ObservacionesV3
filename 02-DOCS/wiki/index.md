# Wiki del Sistema de Observaciones REM

> **Proyecto:** Sistema de Observaciones REM — PHP monolith para el Servicio de Salud Osorno, Chile
> **Rama:** `main` (proyecto madre en GitHub)
> **Última actualización:** 2026-11-13

Esta wiki es el punto de entrada a todo el conocimiento del proyecto. Aquí encontrarás desde el resumen del sistema hasta la configuración del harness rsc, la constitución y los detalles técnicos.

---

## 📖 Documentación del sistema

| Sección | Documento | Qué contiene |
|---------|-----------|--------------|
| **Sistema** | [`sistema/resumen.md`](sistema/resumen.md) | **Resumen completo**: qué hace el sistema, roles, estados, arquitectura, stack, estructura, reglas de negocio, BD, API, instalación, advertencias, glosario. |
| | [`sistema/changelog.md`](sistema/changelog.md) | Historial de cambios por versión (de `CHANGELOG.md`). |
| **Producto** | [`producto/producto.md`](producto/producto.md) | Usuarios, propósito, contexto operativo, capacidades, restricciones, principios. |
| **Diseño** | [`diseno/diseno.md`](diseno/diseno.md) | Paleta DEIS, tipografía, tokens CSS, componentes, estados, reglas de UI, accesibilidad. |
| **Seguridad** | [`seguridad/politica.md`](seguridad/politica.md) | Modelo de amenazas, controles, gestión de parches, secretos, retención, compliance. |
| **Operaciones** | [`operaciones/deploy.md`](operaciones/deploy.md) | Requisitos, preparación, despliegue, Apache, BD, worker, backups, healthcheck, go-live. |
| | [`operaciones/runbook.md`](operaciones/runbook.md) | Servicios, logs, monitoreo, procedimientos, usuarios, rotación cuentas, limpieza, troubleshooting. |
| **API** | [`api/openapi.md`](api/openapi.md) | Resumen navegable del contrato OpenAPI (15 endpoints, modelos, reglas, exportación). |

---

## 📐 Línea base autoritativa (no modificar sin validación)

| Documento | Qué contiene |
|-----------|--------------|
| [`docs/spec/BASELINE.md`](../spec/BASELINE.md) | Propósito, diccionario de datos, flujos críticos, **17 reglas de oro** |
| [`docs/spec/VERDAD-ACTUAL.md`](../spec/VERDAD-ACTUAL.md) | Modelo de datos exacto, **47 reglas de negocio** citadas, matriz de autorización |
| [`docs/spec/openapi.yaml`](../spec/openapi.yaml) | Contrato formal API (OpenAPI 3.1.0, 15 endpoints) |
| [`docs/spec/AUDITORIA-CODIGO-MUERTO.md`](../spec/AUDITORIA-CODIGO-MUERTO.md) | Inventario de código muerto con evidencia y nivel de riesgo |

> **Donde la wiki discrepe con `docs/spec/`, manda `docs/spec/`.**

---

## 🔧 Harness rsc (agente DeepSeek Harness)

| Documento | Qué contiene |
|-----------|--------------|
| [`harness/user-profile.md`](harness/user-profile.md) | Perfil de usuario: nivel técnico, tipo de proyecto, goal del sistema. |
| [`harness/installation-plan.md`](harness/installation-plan.md) | Plan de instalación aceptado: skills, agentes, targets, decisiones. |
| [`harness/decisions.md`](harness/decisions.md) | Decisiones registradas del harness (plan IDs aceptados). |

---

## 🔗 Enlaces externos

| Recurso | URL |
|---------|-----|
| Repositorio GitHub | https://github.com/rogarces85/Sistema_ObservacionesV3 |
| Último tag publicado | `v2.1.0` (commit `425a453`, 2026-06-24) |
| Último commit en `main` | `7c60567` (17/08/2026) — matriz Plazo/Validador |

---

## ⚠️ Advertencias importantes

1. **Datos reales en producción.** El sistema está en producción con datos reales. Cualquier operación sobre la base de datos requiere respaldo verificado previo.
2. **La copia local no es un clone de git.** La carpeta `respaldo_observaciones` no tiene `.git`. Para trabajar desde casa, clonar desde GitHub.
3. **Diferencias local vs GitHub.** El módulo Versionado fue eliminado localmente pero aún existe en GitHub (se eliminó al hacer push --force).
4. **No hay pruebas automatizadas.** Verificación manual: `php -l`, walkthroughs de páginas, pruebas controladas.
5. **CSRF obligatorio** en todo request mutante desde el navegador.
6. **rsc-harness usa workflow `branches`.** La rama `main` está cerrada para el agente; se pregunta antes de cada cambio de código.

---

## 🗂️ Estructura de esta wiki

```
02-DOCS/wiki/
├── index.md                          # Esta página (tabla de contenidos)
├── sistema/
│   ├── resumen.md                    # Resumen completo del sistema
│   └── changelog.md                  # Historial de versiones
├── producto/
│   └── producto.md                   # Especificación de producto
├── diseno/
│   └── diseno.md                     # Diseño visual DEIS
├── seguridad/
│   └── politica.md                   # Política de seguridad
├── operaciones/
│   ├── deploy.md                     # Despliegue a producción
│   └── runbook.md                    # Operaciones y monitoreo
├── api/
│   └── openapi.md                    # Resumen del contrato API
├── sdd/
│   └── constitution.md               # Constitución del proyecto (reglas no negociables)
└── harness/
    ├── user-profile.md               # Perfil de usuario rsc
    ├── installation-plan.md          # Plan de rsc aceptado
    └── decisions.md                  # Decisiones del harness
```

---

> **¿Cómo usar esta wiki?** Comienza con [`sistema/resumen.md`](sistema/resumen.md) para entender el sistema completo. Usa [`sdd/constitution.md`](sdd/constitution.md) como referencia de las reglas que no se deben romper al modificar código. Usa [`harness/installation-plan.md`](harness/installation-plan.md) si necesitas entender o modificar la configuración de rsc-harness.