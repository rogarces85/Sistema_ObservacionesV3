# Contrato de UI: Estados de Carga y Error por Categoría

**Branch**: `[003-f4-categoria-ux]` | **Date**: 2026-06-17
**Spec**: [spec.md](spec.md) | **Addendum de**: `specs/002-mejorar-reportes-analiticos/contracts/ui-reportes-analiticos.md`

## Propósito

Documenta los estados explícitos de UI (cargando, error) por cada una de las cinco categorías analíticas, junto con los selectores DOM, las clases CSS y los handlers JS asociados. Es un **addendum** del contrato UI del feature 002; no modifica el contrato previo.

## Estados de UI por categoría

Cada categoría tiene un div `[data-estado-categoria="<categoria>"]` que puede contener uno de tres estados visibles:

### Estado 1: Cargando (spinner)

**Cuándo se muestra**: mientras la consulta de la categoría está en curso.

**HTML esperado**:
```html
<div class="reportes-analytics__estado reportes-analytics__estado--loading" data-estado-categoria="errores_establecimiento" role="status" aria-live="polite">
    <div class="spinner-border spinner-border-sm text-primary me-2" aria-hidden="true"></div>
    <span>Cargando Errores por establecimiento...</span>
</div>
```

**Reglas**:
- Atributo `role="status"` y `aria-live="polite"` para accesibilidad (lectores de pantalla anuncian el cambio).
- `aria-hidden="true"` en el spinner visual para que el lector de pantalla lea solo el texto.
- Texto en español: "Cargando {titulo_categoria}..." usando el título de `CATEGORIAS_ANALITICAS[categoria].titulo`.
- `spinner-border-sm` para tamaño discreto (no dominar visualmente el panel).

**Función JS** (nueva en `assets/js/reportes.js`):
```javascript
function setEstadoCargando(categoria) {
    const estado = document.querySelector(`[data-estado-categoria="${categoria}"]`);
    if (!estado) return;
    const titulo = CATEGORIAS_ANALITICAS[categoria]?.titulo || categoria;
    estado.className = 'reportes-analytics__estado reportes-analytics__estado--loading';
    estado.setAttribute('role', 'status');
    estado.setAttribute('aria-live', 'polite');
    estado.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm text-primary me-2" aria-hidden="true"></div>
            <span>Cargando ${escapeHtml(titulo)}...</span>
        </div>
    `;
}
```

### Estado 2: Error recuperable

**Cuándo se muestra**: cuando la consulta de la categoría falla (HTTP no-2xx, timeout, error de red, JSON inválido).

**HTML esperado**:
```html
<div class="reportes-analytics__estado reportes-analytics__estado--error" data-estado-categoria="errores_establecimiento" role="alert">
    <p class="mb-2">No fue posible cargar esta categoría.</p>
    <button type="button" class="btn btn-sm btn-primary reportes-analytics__retry-button" data-reintentar-categoria="errores_establecimiento">
        <i class="ti ti-refresh me-1"></i> Reintentar
    </button>
</div>
```

**Reglas**:
- `role="alert"` para accesibilidad (lectores anuncian el error).
- Mensaje en español, lenguaje natural: "No fue posible cargar esta categoría."
- Botón "Reintentar" con icono Tabler `ti-refresh` (sin nuevas dependencias).
- `data-reintentar-categoria="<categoria>"` como selector para el handler de evento (event delegation).

**Función JS** (nueva):
```javascript
function setEstadoError(categoria, mensaje = 'No fue posible cargar esta categoría.') {
    const estado = document.querySelector(`[data-estado-categoria="${categoria}"]`);
    if (!estado) return;
    estado.className = 'reportes-analytics__estado reportes-analytics__estado--error';
    estado.setAttribute('role', 'alert');
    estado.innerHTML = `
        <p class="mb-2">${escapeHtml(mensaje)}</p>
        <button type="button" class="btn btn-sm btn-primary reportes-analytics__retry-button" data-reintentar-categoria="${categoria}">
            ${tablerIcon('refresh')} Reintentar
        </button>
    `;
}
```

**Función JS** (handler de reintento, nueva):
```javascript
async function recargarCategoria(categoria) {
    setEstadoCargando(categoria);
    setBotonReintentarHabilitado(categoria, false);
    try {
        const response = await fetchAPI('api/reports.php', {
            method: 'GET',
            params: construirParams(obtenerFiltrosAnaliticos(), { report: 'reportes-analiticos', categoria })
        });
        if (response.success) {
            if (response.data.categorias[categoria].resultados.length === 0) {
                setEstadoAnalitico(categoria, response.data.categorias[categoria].mensaje || 'No hay datos para los filtros seleccionados.', 'muted');
            } else {
                renderizarCategoriaExitosa(categoria, response.data.categorias[categoria]);
            }
        } else {
            setEstadoError(categoria, response.message);
        }
    } catch (error) {
        setEstadoError(categoria);
    } finally {
        setBotonReintentarHabilitado(categoria, true);
    }
}

function setBotonReintentarHabilitado(categoria, habilitado) {
    const boton = document.querySelector(`[data-reintentar-categoria="${categoria}"]`);
    if (boton) boton.disabled = !habilitado;
}

// Event delegation (en la inicialización del módulo Reportes)
document.querySelectorAll('[data-panel-categoria]').forEach(panel => {
    panel.addEventListener('click', (e) => {
        if (e.target.matches('[data-reintentar-categoria]')) {
            recargarCategoria(e.target.dataset.reintentarCategoria);
        }
    });
});
```

### Estado 3: Listo (sin cambios respecto al feature 002)

**Cuándo se muestra**: la categoría cargó exitosamente con datos.

**Comportamiento**: idéntico al feature 002. El indicador de éxito (`"N filas agregadas"`) se mantiene.

**Función JS** (existente, sin cambios): `setEstadoAnalitico(categoria, mensaje, 'success')`.

### Estado 4: Vacío (sin cambios respecto al feature 002)

**Cuándo se muestra**: la categoría cargó exitosamente pero sin datos para los filtros activos.

**Comportamiento**: idéntico al feature 002. Mensaje: "No hay datos para los filtros seleccionados."

**Función JS** (existente, sin cambios): `setEstadoAnalitico(categoria, mensaje, 'muted')`.

## Clases CSS (nuevas, BEM)

En `assets/css/tabler-override.css`:

```css
.reportes-analytics__estado--loading {
    /* Spinner visible con texto; hereda estilos de .reportes-analytics__estado */
    padding: 0.75rem 0;
    color: var(--tblr-secondary, #6c757d);
}

.reportes-analytics__estado--error {
    padding: 0.75rem 0;
    color: var(--tblr-danger, #dc3545);
}

.reportes-analytics__retry-button {
    /* Reusa .btn .btn-sm .btn-primary de Tabler; sin estilos adicionales */
}
```

## Selectores CSS / JS clave

| Selector | Tipo | Uso |
|----------|------|-----|
| `[data-estado-categoria="<categoria>"]` | Atributo | Contenedor de estado de cada categoría |
| `[data-reintentar-categoria="<categoria>"]` | Atributo | Botón "Reintentar" de cada categoría |
| `.reportes-analytics__estado` | Clase | Clase base (existente) |
| `.reportes-analytics__estado--loading` | Clase modificadora (nueva) | Estado de carga |
| `.reportes-analytics__estado--error` | Clase modificadora (nueva) | Estado de error |
| `.reportes-analytics__retry-button` | Clase (nueva) | Botón de reintento |

## Compatibilidad con el feature 002

- ✅ No se elimina ninguna función existente.
- ✅ `setEstadoAnalitico(categoria, mensaje, 'success'|'muted')` se mantiene para los estados `listo` y `vacio`.
- ✅ `cargarReportesAnaliticos()` (carga inicial de las 5) sigue usando `setEstadoCargando` y `setEstadoError` en lugar de `setEstadoAnaliticoTodos('Cargando...')`.
- ✅ El contrato de API no cambia.

## Criterios de aceptación del contrato

- [ ] Spinner DOM contiene `role="status"` y `aria-live="polite"`.
- [ ] Error DOM contiene `role="alert"`.
- [ ] Botón "Reintentar" tiene `data-reintentar-categoria` con el nombre de la categoría.
- [ ] Click en "Reintentar" dispara `recargarCategoria(categoria)`.
- [ ] `recargarCategoria` usa los filtros activos en cada intento.
- [ ] Botón se deshabilita durante la consulta y se rehabilita al terminar.
- [ ] Clases BEM respetadas (verificable con grep en `tabler-override.css`).
- [ ] Sin estilos inline en el HTML generado.
