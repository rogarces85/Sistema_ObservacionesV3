<!--
  Sync Impact Report (v1.1.0)
  - Version change: 1.0.0 → 1.1.0 (minor; sin breaking changes)
  - Principios: sin cambios en los existentes (I–V); se agregan VI y VII.
  - Principios nuevos:
      VI. Trazabilidad documental
      VII. Sistema desde 0 (para nuevos SpecKits)
  - Secciones actualizadas:
      Stack Tecnológico — agregado @tabler/core (npm) y versiones Composer.
      Gobernanza — agregado ciclo SpecKit (discover → constitution → specify
        → plan → tasks → implement → archive) y referencia al README.md.
  - Templates actualizados: ninguno (compatibles).
  - Fuente de verdad: README.md (v2.3.0, generado por /speckit.discover).
-->
# Sistema de Observaciones REM — Constitución

## Principios Rectores

### I. Seguridad ante todo
Toda funcionalidad debe implementar las capas de seguridad del sistema sin
excepción:
- **Autenticación**: Sesiones PHP con cookie httponly. Verificación en cada
  endpoint y vista.
- **CSRF**: Token de 32 bytes (`bin2hex(random_bytes(32))`) en meta tag +
  header `X-CSRF-TOKEN`. Validado en todos los endpoints POST/PUT/DELETE.
- **Contraseñas**: `password_hash` con bcrypt (`PASSWORD_DEFAULT`). Política:
  mínimo 8 caracteres, 1 mayúscula, 1 número.
- **SQL Injection**: Consultas preparadas PDO (`emulate_prepares = false`).
  Nunca concatenar valores en SQL.
- **Roles**: Verificación de rol (supervisor/registrador) en backend. El
  frontend NUNCA es la única barrera de permisos.
- **Asignaciones**: Validación backend de que un registrador tenga el
  establecimiento asignado para el mes exacto. Devolver 403 si no corresponde.
- **Ruta dinámica**: `API_BASE` debe calcularse desde
  `window.location.pathname`. Sin rutas hardcodeadas.

### II. Arquitectura por capas
El sistema sigue una arquitectura de tres capas estrictas:
1. **Vistas** (`views/`) — PHP que renderiza HTML con componentes Tabler.
   Contienen la lógica de presentación mínima. Datos se pasan desde modelos.
2. **APIs** (`api/`) — Endpoints REST que reciben/responden JSON. Validan
   entrada, aplican permisos, delegan a modelos.
3. **Modelos** (`models/`) — Capa de datos con PDO Singleton. Nunca acceden
   directamente a `$_GET`/`$_POST`. Sin lógica de presentación.
- El ruteo central está en `index.php` (router principal: login check + page
  routing + permisos).
- Las APIs se comunican con el frontend vía `fetch()` con headers JSON.

### III. Idioma español obligatorio
Todo el sistema debe estar en español:
- Interfaz de usuario: etiquetas, mensajes, botones, tooltips, placeholders.
- Código fuente: nombres de variables, clases, métodos, comentarios,
  documentación.
- APIs: nombres de acciones, parámetros, mensajes de error.
- Commits y documentación técnica.
- La única excepción son palabras técnicas estándar del stack (PDO, MySQL,
  JSON, CSRF, bcrypt, Singleton, etc.).

### IV. Framework UI: Tabler Core + Tabler Icons
El frontend usa exclusivamente Tabler Core 1.4 (Bootstrap 5) y Tabler Icons:
- Componentes HTML: usar clases de Tabler/Bootstrap (`.card`, `.table`,
  `.btn`, `.dropdown`, `.modal`, `.nav-tabs`, etc.).
- Iconos: siempre `<i class="ti ti-nombre"></i>` (Tabler Icons). No usar
  iconos SVG inline, Font Awesome, ni otros sets.
- Gráficos: ApexCharts 3.45. No usar Chart.js, D3, u otras librerías.
- CSS personalizado: Solo en `assets/css/tabler-override.css`. Usar
  convención BEM y paleta SSO. Sin estilos inline.
- JavaScript: Módulos en `assets/js/`. Cargar después de Tabler y antes de
  `</body>`. Usar `fetchAPI()` de `app.js` para llamadas AJAX.

### V. Mantenibilidad y convenciones
- **Sin comentarios innecesarios**: El código debe ser autoexplicativo.
  Comentar solo decisiones complejas no obvias.
- **Modularidad**: Cada archivo tiene un propósito único. `app.js` contiene
  la lógica general compartida. Los módulos específicos van en archivos
  separados (ej: `charts-apex.js`, `toasts.js`, `dashboard-features.js`).
- **Versiones**: Usar `git tag -a vX.Y.Z`. Convención de commits:
  `feat:`, `fix:`, `refactor:`, `style:`, `docs:`, `chore:`, `perf:`.
- **Migraciones SQL**: Cada cambio de esquema DB debe tener su propio archivo
  SQL en `config/`, numerado y fechado.

### VI. Trazabilidad documental
- **Manuales con mockups**: Cada módulo del sistema debe contar con su manual
  de usuario en `docs/manuales/{modulo}.md`, con mockups en ASCII-art o SVG
  (referenciados relativos al `.md`).
- **Cambios SpecKit documentados**: Cada cambio se registra en
  `openspec/changes/{nombre}/` con `proposal.md`, `tasks.md` y, al archivarse,
  delta aplicado en `openspec/specs/`.
- **README como fuente de verdad**: El `README.md` raíz (generado vía
  `/speckit.discover`) es la descripción viva del sistema. Toda sección nueva
  debe enlazar a la spec o manual correspondiente.
- **Specs en `specs/`**: Cada módulo del sistema tiene su spec funcional en
  `specs/{modulo}.md` o `specs/mod-{modulo}.md`, indexada por
  `specs/INDICE.md`.

### VII. Sistema desde 0 (para nuevos SpecKits)
- Por convención del proyecto (`AGENTS.md`), cada nuevo SpecKit trata el
  código existente como **referencia documental**, no como restricción de
  implementación. Esto habilita refactors disruptivos, renames, y
  consolidaciones sin ataduras al pasado.
- **Excepción — BD intocable**: El esquema y los datos existentes son
  inmutables salvo migración explícita aprobada y versionada en `config/`.
  Ningún SpecKit puede añadir/modificar columnas o tablas sin una migración
  `migration_YYYY_MM_DD_*.sql` aprobada.
- **Excepción — manuales vivos**: Los manuales en `docs/manuales/` deben
  mantenerse sincronizados con cualquier cambio que afecte la UX del módulo.
  Si un SpecKit cambia un flujo visible al usuario, el manual correspondiente
  se actualiza en el mismo cambio.

## Stack Tecnológico

| Categoría | Tecnologías |
|-----------|-------------|
| **Backend** | PHP 7.4+, PDO MySQL (Singleton) |
| **Base de Datos** | MySQL 5.7+ (InnoDB, utf8mb4) |
| **Frontend** | HTML5, CSS3, JavaScript ES6+ |
| **UI Framework** | Tabler Core 1.4 (Bootstrap 5), Tabler Icons |
| **Gráficos** | ApexCharts 3.45 |
| **Librerías PHP** | PhpSpreadsheet 5.4 (Excel), TCPDF 6.10 (PDF) |
| **Servidor** | Apache (XAMPP) |

**Dependencias declaradas (no inferir, leer de los lockfiles):**
- `composer.json` → `phpoffice/phpspreadsheet ^5.4`, `tecnickcom/tcpdf ^6.10`.
  Versiones exactas en `composer.lock`.
- `package.json` → `@tabler/core ^1.4.0`. Versiones exactas en
  `package-lock.json`. Las demás librerías JS viven en `assets/libs/`
  (vendor preempaquetado) y se consideran inmutables salvo migración
  explícita.

- PHP 7.4+ es el mínimo soportado. No migrar a versiones modernas sin
  aprobación explícita.
- MySQL 5.7+ con InnoDB y charset `utf8mb4_unicode_ci`. Usar migraciones
  secuenciales para cambios de esquema.
- XAMPP es el entorno de producción objetivo.

## Convenciones de Código

### PHP
- Namespace global (sin PSR-4 por ahora). Clases autocargadas vía `require`.
- Modelos extienden o usan `Database::getInstance()` (Singleton PDO).
- API endpoints: switch de acción, validación CSRF, verificación de rol,
  delegación a modelo, respuesta JSON.
- Vistas: PHP puro con apertura `<?php` y cierre `?>`. Evitar lógica de
  negocios en vistas.

### JavaScript
- ES6+: `const`/`let`, arrow functions, template literals, clases.
- `fetchAPI()` de `app.js` para todas las solicitudes AJAX (envía JSON,
  CSRF token, maneja errores).
- Sin dependencias npm/build tools. JavaScript vanilla.

### Base de Datos
- Nombres de tablas en plural y minúsculas con guión bajo: `observaciones`,
  `historial_estados`, `usuarios`.
- Columnas: `snake_case`. FK con sufijo `_id`.
- Charset `utf8mb4_unicode_ci`. Motor InnoDB.
- Índices compuestos para consultas frecuentes de reportes.

## Gobernanza

- Esta constitución prevalece sobre cualquier práctica ad-hoc no documentada.
- Las enmiendas requieren: (1) documento de cambio, (2) aprobación del
  equipo, (3) plan de migración si aplica, (4) actualización de la línea de
  versión al final de este documento.
- Cambios mayores (MAJOR): modificaciones incompatibles en principios
  rectores o eliminación de principios existentes.
- Cambios menores (MINOR): nuevos principios o secciones agregadas.
- Parches (PATCH): aclaraciones, correcciones de redacción, ajustes no
  semánticos.
- Toda revisión de PR debe verificar cumplimiento con los principios de
  seguridad y stack tecnológico.
- La complejidad debe justificarse: si una solución requiere más archivos o
  capas de lo esperable, documentar por qué.

### Ciclo SpecKit canónico
Los cambios al sistema siguen el flujo:

```
/speckit.discover    →  README.md (fuente de verdad)
/speckit.constitution → .specify/memory/constitution.md (este archivo)
/speckit.specify     →  specs/{NNN}-{nombre}/spec.md
/speckit.plan        →  specs/{NNN}-{nombre}/plan.md + data-model.md + contracts/
/speckit.tasks       →  specs/{NNN}-{nombre}/tasks.md
/speckit.implement   →  código + tests + migración a docs/manuales/
/speckit.archive     →  mover change a openspec/changes/archive/ + delta a openspec/specs/
```

Antes de ejecutar `/speckit.specify` debe existir un `/speckit.discover`
vigente (no más de 90 días) y esta constitución ratificada.

**Versión**: 1.1.0 | **Ratificado**: 2026-06-01 | **Última enmienda**: 2026-06-17
