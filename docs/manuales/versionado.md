# Manual de Usuario - Versionado del Sistema

## Descripción

El módulo de **Versionado del Sistema** permite crear snapshots (copias de respaldo) del código fuente activo del sistema y restaurar versiones anteriores cuando sea necesario. Esta función está disponible exclusivamente para usuarios con rol **Supervisor**.

## Acceso

1. Iniciar sesión con credenciales de Supervisor
2. En el menú lateral, bajo **Configuración**, hacer clic en **Versionado**

---

## Flujo de Creación de Snapshot

### Paso 1: Abrir modal de creación

- Hacer clic en el botón verde **"Crear Snapshot"** ubicado en la esquina superior derecha de la página.

### Paso 2: Completar la descripción

- En el campo **"Descripción del cambio"**, escribir una descripción clara de los cambios incluidos en esta versión.
- Este campo es **obligatorio**.
- Ejemplo: `"Actualización de validaciones REM Serie A - Marzo 2026"`

### Paso 3: Confirmar creación

- Hacer clic en **"Crear Snapshot"**
- El sistema copiará todos los archivos de código fuente activos al directorio `uploads/versiones/vXXX/`
- Se generará un manifiesto MD5 con las rutas relativas y checksums de cada archivo
- Se creará un registro en la base de datos con la versión (formato v001, v002... v999)

### Archivos incluidos en el snapshot

| Incluidos | Excluidos |
|-----------|-----------|
| `.php` | `node_modules/` |
| `.js` | `.git/` |
| `.css` | `uploads/` |
| `.sql` | `vendor/` |
| `.json` | `*.log` |
| `.md` | `*.tmp` |
| | `assets/cache/` |
| | `.env` |

---

## Flujo de Restauración (Rollback)

### Paso 1: Seleccionar versión

- En la tabla **Historial de Versiones**, ubicar la versión deseada
- Hacer clic en el ícono de **ojo** para ver detalle o en el ícono de **restaurar** (flecha) para iniciar el rollback

### Paso 2: Confirmar restauración

- Se abrirá un modal de confirmación con advertencias importantes:
  - **Advertencia de archivos**: Los archivos actuales serán sobrescritos
  - **Advertencia de base de datos**: El snapshot NO incluye la BD. Si hay cambios de esquema, ejecutar migraciones manualmente
- Marcar el checkbox: *"Entiendo que los archivos serán sobrescritos y que debo verificar migraciones de BD manualmente"*
- Hacer clic en **"Restaurar Versión"**

### Paso 3: Resultado

- Si la restauración es exitosa: se mostrará notificación verde con la cantidad de archivos restaurados
- Si hay archivos fallidos: se mostrará notificación amarilla con la lista de archivos no restaurados. El supervisor debe verificar y reintentar manualmente
- Se crea un nuevo registro de versión documentando el rollback (ej: `"Rollback desde versión v003"`)

---

## Vista de Detalle de Versión

Al hacer clic en el ícono de **ojo** en cualquier versión:

- Se muestra información completa: descripción, autor, fecha de creación
- Se muestra el **manifiesto de archivos** con:
  - Ruta relativa completa
  - Hash MD5
  - Tamaño del archivo

---

## Estadísticas

En la parte superior de la página se muestran:

| Tarjeta | Descripción |
|---------|-------------|
| Total Versiones | Cantidad total de snapshots creados |
| Última Versión | Tag de la versión más reciente |
| Archivos en Última | Cantidad de archivos en el último snapshot |
| Solo código | Recordatorio de que se excluyen uploads, vendor, .git |

---

## Advertencias Importantes

1. **El rollback NO es atómico**: Si falla a medio camino, se mostrará la lista de archivos no restaurados
2. **La base de datos NO se restaura**: Los cambios de esquema deben manejarse con migraciones SQL secuenciales
3. **Cada rollback genera una nueva versión**: Para mantener el historial completo de cambios
4. **Límite de versiones**: Formato v001 a v999 (3 dígitos zero-padded)

---

## Mockups

### Lista de Versiones

```
┌─────────────────────────────────────────────────────────────────────┐
│  Versionado del Sistema                              [Crear Snapshot]│
│  Gestión de versiones y snapshots del sistema                        │
├─────────────────────────────────────────────────────────────────────┤
│  [📦 5]        [📷 v005]      [📄 142]       [⚠️ Solo código]       │
│  Total Versiones Última Versión Archivos en Última Excluye uploads   │
├─────────────────────────────────────────────────────────────────────┤
│  ℹ️ Información importante                                           │
│  • Snapshots incluyen solo código fuente activo                      │
│  • Se excluyen: node_modules, .git, uploads, vendor, *.log, *.tmp   │
│  • Rollback solo restaura archivos. BD requiere migraciones manuales │
├─────────────────────────────────────────────────────────────────────┤
│  Historial de Versiones                                              │
│  ┌────────┬──────────────────┬──────────┬──────────┬──────────────┐  │
│  │Versión │ Descripción      │ Autor    │ Archivos │ Fecha        │  │
│  ├────────┼──────────────────┼──────────┼──────────┼──────────────┤  │
│  │ v005   │ Fix validaciones │ J. Pérez │ 142 arch │ 02/06/2026   │  │
│  │ v004   │ Rollback v002    │ J. Pérez │ 138 arch │ 01/06/2026   │  │
│  │ v003   │ Módulo reportes  │ J. Pérez │ 140 arch │ 30/05/2026   │  │
│  │ v002   │ Importación Excel│ J. Pérez │ 135 arch │ 28/05/2026   │  │
│  │ v001   │ Snapshot inicial │ J. Pérez │ 130 arch │ 25/05/2026   │  │
│  └────────┴──────────────────┴──────────┴──────────┴──────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

### Modal de Creación

```
┌──────────────────────────────────────────┐
│  📷 Crear Nuevo Snapshot              [×] │
├──────────────────────────────────────────┤
│  ⚠️ Se creará un snapshot completo del   │
│     código fuente actual del sistema.     │
│                                          │
│  Descripción del cambio *                │
│  ┌────────────────────────────────────┐  │
│  │ Actualización de validaciones      │  │
│  │ REM Serie A - Marzo 2026           │  │
│  └────────────────────────────────────┘  │
│                                          │
│  Archivos incluidos:                      │
│  [.php] [.js] [.css] [.sql] [.json] [.md] │
│                                          │
│  Archivos excluidos:                      │
│  [node_modules/] [.git/] [uploads/]       │
│  [vendor/] [*.log] [*.tmp] [.env]         │
│                                          │
│              [Cancelar] [📷 Crear Snapshot]│
└──────────────────────────────────────────┘
```

### Modal de Confirmación de Restauración

```
┌──────────────────────────────────────────┐
│  ⚠️ Confirmar Restauración            [×] │
├──────────────────────────────────────────┤
│  🚨 Advertencia: Restauración de archivos│
│  Esta acción sobrescribirá los archivos   │
│  actuales con los de la versión v003.     │
│                                          │
│  ⚠️ Importante: Base de datos            │
│  El snapshot NO incluye la BD. Si hay     │
│  cambios de esquema, ejecutar migraciones │
│  SQL manualmente.                         │
│                                          │
│  Detalles de la restauración:             │
│  • Se restaurarán todos los archivos      │
│  • Los archivos actuales serán sobreesc.  │
│  • Se creará nuevo registro de versión    │
│  • Si falla, se mostrará lista de errores │
│                                          │
│  ☐ Entiendo que los archivos serán       │
│    sobrescritos y debo verificar BD       │
│                                          │
│           [Cancelar] [⬅ Restaurar Versión]│
└──────────────────────────────────────────┘
```
