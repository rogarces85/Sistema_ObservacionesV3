# Especificación: MOD-IMP — Importación de Observaciones

## Historia de Usuario

> **Como** Registrador del sistema,
> **necesito** importar observaciones masivamente desde un archivo Excel,
> **para** agilizar el registro de datos y evitar la carga manual repetitiva de cada observación.

---

## Descripción General

Este módulo permite la carga masiva de observaciones mediante archivos Excel (`.xlsx`, `.xls`). El sistema implementa un flujo de **dos pasos** (Vista Previa y Confirmación) para asegurar la integridad de los datos.

La importación vincula los registros de la hoja de cálculo con los establecimientos del sistema mediante el código o nombre del establecimiento. El proceso es **tolerante a fallos**: las filas con errores se omiten y se reportan, mientras que las filas válidas se importan exitosamente. **Solo los Registradores** tienen permiso para ejecutar esta función.

---

## Funciones del Módulo

### IMP-001: Subir y Validar Archivo (Vista Previa)

**Descripción**: Procesa el archivo Excel seleccionado y muestra un resumen de los datos sin guardarlos en la base de datos.

**Reglas de Negocio**:
- **Formato**: Solo se aceptan archivos `.xlsx` y `.xls`.
- **Campos Obligatorios por Fila**: `mes`, `tipo` (tipo de error) y `establecimiento` (código o nombre). Si falta alguno, la fila se marca como error.
- **Campo Condicional `rem` (Hoja)**:
  - **Requerido** para tipos: ERROR, REVISAR, F/PLAZO
  - **Opcional** (puede estar vacío) para tipo: S/OBSERVACION
- **Campo `usa_validador`**: Acepta valores "Sí", "No", "N/A". El valor "N/A" se convierte a "NO" antes de guardar.
- **Vinculación**: El sistema busca el establecimiento primero por `codigo_establecimiento`. Si no lo encuentra, intenta buscar por `nombre_establecimiento`. Si falla en ambos, la fila se marca como error.
- **Resultado**: Muestra el total de filas procesadas, cuántas son válidas y cuántas tienen error (con detalle del error por fila).

### IMP-002: Confirmar Importación

**Descripción**: Inserta en la base de datos las filas marcadas como válidas en la vista previa.

**Reglas de Negocio**:
- **Solo Válidas**: Se importan únicamente las filas que pasaron la validación.
- **Año**: Se utiliza el año seleccionado por el usuario en el momento de la carga (o el año de la sesión por defecto).
- **Duplicados**: No se valida duplicidad; se insertan los registros tal como fueron validados.
- **Historial**: Cada observación importada se crea con estado "Pendiente" y se registra "Registro inicial" en su historial.

### IMP-003: Reporte de Resultados

**Descripción**: Muestra el resultado final del proceso de importación.

**Reglas de Negocio**:
- Indica cuántas observaciones se importaron exitosamente.
- Proporciona un resumen de las filas omitidas y sus razones (para que el usuario pueda corregir su archivo Excel si es necesario).

### IMP-004: Generar Plantilla Excel

**Descripción**: Descarga un archivo Excel preformateado con las columnas requeridas y ejemplos, para que el registrador lo use como base.

**Endpoint**: `GET /api/import_template.php`

**Reglas de Negocio**:
- Genera un archivo `.xlsx` con encabezados: `codigo_establecimiento`, `establecimiento`, `mes`, `tipo`, `serie`, `rem`, `detalle_observacion`, `plazo_entrega`, `usa_validador`, `respuesta_establecimiento`.
- Incluye una segunda hoja "Instrucciones" con valores válidos para cada campo.
- Incluye 4 filas de ejemplo con datos reales.
- La columna de código de establecimiento se resalta en verde (prioritaria).
- Solo accesible para usuarios autenticados (Registrador).

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Subir Archivo | ✅ | ❌ No permitido |
| Confirmar Importación | ✅ | ❌ No permitido |
| Ver Reporte | ✅ | ❌ No permitido |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-IMP-001 | Vista Previa OK | `Vista previa generada. [N] filas válidas, [M] filas con errores.` |
| MSG-IMP-002 | Importación Exitosa | `Se importaron [N] observaciones correctamente.` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-IMP-101 | Formato inválido | `Error: Solo se permiten archivos Excel (.xlsx, .xls).` |
| MSG-IMP-102 | Archivo vacío | `Error: El archivo está vacío o no tiene el formato correcto.` |
| MSG-IMP-103 | Sin filas válidas | `Error: No se encontraron filas válidas para importar. Revise el archivo.` |
| MSG-IMP-104 | Permiso denegado | `Error: No tiene permisos para importar archivos.` |

---

## Escenarios BDD (Gherkin)

### Escenario: Importación parcial (algunas filas con error)
```gherkin
Dado que soy un Registrador autenticado
Y tengo un archivo Excel con 10 filas
Y 2 filas tienen el campo "establecimiento" vacío
Cuando subo el archivo para vista previa
Entonces el sistema muestra "8 filas válidas, 2 filas con errores"
Y cuando confirmo la importación
Entonces el sistema importa las 8 observaciones válidas
Y omite las 2 filas con error
```

### Escenario: Vinculación de establecimiento por código
```gherkin
Dado que soy un Registrador autenticado
Y el establecimiento "Hospital Norte" tiene código "HN-001"
Cuando importo una fila donde el establecimiento es "HN-001"
Entonces el sistema vincula correctamente la observación con "Hospital Norte"
```

### Escenario: Supervisor intenta importar
```gherkin
Dado que soy un Supervisor autenticado
Cuando intento acceder a la función de importación
Entonces el sistema deniega el acceso con error 403
```

### Escenario: Validación de campos obligatorios
```gherkin
Dado que soy un Registrador autenticado
Y tengo una fila en Excel sin el campo "tipo"
Cuando subo el archivo
Entonces esa fila se marca como error con el mensaje "Campo 'tipo' es requerido"
```

### Escenario: Importar observación tipo S/OBSERVACION sin hoja REM
```gherkin
Dado que soy un Registrador autenticado
Y tengo una fila en Excel con los siguientes datos:
  | mes    | tipo          | establecimiento | serie   | rem | detalle           |
  | Marzo  | S/OBSERVACION | 12345           | SERIE A |     | Observación general |
Cuando subo el archivo para vista previa
Entonces la fila se marca como válida
Y el campo rem se guarda como vacío
Y cuando confirmo la importación
Entonces el sistema importa la observación con codigo_hoja vacío
```

### Escenario: Importar observación tipo ERROR sin hoja REM (debe fallar)
```gherkin
Dado que soy un Registrador autenticado
Y tengo una fila en Excel con los siguientes datos:
  | mes   | tipo  | establecimiento | serie   | rem | detalle          |
  | Marzo | ERROR | 12345           | SERIE A |     | Falta información |
Cuando subo el archivo para vista previa
Entonces la fila se marca como error con el mensaje "Campo 'rem' (Hoja) es requerido para tipo 'ERROR'"
Y cuando confirmo la importación
Entonces el sistema omite esa fila
```

### Escenario: Importar observación con Usa Validador N/A
```gherkin
Dado que soy un Registrador autenticado
Y tengo una fila en Excel con usa_validador = "N/A"
Cuando subo el archivo para vista previa
Entonces la fila se marca como válida
Y el valor "N/A" se convierte a "NO"
Y cuando confirmo la importación
Entonces el sistema importa la observación con usa_validador = "no"
```

---

## Mockup ASCII

### Pantalla de Importación

```
+==============================================================================+
|  IMPORTACIÓN MASIVA DE OBSERVACIONES                                         |
+==============================================================================+
|                                                                              |
|  Año de importación: [ 2026 ▼ ]                                              |
|                                                                              |
|  Seleccione archivo Excel (.xlsx, .xls):                                     |
|  +------------------------------------------------------------------------+  |
|  | [ Seleccionar Archivo... ]                                             |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|  [ 📤 Generar Vista Previa ]                                                 |
|                                                                              |
+==============================================================================+
```

### Pantalla de Resultados (Vista Previa)

```
+==============================================================================+
|  RESULTADO DE LA VISTA PREVIA                                                |
+==============================================================================+
|                                                                              |
|  Total de filas: 100                                                         |
|  ✅ Válidas: 95                                                              |
|  ❌ Con errores: 5                                                           |
|                                                                              |
|  DETALLE DE ERRORES:                                                         |
|  +------+------------------------------------------------------------------+  |
|  | Fila | Error                                                            |  |
|  +------+------------------------------------------------------------------+  |
|  | 12   | Establecimiento 'Clinica X' no encontrado.                       |  |
|  | 45   | Campo 'mes' es requerido.                                        |  |
|  | 78   | Campo 'tipo' es requerido.                                       |  |
|  | 89   | Establecimiento no especificado.                                 |  |
|  | 99   | Establecimiento 'Codigo 999' no encontrado.                      |  |
|  +------+------------------------------------------------------------------+  |
|                                                                              |
|  [ ← Volver ]                [ ✅ Confirmar Importación (95 registros) ]     |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Formatos soportados | ✅ Modificada → **Solo Excel (.xlsx, .xls)** |
| 2 | Flujo de dos pasos | ✅ Aceptada → Vista previa y luego confirmación |
| 3 | Vinculación de Establecimientos | ✅ Aceptada → Prioridad por Código, fallback por Nombre |
| 4 | Campos obligatorios | ✅ Modificada → **`mes`, `tipo` y `establecimiento` obligatorios**. `rem` (hoja) es condicional: requerido para ERROR/REVISAR/F/PLAZO, opcional para S/OBSERVACION. |
| 5 | Duplicados | ✅ Aceptada → No se validan duplicados |
| 6 | Permisos | ✅ Modificada → **Solo Registradores** |
| 7 | Manejo de Errores | ✅ Aceptada → Omite filas erróneas, importa las válidas |
| 8 | Año de importación | ✅ Aceptada → Seleccionable o año de sesión |
| 9 | Opción N/A en Usa Validador | ✅ Nueva → Acepta "N/A" en Excel, se convierte a "NO" antes de guardar |
| 10 | Plantilla descargable | ✅ Nueva → `import_template.php` genera Excel con encabezados, ejemplos e instrucciones |
