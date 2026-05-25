## ADDED Requirements

### Requirement: Vista de Reportes de Errores

El sistema DEBE mostrar una vista de reportes enfocada exclusivamente en errores con 5 gráficos y filtros multi-select.

**Filtros disponibles:**
- Año (selector único)
- Meses (checkboxes, selección múltiple)
- Comunas (selector múltiple)

**Gráficos incluidos:**

| # | Título | Tipo | Datos |
|---|--------|------|-------|
| ① | Errores por Establecimiento | Bar horizontal | Nombre establecimiento + conteo de errores (`tipo_error = 'ERROR'`) |
| ② | Fuera de Plazo por Establecimiento | Bar vertical | Nombre establecimiento + conteo fuera de plazo (`plazo_entrega = 'fuera_plazo'`) |
| ③ | No usa Validador por Establecimiento | Bar vertical | Nombre establecimiento + conteo sin validador (`usa_validador = 'no'`) |
| ④ | Errores por Serie REM | Bar horizontal | Nombre serie REM + conteo de errores (`tipo_error = 'ERROR'`) |
| ⑤ | Errores por Hoja REM | Bar vertical | Nombre hoja REM + conteo de errores (`tipo_error = 'ERROR'`) |

**Layout:** Gráficos ①-④ en grid de 2 columnas. Gráfico ⑤ en ancho completo.

Cada gráfico DEBE incluir una tabla de datos debajo con los mismos valores.

La vista DEBE respetar la visibilidad por rol (Registrador solo ve sus datos, Supervisor ve todos).

#### Scenario: Supervisor filtra por año, meses y comunas
- **WHEN** un Supervisor selecciona año 2026, meses Enero y Febrero, comunas Osorno y Purranque
- **AND** presiona "Aplicar Filtros"
- **THEN** los 5 gráficos se actualizan mostrando solo datos que coinciden con todos los filtros
- **AND** cada gráfico muestra su tabla de datos correspondiente

#### Scenario: Registrador ve solo sus datos
- **WHEN** un Registrador accede a la vista de reportes de errores
- **THEN** todos los gráficos muestran solo sus propias observaciones
- **AND** los filtros de comuna solo muestran comunas donde tiene datos

#### Scenario: Gráfico sin datos muestra mensaje
- **WHEN** los filtros aplicados no producen resultados para un gráfico específico
- **THEN** el gráfico muestra un mensaje "Sin datos para los filtros seleccionados"
- **AND** la tabla correspondiente muestra "—" en todas las celdas

#### Scenario: Gráfico de hoja REM con muchas barras
- **WHEN** existen más de 15 hojas REM con errores
- **THEN** el canvas del gráfico ⑤ ajusta su altura para mostrar todas las barras sin colapsar
- **AND** se aplica scroll vertical si es necesario
