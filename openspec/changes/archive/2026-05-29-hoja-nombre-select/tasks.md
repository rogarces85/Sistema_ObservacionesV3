# Tareas

## Requisito previo: Definir nombres de hojas REM

Antes de codificar, se necesita la lista de nombres descriptivos para cada hoja REM. Para hojas cuyo código ya es descriptivo (ej. `Hoja Control`, `Renombre archivo`, `Hoja Parto_RN`), se usará el mismo código como nombre. Para códigos crípticos (`A01`, `BM18`, `P01`, etc.), se necesita su nombre REM oficial.

## Tareas de implementación

### 1. Reestructurar `$HOJAS_POR_SERIE` en `config/constants.php`

Cambiar de array plano de strings a array de arrays asociativos con `codigo` y `nombre`:

**Antes:**
```php
'SERIE A' => ['A01', 'A02', ...]
```

**Después:**
```php
'SERIE A' => [
    ['codigo' => 'A01', 'nombre' => '...'],
    ['codigo' => 'A02', 'nombre' => '...'],
    ...
]
```

**Archivos:**
- `config/constants.php`

---

### 2. Actualizar `loadHojasREM()` en `views/observaciones.php`

Cambiar el forEach para mostrar `codigo - nombre` en vez del código solo.

**Antes:**
```js
hojas.forEach(hoja => {
    const option = document.createElement('option');
    option.value = hoja;
    option.textContent = hoja;
    hojaSelect.appendChild(option);
});
```

**Después:**
```js
hojas.forEach(hoja => {
    const option = document.createElement('option');
    option.value = hoja.codigo;
    option.textContent = hoja.codigo + ' - ' + hoja.nombre;
    hojaSelect.appendChild(option);
});
```

**Archivos:**
- `views/observaciones.php`

---

### 3. Verificar `editObservation()` en `views/observaciones.php`

Confirmar que `document.getElementById('codigo_hoja').value = obs.codigo_hoja` sigue funcionando (sí, porque `value` sigue siendo el código).

No requiere cambios.

---

### 4. Verificación

- Probar apertura del modal de creación y ver que el select `codigo_hoja` muestre `A01 - Atención Profesional` (o el nombre que corresponda)
- Probar edición de una observación existente para ver que el valor se selecciona correctamente
- Probar creación con `S/OBSERVACION` (debe ocultar el campo hoja)
- Confirmar que el valor guardado en BD sigue siendo solo el código
