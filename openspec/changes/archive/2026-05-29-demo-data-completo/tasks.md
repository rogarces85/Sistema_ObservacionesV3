## 1. Asignaciones demo

- [x] 1.1 Agregar función `seedAsignaciones()` en `seed_demo.php` que asigne establecimientos existentes al registrador demo (códigos: 101, 102, 201) con asignación anual
- [x] 1.2 Verificar que el script no duplica asignaciones si ya existen

## 2. Observaciones demo

- [x] 2.1 Agregar función `seedObservaciones()` que cree 1 observación en cada estado (pendiente, aprobado, rechazado, error, justificado) para el registrador demo
- [x] 2.2 Verificar que usa valores válidos de constantes del sistema (series, hojas, plazos)
- [x] 2.3 Agregar función `seedHistorial()` que cree entradas en `historial_estados` para observaciones con estado distinto a pendiente, asociadas al supervisor demo

## 3. Integración y verificación

- [x] 3.1 Integrar las funciones en el flujo principal de `seed_demo.php` (ejecutar después de crear usuarios)
- [x] 3.2 Ejecutar script y verificar datos creados en la base de datos
- [x] 3.3 Verificar que re-ejecutar no duplica datos
- [ ] 3.4 Verificar visualmente que dashboard, supervisión y reportes muestran datos
