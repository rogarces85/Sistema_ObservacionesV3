# MOD-IMP: Importación de Observaciones desde Excel

## User Scenarios & Testing

### Historia de Usuario
Como Registrador, necesito importar observaciones desde un archivo Excel, para cargar múltiples registros de forma masiva y ahorrar tiempo en la carga manual.

### Escenarios de Aceptación

#### HU-IMP-001: Subir archivo Excel con vista previa
**Prioridad: P1**

```gherkin
Dado un usuario autenticado con rol Registrador
  Y un archivo Excel (.xlsx o .xls) con observaciones válidas
Cuando selecciona el archivo y el año de importación
  Y presiona "Generar Vista Previa"
Entonces el sistema procesa el archivo SIN guardar en base de datos
  Y muestra el total de filas procesadas
  Y muestra cuántas filas son válidas y cuántas tienen errores
  Y lista el detalle de error por cada fila inválida
```

#### HU-IMP-002: Confirmar importación después de vista previa
**Prioridad: P1**

```gherkin
Dado un usuario autenticado con rol Registrador
  Y una vista previa generada con filas válidas
Cuando el usuario confirma la importación
Entonces el sistema inserta solo las filas válidas en la tabla observaciones
  Y cada observación se crea con estado "Pendiente"
  Y se registra "Registro inicial" en el historial
  Y muestra mensaje "Se importaron [N] observaciones correctamente"
  Y omite las filas con errores sin interrumpir el proceso
```

#### HU-IMP-003: Descargar plantilla Excel
**Prioridad: P2**

```gherkin
Dado un usuario autenticado con rol Registrador
Cuando solicita descargar la plantilla de importación
Entonces el sistema genera un archivo .xlsx con encabezados predefinidos
  Y el archivo incluye una hoja "Instrucciones" con valores válidos para cada campo
  Y el archivo incluye 4 filas de ejemplo con datos reales
  Y la columna de código de establecimiento está resaltada en verde
  Y la descarga se inicia automáticamente
```

#### HU-IMP-004: Vinculación inteligente de establecimiento
**Prioridad: P1**

```gherkin
Dado un usuario autenticado con rol Registrador
  Y un archivo Excel con filas que usan código de establecimiento
Cuando se procesa el archivo
Entonces el sistema busca el establecimiento primero por código_establecimiento
  Y si no encuentra por código, intenta por nombre_establecimiento
  Y la fila se marca como válida solo si encuentra coincidencia
```

#### HU-IMP-005: Validación de campos obligatorios
**Prioridad: P1**

```gherkin
Dado un usuario autenticado con rol Registrador
  Y un archivo Excel con filas que faltan campos obligatorios
Cuando se procesa el archivo
Entonces cada fila con campos faltantes se marca como error
  Y se muestra el detalle: "Campo '[nombre]' es requerido"
```

Campos obligatorios: mes, establecimiento_id, codigo_serie, tipo_error, detalle_observacion, plazo_entrega, usa_validador.

#### HU-IMP-006: Retrocompatibilidad con nombres de columna antiguos
**Prioridad: P2**

```gherkin
Dado un usuario autenticado con rol Registrador
  Y un archivo Excel con nombres de columna antiguos (tipo_error, codigo_serie, codigo_hoja)
Cuando se procesa el archivo
Entonces el sistema acepta los nombres antiguos como equivalentes
  Y el mapeo se realiza de forma transparente
```

#### HU-IMP-007: Supervisor no puede importar
**Prioridad: P1**

```gherkin
Dado un usuario autenticado con rol Supervisor
Cuando intenta acceder a la función de importación
Entonces el sistema deniega el acceso con código 403
  Y muestra mensaje "No tiene permisos para importar archivos"
```

#### HU-IMP-008: Archivo sin filas válidas
**Prioridad: P2**

```gherkin
Dado un usuario autenticado con rol Registrador
  Y un archivo Excel donde todas las filas tienen errores
Cuando se genera la vista previa
Entonces el sistema muestra 0 filas válidas
  Y el botón de confirmar importación está deshabilitado
  Y muestra mensaje "No se encontraron filas válidas para importar. Revise el archivo."
```

### Casos Borde

| Caso | Entrada | Resultado Esperado |
|------|---------|-------------------|
| Formato inválido | Archivo .csv o .ods | Rechazar, solo .xlsx y .xls |
| Archivo vacío | Excel sin datos | Mostrar error "El archivo está vacío o no tiene el formato correcto" |
| Establecimiento no encontrado | Código y nombre no existen en BD | Marcar fila como error con detalle "Establecimiento '[valor]' no encontrado" |
| Duplicados | Filas con datos idénticos | No validar duplicados; importar todos los registros válidos |
| Usa validador = N/A | Valor "N/A" en columna usa_validador | Convertir a "NO" antes de guardar |
| Tipo S/OBSERVACION sin hoja | Fila tipo S/OBSERVACION con hoja vacía | Marcar como válida, guardar codigo_hoja como vacío |
| Tipo ERROR sin hoja | Fila tipo ERROR con hoja vacía | Marcar como error, codigo_hoja requerido para este tipo |
| Año no seleccionado | No hay año en la sesión ni en el formulario | Usar año actual del sistema por defecto |
| Caracteres especiales | Nombres con tildes, ñ, caracteres UTF-8 | Procesar correctamente usando PhpSpreadsheet con codificación UTF-8 |

---

## Requirements

### Requerimientos Funcionales

| ID | Descripción | Método | Endpoint |
|----|------------|--------|----------|
| FR-IMP-001 | El sistema debe aceptar archivos Excel (.xlsx y .xls) para importación de observaciones | POST | api/import.php?action=preview |
| FR-IMP-002 | El sistema debe validar cada fila del archivo y mostrar vista previa con filas válidas y errores sin guardar en BD | POST | api/import.php?action=preview |
| FR-IMP-003 | El sistema debe insertar solo las filas válidas al confirmar la importación | POST | api/import.php?action=confirm |
| FR-IMP-004 | El sistema debe generar una plantilla descargable .xlsx con encabezados, hoja de instrucciones y filas de ejemplo | GET | api/import_template.php |
| FR-IMP-005 | El sistema debe validar campos obligatorios: mes, establecimiento_id, codigo_serie, tipo_error, detalle_observacion, plazo_entrega, usa_validador | — | — |
| FR-IMP-006 | El sistema debe vincular establecimientos primero por código DEIS (codigo_establecimiento), luego por nombre como fallback. No se usa ID numérico de BD en el Excel | — | — |
| FR-IMP-007 | El sistema debe aceptar nombres de columna antiguos (tipo_error, codigo_serie, codigo_hoja) como retrocompatibilidad | — | — |
| FR-IMP-008 | El sistema debe crear cada observación importada con estado "Pendiente" y registro "Registro inicial" en el historial | — | — |
| FR-IMP-009 | El sistema debe restringir la importación solo a usuarios con rol Registrador | — | — |
| FR-IMP-010 | El sistema debe convertir el valor "N/A" de usa_validador a "NO" antes de guardar | — | — |
| FR-IMP-011 | El sistema debe rechazar archivos que no sean .xlsx o .xls con mensaje de error | — | — |
| FR-IMP-012 | El sistema debe usar PhpSpreadsheet (versión 5.4) para procesamiento de archivos Excel | — | — |

### Entidades Clave

#### observaciones (importación inserta registros)
| Campo | Tipo | Requerido | Descripción |
|-------|------|:---------:|-------------|
| mes | INT | Sí | Mes de la observación. En Excel se acepta número (1-12) o texto en español ("Enero"–"Diciembre"), se normaliza a INT |
| establecimiento_id | INT (FK) | Sí | Vinculado por código o nombre |
| codigo_serie | VARCHAR(50) | Sí | Código de serie REM |
| tipo_error | VARCHAR(50) | Sí | Tipo de error |
| detalle_observacion | TEXT | Sí | Descripción de la observación |
| plazo_entrega | DATE | Sí | Fecha de plazo de entrega |
| usa_validador | ENUM('si','no') | Sí | Acepta "Sí", "No", "N/A" (se convierte a "no") |
| codigo_hoja | VARCHAR(50) | Condicional | Requerido para ERROR/REVISAR/F/PLAZO, opcional para S/OBSERVACION |
| estado | ENUM | — | Siempre "Pendiente" en importación |

#### Columnas esperadas en Excel (con retrocompatibilidad)

| Columna actual | Columna antigua (aceptada) |
|----------------|---------------------------|
| mes | mes |
| codigo_establecimiento | (código DEIS, identificador principal) |
| nombre_establecimiento | establecimiento (fallback si no hay código) |
| codigo_serie | codigo_serie |
| codigo_hoja | codigo_hoja |
| tipo_error | tipo_error |
| detalle_observacion | detalle_observacion |
| plazo_entrega | plazo_entrega |
| usa_validador | usa_validador |

### Roles y Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Subir archivo para vista previa | ✅ | ❌ |
| Confirmar importación | ✅ | ❌ |
| Descargar plantilla | ✅ | ❌ |

---

## Success Criteria

1. Un Registrador puede subir un archivo Excel con 100 filas y obtener la vista previa con el desglose de válidas/errores en < 3 segundos.
2. Al confirmar la importación, solo las filas válidas se insertan en la base de datos; las filas con error se omiten sin afectar el proceso.
3. Las observaciones importadas se crean con estado "Pendiente" y su historial registra "Registro inicial".
4. El sistema vincula correctamente establecimientos primero por código y luego por nombre como fallback.
5. Los nombres de columna antiguos (tipo_error, codigo_serie, codigo_hoja) son aceptados sin errores.
6. Un Supervisor recibe código 403 al intentar acceder a cualquier función de importación.
7. Archivos en formato no soportado (.csv, .ods) son rechazados con mensaje claro.
8. El valor "N/A" en usa_validador se convierte automáticamente a "NO" al guardar.
9. La plantilla descargable incluye encabezados correctos, una hoja de instrucciones con valores válidos y 4 filas de ejemplo.
10. El sistema no valida duplicados; todas las filas válidas se importan independientemente de si ya existen registros similares.

---

## Clarifications

### Session 2026-06-01

- Q: ¿El mes se espera como texto o número en el Excel? → A: Aceptar ambos. Número (1-12) o texto en español. Normalizar a INT internamente.
- Q: ¿Cómo se determina el año de las observaciones importadas? → A: Selector en el formulario web. No hay columna "anio" en el Excel.
- Q: ¿Cómo identifica el usuario el establecimiento en el Excel? → A: Solo código DEIS (codigo_establecimiento). nombre_establecimiento como fallback. Sin ID numérico de BD.
- Q: ¿Validar serie y hoja REM contra listas permitidas? → A: No validar. El backend acepta cualquier valor. La consistencia la valida el supervisor después.
- Q: ¿Codificación de caracteres especiales en Excel? → A: PhpSpreadsheet detecta automáticamente. BD usa utf8mb4_unicode_ci.

---

## Assumptions

1. Solo los Registradores tienen permiso para importar; los Supervisores no tienen acceso a esta funcionalidad en ninguna circunstancia.
2. Los archivos Excel se procesan con PhpSpreadsheet versión 5.4, que debe estar instalado vía Composer.
3. El año de importación se selecciona en el formulario o se obtiene de la sesión; por defecto se usa el año actual si no hay selección.
4. No se valida duplicidad de observaciones; todas las filas válidas se importan sin verificar existencia previa.
5. El establecimiento se vincula automáticamente (por código, luego por nombre). Si no se encuentra coincidencia, la fila se marca como error.
6. Los campos obligatorios deben estar presentes en cada fila; no hay valores por defecto para campos requeridos.
7. El valor "N/A" en usa_validador es una conveniencia para el usuario y se normaliza a "NO" internamente.
8. La hoja REM (codigo_hoja) es obligatoria para tipos ERROR, REVISAR y F/PLAZO; es opcional para S/OBSERVACION.
9. Los nombres de columna antiguos se mantienen por compatibilidad con archivos Excel creados antes de la migración a los nombres actuales.
10. La tabla `observaciones` usa AUTO_INCREMENT en su PK, por lo que no se requiere gestionar IDs manualmente durante la importación.
