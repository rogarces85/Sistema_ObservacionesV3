## ADDED Requirements

### Requirement: Dashboard actualiza datos automáticamente
El dashboard SHALL implementar auto-refresh periódico que recargue estadísticas, gráficos y tabla cada 2 minutos sin recargar la página completa.

#### Scenario: Auto-refresh activado actualiza datos
- **WHEN** el auto-refresh está activado y pasan 2 minutos
- **THEN** el sistema SHALL hacer fetch de nuevos datos vía AJAX
- **THEN** los números de stat cards SHALL animar su cambio si difieren
- **THEN** los gráficos ApexCharts SHALL actualizarse con nuevos datos
- **THEN** la tabla de últimas observaciones SHALL actualizarse

#### Scenario: Toggle de auto-refresh en header
- **WHEN** el usuario hace clic en el toggle de auto-refresh
- **THEN** el sistema SHALL activar/desactivar el intervalo de actualización
- **THEN** el estado del toggle SHALL persistir en localStorage

#### Scenario: Auto-refresh pausa en pestaña oculta
- **WHEN** el usuario cambia a otra pestaña del navegador
- **THEN** el auto-refresh SHALL pausarse para ahorrar recursos
- **WHEN** el usuario vuelve a la pestaña
- **THEN** el auto-refresh SHALL reanudarse desde donde quedó

#### Scenario: Badge de última actualización
- **WHEN** el dashboard realiza una actualización
- **THEN** un badge en el header SHALL mostrar "Actualizado hace X seg"
- **THEN** el contador SHALL incrementar cada segundo
