## 1. UI Structure

- [x] 1.1 Crear estructura HTML de nav-tabs en `views/reportes.php` con 5 pestañas
- [x] 1.2 Mover cada gráfico (canvas + tabla) a su contenedor de pestaña correspondiente
- [x] 1.3 Mantener filtros globales arriba de las pestañas, visibles siempre
- [x] 1.4 Agregar estilos CSS para tabs activo/inactivo y responsive
- [x] 2.1 Implementar función `switchTab(tabId)` que muestra/oculta contenido de pestañas
- [x] 2.2 Implementar lazy loading: cargar datos solo al activar pestaña por primera vez
- [x] 2.3 Adaptar `loadErrorReports()` para recibir parámetro de pestaña activa
- [x] 2.4 Guardar estado de pestaña activa en `location.hash` para persistencia al recargar
- [x] 2.5 Asegurar que filtros se mantengan al cambiar de pestaña
- [x] 3.1 Verificar que cada pestaña muestra su gráfico correctamente
- [x] 3.2 Probar lazy loading (primera carga vs cambio entre pestañas ya cargadas)
- [x] 3.3 Probar filtros aplicados desde diferentes pestañas
- [x] 3.4 Verificar responsive en móvil (scroll horizontal o menú desplegable)
- [x] 3.5 Verificar que al recargar la página se mantiene la pestaña activa (hash)
