# Especificación: MOD-EXP — Exportación y Reportes

## Historia de Usuario

> **Como** usuario del sistema (Supervisor o Registrador),
> **necesito** generar y descargar reportes de observaciones en formatos Excel y PDF,
> **para** analizar tendencias de errores, presentar informes formales y realizar auditorías de calidad.

---

## Descripción General

El módulo de Exportación permite generar archivos descargables basados en los datos de observaciones. Soporta tres categorías de reportes: **General** (listado plano), **Detallado** (jerárquico para impresión) y **Analíticos** (agrupados por métricas).

El sistema genera archivos en formato **Excel (.xlsx)** y **PDF**. Para garantizar la estabilidad del sistema con grandes volúmenes de datos, la generación de reportes es **asíncrona**: el usuario solicita el reporte, continúa trabajando y recibe una notificación cuando el archivo está listo para descargar.

---

## Funciones del Módulo

### EXP-001: Exportar Reporte General

**Descripción**: Genera un listado plano de todas las observaciones que coinciden con los filtros activos.

**Reglas de Negocio**:
- **Formatos**: Excel (.xlsx) y PDF.
- **Contenido**: Una fila por observación con todas las columnas visibles en el listado.
- **Filtros**: Respeta los filtros de Año, Mes, Estado, Establecimiento y Comuna.
- **Volumen**: Exporta todos los registros coincidentes, sin límite de paginación.

### EXP-002: Exportar Reporte Detallado (PDF Jerárquico)

**Descripción**: Genera un documento PDF estructurado jerárquicamente para presentación formal.

**Reglas de Negocio**:
- **Formato**: Exclusivamente PDF.
- **Estructura**: Agrupa los datos por Comuna → Establecimiento → Mes.
- **Uso**: Diseñado para imprimir y firmar como informe oficial.
- **Filtros**: Permite filtrar por año y establecimientos específicos.

### EXP-003: Exportar Reportes Analíticos

**Descripción**: Genera reportes agrupados por métricas específicas (Errores, Fuera de Plazo, Validador, Series).

**Reglas de Negocio**:
- **Formatos**: Excel (.xlsx) y PDF.
- **Tipos Disponibles**:
  - Errores por Mes/Establecimiento/Comuna.
  - Fuera de Plazo por Mes/Establecimiento/Comuna.
  - Uso de Validador por Mes/Establecimiento/Comuna.
  - Detalle por Serie REM y Hoja REM.
- **Contenido**: Tablas resumen con conteos y porcentajes.

### EXP-004: Gestión de Descargas (Asíncrono)

**Descripción**: Maneja la cola de generación de reportes y notifica al usuario.

**Reglas de Negocio**:
- **Solicitud**: El usuario configura el reporte y presiona "Generar".
- **Procesamiento**: El sistema procesa en segundo plano.
- **Notificación**: Una vez listo, aparece un aviso (campana o banner) con el enlace de descarga.
- **Expiración**: Los archivos generados están disponibles por un tiempo limitado (ej. 24 horas) y luego se eliminan automáticamente del servidor.

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Exportar General | ✅ Solo propios | ✅ Todos |
| Exportar Detallado | ✅ Solo propios | ✅ Todos |
| Exportar Analíticos | ✅ Solo propios | ✅ Todos |
| Ver Notificaciones | ✅ | ✅ |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-EXP-001 | Solicitud recibida | `Su reporte se está generando. Le notificaremos cuando esté listo.` |
| MSG-EXP-002 | Reporte listo | `El reporte "[Nombre]" está listo para descargar.` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-EXP-101 | Sin datos | `Error: No hay datos que coincidan con los filtros seleccionados.` |
| MSG-EXP-102 | Permiso denegado | `Error: No tiene permisos para acceder a estos datos.` |
| MSG-EXP-103 | Error de generación | `Error: No se pudo generar el reporte. Intente nuevamente.` |

---

## Escenarios BDD (Gherkin)

### Escenario: Generación asíncrona de reporte grande
```gherkin
Dado que soy un Supervisor autenticado
Y existen más de 10,000 observaciones en el sistema
Cuando solicito un "Reporte General" en formato Excel
Entonces el sistema muestra el mensaje "Su reporte se está generando..."
Y el sistema procesa el archivo en segundo plano
Y eventualmente recibo una notificación "El reporte está listo para descargar"
```

### Escenario: Exportar reporte detallado PDF
```gherkin
Dado que soy un Registrador autenticado
Y tengo observaciones registradas en el año actual
Cuando selecciono "Reporte Detallado" y elijo el formato PDF
Entonces el sistema genera un documento jerárquico agrupado por Comuna y Establecimiento
Y el archivo contiene solo mis observaciones
```

### Escenario: Intentar exportar sin datos
```gherkin
Dado que soy un Supervisor autenticado
Y aplico filtros que no coinciden con ninguna observación
Cuando intento generar un reporte
Entonces el sistema muestra el error "No hay datos que coincidan con los filtros seleccionados"
Y no se inicia ningún proceso de generación
```

---

## Mockup ASCII

### Pantalla de Selección de Reportes

```
+==============================================================================+
|  EXPORTACIÓN Y REPORTES                                                      |
+==============================================================================+
|                                                                              |
|  TIPO DE REPORTE:                                                            |
|  ( ) General (Listado plano)                                                 |
|  ( ) Detallado (PDF Jerárquico)                                              |
|  (x) Analítico - Errores por Establecimiento                                 |
|                                                                              |
|  FORMATO:                                                                    |
|  [x] Excel (.xlsx)    [ ] PDF                                                |
|                                                                              |
|  FILTROS:                                                                    |
|  Año: [ 2026 ▼ ]   Mes: [ Todos ▼ ]   Estado: [ Todos ▼ ]                   |
|                                                                              |
|                          [ Generar Reporte ]                                 |
|                                                                              |
+==============================================================================+
```

### Notificación de Reporte Listo

```
+----------------------------------------------------------------+
|  🔔 Notificaciones                                            |
+----------------------------------------------------------------+
|  • Hace 2 min: Su reporte "Errores_Marzo.xlsx" está listo.    |
|    [ Descargar ]                                               |
|  • Hace 1 hora: Reporte "General_2026.pdf" expirará en 23h.   |
|    [ Descargar ]                                               |
+----------------------------------------------------------------+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Formatos soportados | ✅ Modificada → **Solo Excel (.xlsx) y PDF** |
| 2 | Tipos de Reporte | ✅ Aceptada → **General, Detallado y Analíticos** |
| 3 | Permisos y Datos | ✅ Aceptada → Registrador: propios, Supervisor: todos |
| 4 | Filtros | ✅ Aceptada → Respetan filtros activos |
| 5 | Volumen | ✅ Aceptada → Sin límite de paginación |
| 6 | Diseño Detallado | ✅ Aceptada → Estructura fija jerárquica |
| 7 | Nombre de archivo | ✅ Aceptada → Automático con timestamp |
| 8 | Tiempo de generación | ✅ Modificada → **Asíncrono con notificación** |