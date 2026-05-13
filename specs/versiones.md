# Especificación: Sistema de Versionado de Cambios

## Historia de Usuario

> **Como** administrador técnico del sistema Observaciones,
> **necesito** manejar las versiones de cada cambio ejecutado en el código del sistema,
> **para** poder recuperar versiones anteriores si un cambio introduce errores o fallos.

---

## Descripción General

El sistema de versionado permite registrar, consultar y restaurar versiones del código fuente del sistema Observaciones. Su propósito principal es la **recuperación ante fallos**: si un cambio introduce un error, el administrador técnico puede revertir el sistema a una versión estable anterior.

El versionado cubre **todos los elementos del sistema**: código fuente, archivos de configuración, scripts de base de datos y archivos de infraestructura/despliegue. El historial se conserva de forma **indefinida**, sin políticas de eliminación automática.

---

## Flujos de Trabajo

### FT-1: Registrar una Nueva Versión

```
┌─────────────┐     ┌──────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│ 1. El admin │     │ 2. El sistema    │     │ 3. El admin      │     │ 4. El sistema    │
│ modifica    │────>│ detecta los      │────>│ ingresa una      │────>│ crea un snapshot │
│ archivos    │     │ archivos         │     │ descripción      │     │ con metadatos    │
│ del sistema │     │ modificados      │     │ obligatoria      │     │ y lo almacena    │
└─────────────┘     └──────────────────┘     └──────────────────┘     └─────────────────┘
                                                                         │
                                                                         ▼
                                                              ┌──────────────────┐
                                                              │ 5. El sistema    │
                                                              │ confirma éxito   │
                                                              │ y muestra el     │
                                                              │ ID de versión    │
                                                              └──────────────────┘
```

**Pasos detallados:**

1. El administrador técnico modifica archivos del sistema (código, configuración, BD, infraestructura).
2. El sistema escanea y detecta los archivos modificados respecto a la última versión registrada.
3. El sistema solicita una descripción obligatoria del cambio.
4. El sistema crea un snapshot (copia) de todos los archivos versionados junto con metadatos: autor, fecha/hora, descripción, lista de archivos modificados.
5. El sistema confirma el registro exitoso y muestra el identificador de la nueva versión.

---

### FT-2: Consultar Historial de Versiones

```
┌─────────────┐     ┌──────────────────┐     ┌──────────────────┐
│ 1. El admin │     │ 2. El sistema    │     │ 3. El sistema    │
│ accede al   │────>│ consulta el      │────>│ muestra la lista │
│ historial   │     │ historial        │     │ ordenada por     │
│ de versiones│     │ completo         │     │ fecha descendente│
└─────────────┘     └──────────────────┘     └──────────────────┘
```

**Cada entrada del historial muestra:**

| Campo | Descripción |
|-------|-------------|
| ID de versión | Identificador único de la versión |
| Autor | Usuario que registró el cambio |
| Fecha y hora | Momento exacto del registro |
| Descripción | Texto explicativo del cambio |
| Archivos modificados | Lista de archivos incluidos en la versión |

---

### FT-3: Comparar Dos Versiones

```
┌─────────────┐     ┌──────────────────┐     ┌──────────────────┐
│ 1. El admin │     │ 2. El sistema    │     │ 3. El sistema    │
│ selecciona  │     │ carga ambas      │────>│ muestra las      │
│ dos versiones│────>│ versiones        │     │ diferencias      │
│ a comparar  │     │                  │     │ (agregados,      │
└─────────────┘     └──────────────────┘     │ eliminados,      │
                                             │ modificados)     │
                                             └──────────────────┘
```

**Las diferencias se clasifican en:**

- **Agregados**: archivos que existen en la versión más nueva pero no en la anterior.
- **Eliminados**: archivos que existían en la versión anterior pero no en la más nueva.
- **Modificados**: archivos que existen en ambas versiones pero con contenido diferente.

---

### FT-4: Revertir a una Versión Anterior (Rollback)

```
┌─────────────┐     ┌──────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│ 1. El admin │     │ 2. El sistema    │     │ 3. El admin      │     │ 4. El sistema    │
│ selecciona  │     │ muestra los      │────>│ confirma la      │────>│ restaura los     │
│ la versión  │────>│ archivos que     │     │ acción           │     │ archivos de la   │
│ destino     │     │ serán revertidos │     │                  │     │ versión seleccion│
└─────────────┘     └──────────────────┘     └──────────────────┘     └─────────────────┘
                                                                         │
                                                                         ▼
                                                              ┌──────────────────┐
                                                              │ 5. El sistema    │
                                                              │ crea una nueva   │
                                                              │ versión "rollback│
                                                              │ desde vX" y      │
                                                              │ confirma éxito   │
                                                              └──────────────────┘
```

**Reglas de rollback:**

- El sistema **no elimina** la versión actual; crea una nueva versión que refleja el estado restaurado.
- La descripción de la versión de rollback se genera automáticamente: `"Rollback desde versión [ID]"`.
- El rollback es una operación irreversible en sí misma (se puede hacer otro rollback si es necesario).
- Se requiere confirmación explícita del administrador antes de ejecutar el rollback.

---

## Gestión de Sesiones y Cuentas

### Permisos

| Rol | Registrar versión | Consultar historial | Comparar versiones | Revertir versión |
|-----|:----------------:|:-------------------:|:------------------:|:----------------:|
| Administrador técnico | ✅ | ✅ | ✅ | ✅ |
| Desarrollador | ✅ | ✅ | ✅ | ❌ |
| Usuario final | ❌ | ❌ | ❌ | ❌ |

### Autenticación

- Solo usuarios autenticados con rol de **Administrador técnico** o **Desarrollador** pueden acceder al módulo de versionado.
- Cada acción de versionado registra el usuario autenticado como autor del cambio.

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-001 | Versión registrada | `Versión [ID] registrada exitosamente con [N] archivo(s).` |
| MSG-002 | Historial cargado | `Se encontraron [N] versiones registradas.` |
| MSG-003 | Comparación completada | `Comparación completada: [X] agregados, [Y] eliminados, [Z] modificados.` |
| MSG-004 | Rollback ejecutado | `Rollback ejecutado exitosamente. Nueva versión [ID] creada desde versión [ID_origen].` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-101 | Sin descripción | `Error: Debe ingresar una descripción del cambio.` |
| MSG-102 | Sin archivos modificados | `Error: No se detectaron archivos modificados desde la última versión.` |
| MSG-103 | Rollback sin confirmación | `Error: El rollback requiere confirmación explícita.` |
| MSG-104 | Versión no encontrada | `Error: La versión solicitada no existe en el historial.` |
| MSG-105 | Error de almacenamiento | `Error: No se pudo almacenar la versión. Intente nuevamente.` |
| MSG-106 | Permiso insuficiente | `Error: No tiene permisos para ejecutar esta acción.` |

### Mensajes de Confirmación

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-201 | Confirmar rollback | `¿Está seguro de revertir a la versión [ID]? Esta acción restaurará [N] archivo(s) a su estado anterior y creará una nueva versión de registro.` |
| MSG-202 | Confirmar descarte | `¿Desea descartar los cambios no versionados? Esta acción no se puede deshacer.` |

---

## Escenarios BDD (Gherkin)

### Escenario 1: Registrar una versión exitosamente

```gherkin
Dado que soy un administrador técnico autenticado
Y he modificado archivos del sistema desde la última versión
Cuando ingreso una descripción del cambio
Y solicito registrar una nueva versión
Entonces el sistema crea un snapshot de todos los archivos modificados
Y el sistema asigna un ID único a la versión
Y el sistema registra mi usuario como autor
Y el sistema registra la fecha y hora actual
Y el sistema muestra un mensaje de éxito con el ID de la versión
```

### Escenario 2: Intentar registrar versión sin descripción

```gherkin
Dado que soy un administrador técnico autenticado
Y he modificado archivos del sistema desde la última versión
Cuando intento registrar una nueva versión sin ingresar una descripción
Entonces el sistema muestra un error indicando que la descripción es obligatoria
Y no se registra ninguna versión
```

### Escenario 3: Consultar el historial de versiones

```gherkin
Dado que soy un administrador técnico autenticado
Y existen versiones previamente registradas en el sistema
Cuando accedo al historial de versiones
Entonces el sistema muestra todas las versiones ordenadas por fecha descendente
Y cada entrada muestra: ID, autor, fecha, descripción y archivos modificados
```

### Escenario 4: Comparar dos versiones

```gherkin
Dado que soy un administrador técnico autenticado
Y existen al menos dos versiones registradas en el sistema
Cuando selecciono dos versiones para comparar
Entonces el sistema muestra las diferencias clasificadas en:
  | Tipo        | Descripción                                      |
  | Agregados   | Archivos nuevos en la versión más reciente       |
  | Eliminados  | Archivos que ya no existen en la versión reciente|
  | Modificados | Archivos con contenido diferente en ambas versiones|
```

### Escenario 5: Revertir a una versión anterior con confirmación

```gherkin
Dado que soy un administrador técnico autenticado
Y existen múltiples versiones registradas en el sistema
Cuando selecciono una versión anterior para revertir
Y confirmo explícitamente la acción de rollback
Entonces el sistema restaura todos los archivos al estado de la versión seleccionada
Y el sistema crea una nueva versión con descripción automática "Rollback desde versión [ID]"
Y el sistema muestra un mensaje de éxito con el ID de la nueva versión
```

### Escenario 6: Intentar rollback sin confirmación

```gherkin
Dado que soy un administrador técnico autenticado
Y selecciono una versión anterior para revertir
Cuando intento ejecutar el rollback sin confirmar explícitamente
Entonces el sistema muestra un error indicando que se requiere confirmación
Y no se ejecuta el rollback
```

### Escenario 7: Desarrollador intenta revertir una versión

```gherkin
Dado que soy un desarrollador autenticado
Y existen versiones registradas en el sistema
Cuando intento ejecutar un rollback a una versión anterior
Entonces el sistema muestra un error de permisos insuficientes
Y no se ejecuta el rollback
```

### Escenario 8: Intentar registrar versión sin archivos modificados

```gherkin
Dado que soy un administrador técnico autenticado
Y no se han modificado archivos desde la última versión registrada
Cuando intento registrar una nueva versión con una descripción
Entonces el sistema muestra un error indicando que no hay archivos modificados
Y no se registra ninguna versión
```

---

## Mockup ASCII

### Pantalla Principal: Historial de Versiones

```
+==============================================================================+
|  SISTEMA OBSERVACIONES - GESTIÓN DE VERSIONES                                |
+==============================================================================+
|  [ + Nueva Versión ]                                                         |
+==============================================================================+
|  Historial de Versiones                                                      |
+------+---------------------+----------+-------------------+--------+---------+
|  ID  |  Fecha/Hora         | Autor    | Descripción       | Arch.  | Acciones|
+------+---------------------+----------+-------------------+--------+---------+
| v015 | 2026-05-13 14:32:01 | admin    | Fix consulta REM  | 3      | [Ver]   |
| v014 | 2026-05-12 09:15:44 | admin    | Actualizar config | 1      | [Ver]   |
| v013 | 2026-05-10 16:48:22 | dev_user | Nuevo reporte     | 5      | [Ver]   |
| v012 | 2026-05-09 11:20:00 | admin    | Rollback desde    | 4      | [Ver]   |
|      |                     |          | v009              |        |         |
| v011 | 2026-05-08 08:05:33 | admin    | Migración BD      | 2      | [Ver]   |
| ...  | ...                 | ...      | ...               | ...    | ...     |
+------+---------------------+----------+-------------------+--------+---------+
|  Se muestran 5 de 15 versiones                                              |
|  [ << Anterior ]  [ 1 ] [ 2 ] [ 3 ]  [ Siguiente >> ]                       |
+==============================================================================+
```

### Pantalla: Nueva Versión

```
+==============================================================================+
|  SISTEMA OBSERVACIONES - REGISTRAR NUEVA VERSIÓN                             |
+==============================================================================+
|                                                                              |
|  Archivos modificados detectados:                                            |
|  +------------------------------------------------------------------------+  |
|  | [✓] api/controllers/ObservacionController.php                          |  |
|  | [✓] models/ObservacionModel.php                                        |  |
|  | [✓] config/database.php                                                |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|  Descripción del cambio: *                                                   |
|  +------------------------------------------------------------------------+  |
|  | Corrección en la consulta de observaciones para REM serie anexo        |  |
|  |                                                                        |  |
|  |                                                                        |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|                          [ Cancelar ]    [ Registrar Versión ]               |
|                                                                              |
+==============================================================================+
```

### Pantalla: Comparar Versiones

```
+==============================================================================+
|  SISTEMA OBSERVACIONES - COMPARAR VERSIONES                                  |
+==============================================================================+
|                                                                              |
|  Versión origen: [ v012 ▼ ]    Versión destino: [ v015 ▼ ]    [ Comparar ]  |
|                                                                              |
+------------------------------------------------------------------------------+
|  Resultado de la comparación:                                                |
|                                                                              |
|  ARCHIVOS AGREGADOS (+):                                                     |
|    + api/controllers/NuevoController.php                                     |
|                                                                              |
|  ARCHIVOS ELIMINADOS (-):                                                    |
|    - views/reporte_obsoleto.php                                              |
|                                                                              |
|  ARCHIVOS MODIFICADOS (~):                                                   |
|    ~ api/controllers/ObservacionController.php                               |
|      Línea 45: $query = "SELECT * FROM obs"  →  $query = "SELECT id, ...    |
|      Línea 78: WHERE estado = 1              →  WHERE estado IN (1,2)       |
|    ~ models/ObservacionModel.php                                             |
|      Línea 12: private $tabla = "obs"        →  private $tabla = "observ... |
|    ~ config/database.php                                                     |      Línea 5:  'timeout' => 30               →  'timeout' => 60               |
|                                                                              |
|  Resumen: 1 agregado(s), 1 eliminado(s), 3 modificado(s)                    |
|                                                                              |
|                          [ ← Volver al Historial ]                          |
|                                                                              |
+==============================================================================+
```

### Pantalla: Confirmar Rollback

```
+==============================================================================+
|  SISTEMA OBSERVACIONES - CONFIRMAR ROLLBACK                                  |
+==============================================================================+
|                                                                              |
|  ⚠ ADVERTENCIA: Está por revertir el sistema a una versión anterior.         |
|                                                                              |
|  Versión destino: v012                                                       |
|  Fecha original:  2026-05-09 11:20:00                                        |
|  Autor original:  admin                                                      |
|  Descripción:     Migración BD                                               |
|                                                                              |
|  Archivos que serán restaurados (4):                                         |
|    • api/controllers/ObservacionController.php                               |
|    • models/ObservacionModel.php                                             |
|    • config/database.php                                                     |
|    • includes/functions.php                                                  |
|                                                                              |
|  Esta acción NO elimina las versiones actuales. Se creará una nueva          |
|  versión de registro con descripción automática:                             |
|    "Rollback desde versión v012"                                             |
|                                                                              |
|  +------------------------------------------------------------------------+  |
|  | [✓] Confirmo que deseo ejecutar el rollback                             |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|                          [ Cancelar ]    [ Ejecutar Rollback ]               |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Propósito principal | **Recuperación ante fallos** |
| 2 | Usuarios del versionado | Desarrolladores/administradores técnicos |
| 3 | Alcance de cambios | **Todo incluido** (código, config, BD, infraestructura) |
| 4 | Visualización del historial | Interfaz con autor, fecha, descripción, archivos |
| 5 | Capacidad de rollback | Revertir a versión anterior con confirmación |
| 6 | Comparación de versiones | Ver diferencias entre dos versiones |
| 7 | Aprobación de cambios | Sin flujo de aprobación, directo |
| 8 | Descripción obligatoria | Cada cambio requiere descripción |
| 9 | Granularidad | Commit lógico por conjunto de cambios |
| 10 | Retención | **Indefinida**, sin eliminación automática |
