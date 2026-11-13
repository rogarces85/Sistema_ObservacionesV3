# Wiki del Sistema de Observaciones REM

> **Proyecto:** Sistema de Observaciones REM — PHP monolith para el Servicio de Salud Osorno, Chile
> **Ruta:** `02-DOCS/wiki/index.md`
> **Estado:** vigente

Esta wiki es el punto de entrada a todo el conocimiento del proyecto. Aquí encontrarás desde el resumen del sistema hasta la configuración del harness rsc, la constitución y los detalles técnicos.

---

## 📖 Documentación del sistema

| Documento | Qué contiene |
|-----------|--------------|
| [`sistema/resumen.md`](sistema/resumen.md) | **Resumen completo**: qué hace el sistema, roles, estados, arquitectura, stack, estructura de directorios, reglas de negocio, base de datos, endpoints API, cómo instalar en casa, cómo subir a GitHub, advertencias y glosario. |
| [`sdd/constitution.md`](sdd/constitution.md) | Constitución del proyecto: stack canon, quality bar, convenciones, branching, seguridad, UX, performance, definición de done y log de enmiendas. |

---

## 🔧 Harness rsc (agente DeepSeek Harness)

| Documento | Qué contiene |
|-----------|--------------|
| [`harness/user-profile.md`](harness/user-profile.md) | Perfil de usuario: nivel técnico, tipo de proyecto, goal del sistema. |
| [`harness/installation-plan.md`](harness/installation-plan.md) | Plan de instalación aceptado: skills, agentes, targets, decisiones del harness. |
| [`harness/decisions.md`](harness/decisions.md) | Decisiones registradas del harness (plan IDs aceptados). |

---

## 🔗 Enlaces externos

| Recurso | URL |
|---------|-----|
| Repositorio GitHub | https://github.com/rogarces85/Sistema_ObservacionesV3 |
| Último tag publicado | v2.1.0 (commit `425a453`, 2026-06-24) |
| Último commit en `main` | `7c60567` (17/08/2026) |

---

## ⚠️ Advertencias importantes

1. **Datos reales en producción.** El sistema está en producción con datos reales. Cualquier operación sobre la base de datos requiere respaldo verificado previo.
2. **La copia local no es un clone de git.** La carpeta `respaldo_observaciones` no tiene `.git`. Para trabajar desde casa, primero subir todo a GitHub (ver `sistema/resumen.md` sección 11) y luego clonar.
3. **Diferencias local vs GitHub.** El módulo Versionado fue eliminado localmente pero aún existe en GitHub. Al subir a GitHub se sobrescribirá `main` con el contenido local.
4. **No hay pruebas automatizadas.** Verificación manual: `php -l`, walkthroughs de páginas, pruebas controladas.
5. **CSRF obligatorio** en todo request mutante desde el navegador.
6. **rsc-harness usa workflow `branches`.** La rama `main` está cerrada para el agente; se pregunta antes de cada cambio de código.

---

## 🗂️ Estructura de esta wiki

```
02-DOCS/wiki/
├── index.md                          # Esta página (tabla de contenidos)
├── sistema/
│   └── resumen.md                    # Resumen completo del sistema
├── sdd/
│   └── constitution.md               # Constitución del proyecto (reglas no negociables)
└── harness/
    ├── user-profile.md               # Perfil de usuario (nivel, goal, stack)
    ├── installation-plan.md          # Plan de rsc aceptado (skills, agentes, targets)
    └── decisions.md                  # Decisiones del harness (plan IDs)
```

---

> **¿Cómo usar esta wiki?** Comienza con [`sistema/resumen.md`](sistema/resumen.md) para entender el sistema completo. Usa [`sdd/constitution.md`](sdd/constitution.md) como referencia de las reglas que no se deben romper al modificar código. Usa [`harness/installation-plan.md`](harness/installation-plan.md) si necesitas entender o modificar la configuración de rsc-harness.
