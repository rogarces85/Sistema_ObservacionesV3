# Especificación: MOD-VER — Sistema de Versionado (Snapshots)

## Historia de Usuario

> **Como** Supervisor del sistema,
> **necesito** crear snapshots del código del sistema antes de realizar cambios,
> **para** poder recuperar una versión estable si un cambio introduce errores o fallos.

---

## Descripción General

El sistema de versionado permite crear **snapshots manuales** del código fuente del sistema (directorios `api/`, `models/`, `views/`, `config/`, `includes/`, `assets/` y `index.php`). Su propósito principal es la **recuperación ante fallos**: si un cambio introduce un error en producción, se puede restaurar una versión estable anterior mediante rollback.

Cada snapshot se almacena como copia física de archivos en `uploads/versiones/` y se registra en la base de datos con metadatos (tag, descripción, autor, manifiesto de archivos).

El versionado **no detecta cambios automáticamente**. El Supervisor decide cuándo crear un snapshot (típicamente antes de desplegar modificaciones).

---

## Funciones del Módulo

### VER-001: Crear Snapshot (Versión)

**Descripción**: Crea una copia de seguridad de los archivos del sistema.

**Endpoint**: `POST /api/versioning.php?action=create`

**Reglas de Negocio**:
- **Alcance**: Copia los directorios `api/`, `models/`, `views/`, `config/`, `includes/`, `assets/` y el archivo `index.php`.
- **Exclusiones**: No copia `vendor/`, `uploads/`, `.git/`, specs, ni archivos de configuración con credenciales.
- **Tag automático**: Formato `v001`, `v002`, `v003`... auto-incremental.
- **Metadatos**: Se registra tag, descripción (obligatoria), autor, fecha y manifiesto de archivos (JSON con hash MD5 de cada archivo).
- **Ubicación**: Archivos en `uploads/versiones/vXXX/` replicando la estructura del proyecto.

```
Flujo:
1. Supervisor solicita crear versión con descripción
2. Sistema determina el próximo tag (v001, v002, ...)
3. Sistema crea directorio uploads/versiones/vXXX/
4. Sistema copia recursivamente los directorios versionados
5. Sistema genera manifiesto JSON con ruta relativa + hash MD5
6. Sistema inserta registro en tabla versiones_sistema
7. Sistema retorna ID y tag de la nueva versión
```

---

### VER-002: Listar Versiones

**Descripción**: Muestra el historial completo de snapshots creados.

**Endpoint**: `GET /api/versioning.php?action=list`

**Reglas de Negocio**:
- **Orden**: ID descendente (más recientes primero).
- **Datos visibles**: ID, Tag, Descripción, Autor (nombre), Fecha de creación.
- **Autor**: Se muestra el nombre completo del Supervisor que creó la versión.
- **Acceso**: Solo Supervisores.

---

### VER-003: Ver Detalle de Versión

**Descripción**: Muestra los metadatos completos y el manifiesto de archivos de una versión.

**Endpoint**: `GET /api/versioning.php?action=detail&id={id}`

**Reglas de Negocio**:
- **Contenido**: Tag, descripción, autor, fecha, y lista de archivos con sus hashes MD5.
- **Manifiesto**: El campo `archivos_json` se decodifica para mostrar la lista de archivos incluidos en el snapshot.

---

### VER-004: Rollback (Restaurar Versión)

**Descripción**: Restaura los archivos del sistema al estado de un snapshot anterior.

**Endpoint**: `POST /api/versioning.php?action=rollback&id={id}`

**Reglas de Negocio**:
- **Precondición**: El directorio del snapshot debe existir en el sistema de archivos.
- **Proceso**: Copia cada archivo del snapshot a su ubicación original en el proyecto.
- **Seguridad**: El rollback **no elimina la versión actual** — crea automáticamente una nueva versión con descripción `"Rollback desde versión {TAG}"`.
- **Encadenable**: Se puede hacer rollback de un rollback (cada rollback crea su propio snapshot).
- **Directorios**: Si un directorio destino no existe, se crea automáticamente.

```
Flujo:
1. Supervisor selecciona versión a restaurar
2. Sistema verifica que el snapshot existe en disco
3. Sistema copia cada archivo del snapshot → ubicación original
4. Sistema crea nueva versión "Rollback desde vXXX"
5. Sistema retorna ID de la nueva versión de rollback
```

---

## Arquitectura Técnica

```
┌─────────────────────────────────────────────────────────────────┐
│                    Sistema de Versionado                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  uploads/versiones/                                             │
│  ├── v001/                                                      │
│  │   ├── api/         (copia de api/)                           │
│  │   ├── models/      (copia de models/)                        │
│  │   ├── views/       (copia de views/)                         │
│  │   ├── config/      (copia de config/)                        │
│  │   ├── includes/    (copia de includes/)                      │
│  │   ├── assets/      (copia de assets/)                        │
│  │   └── index.php    (copia de index.php)                      │
│  ├── v002/                                                      │
│  │   └── ...                                                    │
│  └── v003/                                                      │
│      └── ...                                                    │
│                                                                 │
│  BD: versiones_sistema                                          │
│  ┌──────┬─────────┬──────────────────────┬────────────────────┐ │
│  │  id  │ tag     │ descripcion          │ snapshot_path      │ │
│  ├──────┼─────────┼──────────────────────┼────────────────────┤ │
│  │  1   │ v001    │ Versión inicial      │ v001/              │ │
│  │  2   │ v002    │ Fix consulta REM     │ v002/              │ │
│  │  3   │ v003    │ Rollback desde v001  │ v003/              │ │
│  └──────┴─────────┴──────────────────────┴────────────────────┘ │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Archivos versionados vs excluidos

| Incluidos en snapshot | Excluidos del snapshot |
|-----------------------|------------------------|
| `api/` (todos los .php) | `vendor/` (dependencias Composer) |
| `models/` (todos los .php) | `uploads/` (archivos de usuario) |
| `views/` (todos los .php) | `.git/` (repositorio) |
| `config/` (todos los .php y .sql) | `specs/` (documentación) |
| `includes/` (todos los .php) | `openspec/` |
| `assets/` (css, js, imágenes) | `.agents/` |
| `index.php` | Archivos sueltos de testing |

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Listar versiones | ❌ | ✅ |
| Ver detalle | ❌ | ✅ |
| Crear snapshot | ❌ | ✅ |
| Ejecutar rollback | ❌ | ✅ |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-VER-001 | Snapshot creado | `Versión creada exitosamente` (HTTP 201) |
| MSG-VER-002 | Rollback ejecutado | `Rollback ejecutado exitosamente` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-VER-101 | Sin descripción | `La descripción es requerida` (HTTP 400) |
| MSG-VER-102 | Versión no encontrada | `Versión no encontrada` (HTTP 404) |
| MSG-VER-103 | Snapshot no existe en disco | `El snapshot de la versión no existe en el sistema de archivos` |
| MSG-VER-104 | Permiso denegado | `Acceso denegado` (HTTP 403) |
| MSG-VER-105 | No autenticado | `No autenticado` (HTTP 401) |

---

## Escenarios BDD (Gherkin)

### Escenario: Crear snapshot antes de un cambio
```gherkin
Dado que soy un Supervisor autenticado
Y el sistema está funcionando correctamente en su versión actual
Cuando solicito crear una nueva versión con descripción "Antes de modificar reportes"
Entonces el sistema crea un snapshot en uploads/versiones/vXXX/
Y copia todos los archivos de api/, models/, views/, config/, includes/, assets/ e index.php
Y asigna un tag auto-incremental (ej. v005)
Y registra la versión en la tabla versiones_sistema
Y muestra "Versión creada exitosamente"
```

### Escenario: Rollback a versión anterior
```gherkin
Dado que soy un Supervisor autenticado
Y existe la versión v003 que funcionaba correctamente
Y la versión actual (v004) tiene un error
Cuando ejecuto rollback a la versión v003
Entonces el sistema restaura todos los archivos al estado de v003
Y crea una nueva versión con descripción "Rollback desde versión v003"
Y muestra "Rollback ejecutado exitosamente"
```

### Escenario: Intentar crear versión sin descripción
```gherkin
Dado que soy un Supervisor autenticado
Cuando intento crear una versión sin proporcionar descripción
Entonces el sistema responde con HTTP 400
Y muestra "La descripción es requerida"
```

### Escenario: Registrador intenta acceder al versionado
```gherkin
Dado que soy un Registrador autenticado
Cuando intento acceder al endpoint de versionado
Entonces el sistema responde con HTTP 403
Y muestra "Acceso denegado"
```

### Escenario: Intentar rollback a versión sin snapshot en disco
```gherkin
Dado que soy un Supervisor autenticado
Y la versión v002 fue eliminada manualmente del disco
Cuando intento ejecutar rollback a v002
Entonces el sistema lanza una excepción
Y muestra "El snapshot de la versión no existe en el sistema de archivos"
```

---

## Mockup ASCII

### Historial de Versiones

```
+==============================================================================+
|  SISTEMA OBSERVACIONES - GESTIÓN DE VERSIONES                                |
+==============================================================================+
|  [ + Nueva Versión ]                                                         |
+==============================================================================+
|  Historial de Snapshots                                                      |
+------+---------------------+-------------------+-----------------------------+
|  Tag | Fecha               | Autor             | Descripción                 |
+------+---------------------+-------------------+-----------------------------+
| v005 | 2026-05-25 14:32:01 | Cecilia           | Rollback desde versión v003 |
| v004 | 2026-05-24 09:15:44 | Cecilia           | Nuevo módulo de reportes    |
| v003 | 2026-05-20 16:48:22 | Cecilia           | Corrección logout           |
| v002 | 2026-05-15 11:20:00 | Cecilia           | Versión base estable        |
| v001 | 2026-05-10 08:05:33 | Cecilia           | Instalación inicial         |
+------+---------------------+-------------------+-----------------------------+
|                                                                              |
+==============================================================================+
```

### Crear Nueva Versión

```
+==============================================================================+
|  NUEVA VERSIÓN (SNAPSHOT)                                                    |
+==============================================================================+
|                                                                              |
|  Se creará una copia de seguridad de los siguientes directorios:             |
|    • api/                                                                    |
|    • models/                                                                 |
|    • views/                                                                  |
|    • config/                                                                 |
|    • includes/                                                               |
|    • assets/                                                                 |
|    • index.php                                                               |
|                                                                              |
|  Descripción del cambio: *                                                   |
|  +------------------------------------------------------------------------+  |
|  |                                                                        |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|                          [ Cancelar ]    [ Crear Snapshot ]                 |
|                                                                              |
+==============================================================================+
```

### Confirmar Rollback

```
+==============================================================================+
|  CONFIRMAR ROLLBACK                                                          |
+==============================================================================+
|                                                                              |
|  ⚠ ADVERTENCIA: Se restaurarán los archivos del sistema al estado           |
|  de la versión v003.                                                         |
|                                                                              |
|  Descripción: Corrección logout                                              |
|  Fecha:        2026-05-20 16:48:22                                           |
|  Autor:        Cecilia                                                       |
|                                                                              |
|  Se creará automáticamente una nueva versión:                                |
|    "Rollback desde versión v003"                                             |
|                                                                              |
|                          [ Cancelar ]    [ Ejecutar Rollback ]               |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Propósito principal | Recuperación ante fallos en producción |
| 2 | Tipo de creación | **Manual** — el Supervisor decide cuándo crear snapshot |
| 3 | Sin detección automática | El sistema NO detecta cambios; solo copia directorios fijos |
| 4 | Sin comparación de versiones | No existe funcionalidad de diff entre versiones |
| 5 | Alcance del snapshot | Directorios fijos: api, models, views, config, includes, assets, index.php |
| 6 | Exclusiones | vendor, uploads, .git, specs, openspec |
| 7 | Tags | Auto-incrementales: v001, v002, v003... |
| 8 | Rollback seguro | Crea nueva versión en lugar de destruir la actual |
| 9 | Permisos | Solo Supervisores |
| 10 | Retención | Indefinida, hasta eliminación manual del directorio |
| 11 | Rollback de rollback | Soportado (cada rollback crea su propio snapshot) |

---

## Limitaciones Conocidas

1. **Sin detección de cambios**: El sistema copia todo el directorio sin verificar qué archivos cambiaron. Los snapshots pueden ser grandes.
2. **Sin comparación**: No hay funcionalidad de diff entre versiones.
3. **Solo archivos PHP/estáticos**: La base de datos no se versiona (solo código fuente).
4. **Rollback no revierte BD**: Si un cambio incluyó una migración SQL, el rollback de archivos no la revierte.
5. **Snapshots en disco local**: Si el servidor pierde el disco, se pierden los snapshots (no hay respaldo externo).
6. **No compresión**: Los archivos se copian tal cual, sin comprimir.
