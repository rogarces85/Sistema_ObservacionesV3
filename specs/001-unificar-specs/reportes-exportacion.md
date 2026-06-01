# MOD-EXP: Reportes y Exportación

## User Scenarios & Testing

### Historia de Usuario
Como usuario del sistema, necesito generar reportes y exportar datos de observaciones en diferentes formatos, para analizar la información fuera del sistema o presentarla a autoridades.

### Escenarios de Aceptación

#### HU-EXP-001: Exportar reporte general en Excel
**Prioridad: P1**

```gherkin
Dado un usuario autenticado (Registrador o Supervisor)
  Y existen observaciones en el sistema
Cuando selecciona filtros (año obligatorio, mes, estado, establecimiento)
  Y elige formato "Excel"
  Y presiona "Exportar"
Entonces el sistema genera un archivo .xlsx con una fila por observación
  Y el archivo incluye las columnas estándar (ID, Año, Mes, Establecimiento, Comuna, Código Serie, Código Hoja, Tipo Error, Detalle, Plazo Entrega, Usa Validador, Estado, Clasificación, Registrador, Supervisor, Fecha Registro)
  Y el nombre del archivo incluye timestamp (Ej: Observaciones_REM_2026_2026-05-25_143022.xlsx)
  Y la descarga se inicia automáticamente en el navegador
```

#### HU-EXP-002: Exportar reporte general en PDF
**Prioridad: P1**

```gherkin
Dado un usuario autenticado
Cuando selecciona filtros y formato "PDF" para reporte general
Entonces el sistema genera un archivo .pdf con tabla de observaciones
  Y el PDF incluye el logo SSO en el encabezado
  Y los encabezados usan color institucional azul #005288
  Y la descarga se inicia automáticamente
```

#### HU-EXP-003: Exportar reporte general en CSV
**Prioridad: P2**

```gherkin
Dado un usuario autenticado
Cuando selecciona filtros y formato "CSV"
Entonces el sistema genera un archivo .csv con BOM UTF-8 y delimitador punto y coma
  Y la descarga se inicia automáticamente
```

#### HU-EXP-004: Exportar reporte específico (analítico) en Excel
**Prioridad: P1**

```gherkin
Dado un usuario autenticado
Cuando selecciona un reporte específico (errores_mes, errores_establecimiento, errores_comuna, fuera_plazo_mes, fuera_plazo_establecimiento, fuera_plazo_comuna, validador_mes, validador_establecimiento, validador_comuna, serie_detalle, hoja_detalle)
  Y formato "Excel"
Entonces el sistema genera un archivo .xlsx con datos agrupados según la dimensión seleccionada
  Y el nombre refleja el tipo de reporte (Ej: Errores_por_Mes_2026_2026-05-25_143022.xlsx)
```

#### HU-EXP-005: Exportar reporte detallado PDF (jerárquico)
**Prioridad: P1**

```gherkin
Dado un usuario autenticado
Cuando selecciona filtros y solicita "PDF Detallado"
Entonces el sistema genera un PDF en orientación horizontal, tamaño A4
  Y los datos se agrupan jerárquicamente: Comuna → Establecimiento → Mes con celdas fusionadas
  Y cada fila tiene color de fondo según el estado (verde = aprobado, amarillo = pendiente, rojo = rechazado, azul = observaciones con respuesta del establecimiento)
  Y el encabezado usa fondo rojo oscuro (#8B1A1A) con texto blanco
  Y se repite el encabezado cada ~35 filas
  Y el PDF incluye logo SSO y sección de firma para Jefa de Subdepto.
  Y muestra el total de registros al final del documento
```

#### HU-EXP-006: Visualizar reportes en pantalla con gráficos
**Prioridad: P2**

```gherkin
Dado un usuario autenticado
Cuando accede a la vista de reportes (views/reportes.php)
Entonces ve 5 pestañas: "Errores por Establecimiento", "Plazos Entrega", "Uso Validador", "Errores por Serie", "Errores por Hoja"
  Y cada pestaña muestra un gráfico ApexCharts y una tabla de datos
  Y puede filtrar por año, trimestre, mes, comuna y establecimiento
  Y desde cada gráfico puede exportar a Excel individualmente
```

#### HU-EXP-007: Generar Informe Errores REM (Supervisor)
**Prioridad: P1**

```gherkin
Dado un usuario autenticado con rol Supervisor
Cuando solicita el Informe Errores REM trimestral (Q1-Q4) o anual
  Y selecciona formato JSON
Entonces el sistema retorna datos paginados (20 registros por página) para visualización web
  Y puede navegar entre páginas

Cuando selecciona formato PDF
Entonces el sistema genera un PDF profesional con logo SSO
  Y tabla jerárquica con código de colores
  Y sección de firma para Jefa de Subdepto.
```

#### HU-EXP-008: Registrador solo ve sus propios datos
**Prioridad: P1**

```gherkin
Dado un usuario autenticado con rol Registrador
  Y existen observaciones de otros registradores en el sistema
Cuando genera cualquier reporte o exportación
Entonces los resultados incluyen solo sus propias observaciones
  Y no incluye datos de otros registradores
```

### Casos Borde

| Caso | Entrada | Resultado Esperado |
|------|---------|-------------------|
| Sin datos para filtros | Filtros sin observaciones coincidentes | Responder HTTP 200 con `{"data":[], "total":0, "message":"No se encontraron observaciones para los filtros seleccionados"}` |
| Formato inválido | Formato no soportado (ej: XML) | Responder HTTP 400 con mensaje "Formato no válido" |
| Sin autenticación | Usuario no logueado | Responder HTTP 401 |
| Sesión expirada | Token/ sesión inválida | Redirigir a login |
| Supervisor sin datos de registro | Supervisor sin observaciones propias | Ver todos los datos del sistema |
| Volumen grande de datos | >10,000 registros | Generar reporte completo. Si supera 50,000 registros, mostrar mensaje: "La exportación supera el límite de 50,000 registros. Ajuste los filtros para reducir el alcance." |

---

## Requirements

### Requerimientos Funcionales

| ID | Descripción | Método | Endpoint |
|----|------------|--------|----------|
| FR-EXP-001 | El sistema debe permitir exportar reporte general en Excel (.xlsx), PDF y CSV con filtros por año (obligatorio), mes, estado y establecimiento | GET | api/export.php?format={format}&report_type=general |
| FR-EXP-002 | El sistema debe generar 11 reportes específicos en Excel: errores_mes/establecimiento/comuna, fuera_plazo_mes/establecimiento/comuna, validador_mes/establecimiento/comuna, serie_detalle, hoja_detalle | GET | api/export.php?format=excel&report_type={tipo} |
| FR-EXP-003 | El sistema debe generar un PDF detallado jerárquico (Comuna→Establecimiento→Mes) con celdas fusionadas, colores por estado y logo SSO | GET | api/export.php?report_type=detallado |
| FR-EXP-004 | El sistema debe generar el Informe Errores REM en formato JSON (paginado, 20/page) y PDF profesional con logo SSO, tabla jerárquica y sección de firma | GET | api/informe_errores.php?periodo={trimestre\|anual}&format={json\|pdf} |
| FR-EXP-005 | El sistema debe restringir el acceso al Informe Errores REM solo a usuarios con rol Supervisor | — | api/informe_errores.php |
| FR-EXP-006 | El sistema debe filtrar datos según el rol: Registrador ve solo sus observaciones, Supervisor ve todas | — | — |
| FR-EXP-007 | El sistema debe mostrar 5 pestañas de reportes con gráficos ApexCharts y tabla de datos en la vista de reportes | GET | views/reportes.php |
| FR-EXP-008 | El sistema debe generar nombre de archivo automático con timestamp para todas las exportaciones | — | — |
| FR-EXP-009 | El sistema debe incluir el logo SSO en los reportes PDF | — | — |
| FR-EXP-010 | El sistema debe usar códigos de color en PDFs: verde (#E8F5E9) para aprobado, amarillo (#FFF3E0) para pendiente, rojo (#FFEBEE) para rechazado, azul (#E3F2FD) para justificado | — | — |
| FR-EXP-011 | El sistema debe incluir sección de firma para Jefa de Subdepto. en el Informe Errores REM PDF | — | — |
| FR-EXP-012 | El sistema debe generar CSV con BOM UTF-8 y delimitador punto y coma | GET | api/export.php?format=csv |

### Entidades Clave

#### observaciones (solo lectura para reportes)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK) | Identificador único |
| año | INT | Año de la observación |
| mes | VARCHAR(20) | Mes de la observación |
| establecimiento_id | INT (FK) | Referencia al establecimiento |
| comuna | VARCHAR(255) | Comuna del establecimiento (calculada vía JOIN: observaciones → establecimientos → comunas, no almacenada directamente) |
| codigo_serie | VARCHAR(50) | Código de serie REM |
| codigo_hoja | VARCHAR(50) | Código de hoja REM |
| tipo_error | VARCHAR(50) | Tipo de error (ERROR, REVISAR, F/PLAZO, S/OBSERVACION) |
| detalle_observacion | TEXT | Descripción de la observación |
| plazo_entrega | DATE | Fecha de plazo de entrega |
| usa_validador | ENUM('si','no') | Indica si usa validador |
| estado | ENUM('pendiente','aprobado','error','rechazado') | Estado de supervisión. El PDF detallado usa color azul para observaciones con respuesta del establecimiento (no es un estado real) |
| registrador_id | INT (FK) | Usuario que registró |
| supervisor_id | INT (FK) (nullable) | Usuario que supervisó |
| fecha_registro | DATETIME | Fecha de creación |

### Roles y Permisos

| Función | Supervisor | Registrador |
|---------|:----------:|:-----------:|
| Exportar reporte general | ✅ Todos los datos | ✅ Solo propios |
| Exportar reportes específicos | ✅ Todos los datos | ✅ Solo propios |
| Exportar PDF detallado | ✅ Todos los datos | ✅ Solo propios |
| Ver reportes en pantalla (5 pestañas) | ✅ Todos los datos | ✅ Solo propios |
| Generar Informe Errores REM | ✅ | ❌ |

---

## Success Criteria

1. Un usuario puede exportar un reporte general en Excel, PDF y CSV con los filtros seleccionados y el archivo se descarga en < 5 segundos para datasets de hasta 5,000 registros.
2. Los 11 reportes específicos se generan correctamente en Excel con los datos agrupados por la dimensión solicitada.
3. El PDF detallado jerárquico muestra correctamente la estructura Comuna→Establecimiento→Mes con celdas fusionadas y colores de fondo según el estado.
4. El Informe Errores REM incluye logo SSO, tabla jerárquica con código de colores y sección de firma; la versión JSON retorna datos paginados (20/page).
5. Un Registrador nunca ve datos de otros registradores en ningún reporte o exportación.
6. Un Supervisor ve todos los datos del sistema sin restricciones.
7. El acceso al Informe Errores REM está restringido exclusivamente a Supervisores (código 403 para Registradores).
8. La vista de reportes muestra 5 pestañas funcionales con gráficos ApexCharts y tablas de datos sincronizadas con los filtros.
9. Los archivos generados tienen nombres descriptivos con timestamp y son descargables directamente.
10. El sistema responde con código de error apropiado (400, 401, 404) cuando no es posible generar el reporte.

---

## Clarifications

### Session 2026-06-01

- Q: ¿Exportación síncrona o por cola asíncrona? → A: Híbrido: ≤ 1,000 registros síncrono, > 1,000 pasa por cola ReportQueue automáticamente.
- Q: ¿Estado "justificado" es un estado real? → A: No es estado real. El PDF usa azul para observaciones con respuesta del establecimiento. Estados reales: pendiente, aprobado, error, rechazado.
- Q: ¿Comuna se almacena directamente o vía JOIN? → A: Vía JOIN (observaciones → establecimientos → comunas). No almacenar en observaciones.
- Q: ¿Código HTTP cuando no hay datos? → A: 200 con `{"data":[], "total":0, "message":"..."}`.
- Q: ¿Límite máximo de registros exportables? → A: 50,000 registros máximo. Si se excede, advertir al usuario y sugerir reducir filtros.

---

## Assumptions

1. La generación de reportes es híbrida: exportaciones con ≤ 1,000 registros son síncronas (descarga directa). Si supera 1,000 registros, pasa automáticamente por la cola asíncrona (ReportQueue) y se notifica al usuario cuando esté listo.
2. Límite máximo de exportación: 50,000 registros. Si el resultado excede este límite, se muestra mensaje al usuario solicitando ajustar filtros.
3. Los reportes en pantalla (vista reportes.php) pueden tener paginación en la tabla de datos, pero los gráficos muestran todos los datos del período seleccionado.
4. El logo SSO está disponible como archivo de imagen accesible desde el servidor.
5. El color institucional azul #005288 se usa en encabezados de PDF y elementos visuales del módulo.
6. Fuera de plazo y uso de validador se reportan como cantidades absolutas, no proporcionales.
7. El Informe Errores REM es una funcionalidad exclusiva de Supervisores y no está disponible para Registradores ni siquiera como lectura.
8. Los datos de observaciones se mantienen en la tabla `observaciones` y no se requiere un cache separado para reportes.
9. Para volúmenes >10,000 registros, el tiempo de generación puede superar los 5 segundos; se asume suficiente capacidad de memoria y tiempo de ejecución en el servidor.
