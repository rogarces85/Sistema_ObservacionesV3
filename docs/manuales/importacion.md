# Manual de Usuario - Importación de Observaciones desde Excel

## Descripción

El módulo de Importación permite cargar masivamente observaciones REM desde un archivo Excel (.xlsx o .xls). Este módulo está disponible **exclusivamente para usuarios con rol Registrador**.

## Acceso

1. Inicie sesión en el Sistema de Observaciones REM
2. En el menú lateral, busque la sección **Gestión**
3. Haga clic en **Importar desde Excel**

> **Nota:** Si no ve esta opción en el menú, verifique que su rol sea "Registrador". Los supervisores no tienen acceso a este módulo.

## Paso 1: Descargar la Plantilla

Antes de importar, descargue la plantilla Excel proporcionada por el sistema:

1. Haga clic en el botón **"Descargar Plantilla"** (esquina superior derecha)
2. Se descargará un archivo llamado `plantilla_observaciones_YYYY-MM-DD.xlsx`
3. La plantilla incluye:
   - Hoja **"Observaciones"** con encabezados y datos de ejemplo
   - Hoja **"Instrucciones"** con la guía de uso

### Columnas de la Plantilla

| Columna | Obligatoria | Descripción | Ejemplo |
|---------|-------------|-------------|---------|
| `codigo_establecimiento` | **Sí** | Código DEIS del establecimiento | `125301` |
| `establecimiento` | No (fallback) | Nombre del establecimiento (se usa si no hay código) | `CESFAM Dr. Marcelo Lopetegui Adams` |
| `mes` | **Sí** | Número (1-12) o nombre en español | `Enero` o `1` |
| `codigo_serie` | No | Serie REM | `SERIE A`, `SERIE BM` |
| `codigo_hoja` | No | Código de la hoja REM | `A01`, `BM18` |
| `tipo_error` | **Sí** | Tipo de registro | `S/OBSERVACION`, `ERROR`, `REVISAR`, `F/PLAZO` |
| `detalle_observacion` | No | Descripción de la observación | `Sin observaciones` |
| `plazo_entrega` | No | Estado del plazo | `dentro_plazo`, `fuera_plazo` |

### Valores Válidos

**Tipo de Error:**
- `S/OBSERVACION` - Sin observaciones
- `ERROR` - Error detectado
- `REVISAR` - Requiere revisión
- `F/PLAZO` - Fuera de plazo

**Series REM:**
- `SERIE A`, `SERIE BM`, `SERIE BS`, `SERIE D`, `SERIE ANEXO`, `SERIE P`

**Plazo de Entrega:**
- `dentro_plazo`
- `fuera_plazo`

**Mes:**
- Números: `1` a `12`
- Nombres en español: `Enero`, `Febrero`, `Marzo`, `Abril`, `Mayo`, `Junio`, `Julio`, `Agosto`, `Septiembre`, `Octubre`, `Noviembre`, `Diciembre`

## Paso 2: Preparar el Archivo Excel

1. Abra la plantilla descargada en Excel o LibreOffice Calc
2. Complete las filas con sus datos siguiendo el formato de ejemplo
3. **Importante:**
   - El **año** NO se incluye en el archivo Excel; se selecciona en el formulario web
   - Use el **código DEIS** (`codigo_establecimiento`) siempre que sea posible para mayor precisión
   - Si no conoce el código, puede usar el nombre del establecimiento, pero debe coincidir exactamente con el nombre registrado en el sistema
   - No es necesario validar serie/hoja REM; el sistema acepta cualquier valor

### Ejemplo de Datos

```
| codigo_establecimiento | establecimiento                        | mes    | codigo_serie | codigo_hoja | tipo_error    | detalle_observacion      | plazo_entrega |
|------------------------|----------------------------------------|--------|--------------|-------------|---------------|--------------------------|---------------|
| 125301                 | CESFAM Dr. Marcelo Lopetegui Adams     | Enero  | SERIE A      | A01         | S/OBSERVACION | Sin observaciones        | dentro_plazo  |
| 123130                 | Hospital Base San José de Osorno       | 2      | SERIE BM     | BM18        | ERROR         | Discrepancia en total    | dentro_plazo  |
| 125310                 | CESFAM Quinta Centenario               | Marzo  | SERIE D      | D15         | REVISAR       | Valores a verificar      | fuera_plazo   |
```

## Paso 3: Subir el Archivo

1. Seleccione el **Año** correspondiente en el selector del formulario
2. Arrastre su archivo Excel a la zona de carga **o** haga clic para seleccionarlo
3. El sistema mostrará el nombre y tamaño del archivo seleccionado
4. Si necesita cambiar el archivo, haga clic en **"Cambiar archivo"**

## Paso 4: Generar Vista Previa

1. Haga clic en **"Generar Vista Previa"**
2. El sistema procesará el archivo y mostrará:

### Resumen de Resultados

- **Total filas:** Número total de filas de datos en el archivo
- **Válidas:** Filas que cumplen todos los requisitos y se pueden importar
- **Con errores:** Filas que tienen problemas y NO se importarán
- **Duplicados:** Filas que ya existen en la base de datos o se repiten dentro del archivo

### Tabla de Filas Válidas

Muestra todas las filas que serán importadas con sus datos:
- Número de fila original en el Excel
- Establecimiento (nombre oficial del sistema)
- Mes (convertido a nombre)
- Serie, Hoja, Tipo, Detalle y Plazo

### Tabla de Errores

Muestra las filas que NO se importarán con la descripción del error:
- Fila con mes inválido
- Fila con código de establecimiento no encontrado
- Fila con tipo de error faltante
- etc.

### Alerta de Duplicados

Si se detectan registros duplicados:
- **Duplicados internos:** Misma combinación de establecimiento/mes/serie/hoja/tipo dentro del mismo archivo
- **Duplicados en BD:** Registros que ya existen en la base de datos

## Paso 5: Confirmar Importación

1. Revise la vista previa cuidadosamente
2. Si hay duplicados, puede marcar la opción **"Omitir registros duplicados"** para que solo se importen los registros nuevos
3. Haga clic en **"Confirmar Importación"**
4. Confirme la acción en el diálogo emergente
5. El sistema importará solo las filas válidas
6. Al finalizar, será redirigido automáticamente a la página de Observaciones

### Comportamiento con Duplicados

| Opción | Comportamiento |
|--------|---------------|
| Sin marcar | Se importan todas las filas válidas, incluso si ya existen en BD (se crearán registros duplicados) |
| Marcada | Se omiten las filas que ya existen en BD; solo se importan las nuevas |

## Códigos de Error Comunes

| Error | Causa | Solución |
|-------|-------|----------|
| "Campo 'mes' es requerido" | La columna mes está vacía | Complete el mes en la fila |
| "Mes '13' fuera de rango" | El mes no está entre 1-12 | Use un número válido o nombre en español |
| "Mes 'Jan' no reconocido" | El mes no está en español | Use nombres en español: Enero, Febrero, etc. |
| "Código de establecimiento '999999' no encontrado" | El código DEIS no existe en el sistema | Verifique el código o use el nombre del establecimiento |
| "Establecimiento 'XXX' no encontrado" | El nombre no coincide exactamente | Use el código DEIS o verifique el nombre exacto en el sistema |
| "Campo 'tipo_error' es requerido" | La columna tipo está vacía | Complete con: S/OBSERVACION, ERROR, REVISAR o F/PLAZO |

## Notas Importantes

- **Solo Registradores:** Los usuarios con rol Supervisor no pueden acceder a este módulo
- **Año desde formulario:** El año de las observaciones se toma del selector del formulario web, NO del archivo Excel
- **Sin validación de serie/hoja:** El backend acepta cualquier valor en las columnas de serie y hoja
- **Codificación automática:** PhpSpreadsheet detecta automáticamente la codificación del archivo
- **Transacción atómica:** Si ocurre un error durante la importación, se revierten todos los cambios
- **Estado inicial:** Todas las observaciones importadas se crean con estado "pendiente"
