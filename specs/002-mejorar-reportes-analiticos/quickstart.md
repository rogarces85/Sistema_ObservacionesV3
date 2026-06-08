# Quickstart: Mejorar Reportes Analiticos

## Prerrequisitos

- Usuario autenticado con acceso al modulo Reportes.
- Datos de observaciones disponibles para al menos un año de trabajo.
- Entorno XAMPP operativo con Apache y MySQL.

## Flujo de Verificacion Manual

1. Iniciar sesion como supervisor.
2. Abrir la seccion Reportes.
3. Confirmar que se muestran cinco categorias analiticas: errores por establecimiento, plazos de entrega, uso de validador, errores por serie y errores por hoja.
4. Aplicar filtro por año y confirmar que todas las categorias usan ese año.
5. Aplicar filtro por comuna y confirmar que el filtro de establecimiento solo muestra establecimientos de esa comuna.
6. Seleccionar cada categoria y confirmar que mantiene los filtros activos.
7. Verificar que cada categoria muestra resumen visual y tabla.
8. Exportar una categoria con datos y confirmar que el archivo refleja los filtros visibles.
9. Aplicar filtros sin datos y confirmar que se muestra estado vacio y no se permite exportar.
10. Iniciar sesion como registrador y confirmar que no se muestran ni exportan datos fuera de su alcance.

## Criterios de Aceptacion Rapida

- Los textos visibles estan en español.
- Los totales coinciden entre grafico, tabla y exportacion.
- Cambiar de categoria no limpia filtros.
- La pantalla conserva exportacion general e informe de errores para supervisores.
- No hay cambios de esquema de base de datos.

## Comandos de Apoyo

```powershell
php -l api/reports.php
php -l api/export.php
php -l views/reportes.php
```

## Evidencia Esperada

- Captura de filtros aplicados.
- Captura de cada categoria con grafico y tabla.
- Archivo exportado de una categoria con datos.
- Validacion de alcance con usuario registrador.
