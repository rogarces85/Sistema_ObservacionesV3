## Context

El array `$HOJAS_POR_SERIE` en `config/constants.php` es un array plano (`string[]`) donde cada elemento es solo el código de la hoja. El select `codigo_hoja` en el formulario de creación usa `hojasPorSerie` (JS) para generar options donde `value = textContent = codigo`. No hay concepto de nombre descriptivo.

## Goals / Non-Goals

**Goals:**
- Cambiar `$HOJAS_POR_SERIE` a estructura con `codigo` + `nombre`
- Mostrar en el select: `codigo - nombre` (ej. `A01 - Atención Profesional`)
- Mantener `codigo` como `value` del option (lo que se guarda en BD)
- Aplicar a todas las series existentes

**Non-Goals:**
- No modificar la BD (columna `codigo_hoja` sigue igual)
- No modificar vistas de detalle, reportes, exportación ni importación
- No cambiar APIs ni modelos

## Decisions

### 1. Estructura de datos

**Antes:**
```php
'SERIE A' => ['A01', 'A02', 'A03', ...]
```

**Después:**
```php
'SERIE A' => [
    ['codigo' => 'A01', 'nombre' => 'Atención Profesional'],
    ['codigo' => 'A02', 'nombre' => '...'],
    ...
]
```

### 2. Impacto en JS

`hojasPorSerie` se genera con `json_encode()`. La nueva estructura anidada se serializa naturalmente a JSON. En `loadHojasREM()`, cambiar:

```js
// Antes
option.value = hoja;
option.textContent = hoja;

// Después
option.value = hoja.codigo;
option.textContent = hoja.codigo + ' - ' + hoja.nombre;
```

### 3. Nombres a definir

Cada hoja necesita un nombre descriptivo. Para hojas que ya tienen nombre descriptivo como código (ej. `Hoja Control`, `Renombre archivo`, `Hoja Parto_RN`), se puede repetir el mismo valor como nombre o usar una versión más legible. Para códigos crípticos (`A01`, `BM18`, `P01`), se deben definir nombres según la tabla REM oficial.

## Risks / Trade-offs

- **Carga de datos:** La estructura anidada aumenta ligeramente el tamaño del JS embebido, pero es marginal (~2KB adicional).
- **Sin cambios en BD:** El `value` del option sigue siendo `codigo`, por lo que el valor almacenado no cambia.
- **Nombres pendientes:** Se deben definir los nombres para todas las hojas antes de completar la implementación.

## Open Questions

- ¿Cuáles son los nombres descriptivos oficiales de cada hoja REM? Se necesitan para completar `$HOJAS_POR_SERIE`.
