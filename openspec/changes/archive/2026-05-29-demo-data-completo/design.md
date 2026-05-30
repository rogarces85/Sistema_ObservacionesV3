## Context

Actualmente `seed_demo.php` solo crea usuarios demo (registrador + supervisor). No hay datos de prueba en el sistema: el dashboard muestra todo en cero, los reportes están vacíos, la supervisión no tiene observaciones que revisar, y el registrador demo no tiene establecimientos asignados.

El `config/init_db.sql` ya incluye comunas y establecimientos reales del Servicio de Salud Osorno, que deben reutilizarse.

## Goals / Non-Goals

**Goals:**
- Extender `seed_demo.php` para poblar todas las tablas con datos demo realistas
- Asignar establecimientos al registrador demo (al menos 3-4)
- Crear observaciones demo en múltiples estados (pendiente, aprobado, rechazado, error, justificado)
- Crear historial de cambios para observaciones con estado modificado
- Usar el año actual para los datos
- Idempotente: re-ejecutable sin duplicar datos

**Non-Goals:**
- No modificar el modelo de datos existente
- No agregar tablas nuevas
- No modificar API, vistas o controladores
- No generar datos masivos (solo los necesarios para demostrar funcionalidad)

## Decisions

| Decisión | Opción elegida | Alternativas | Razón |
|----------|---------------|--------------|-------|
| Año de los datos | Año actual (`date('Y')`) | Año fijo | Siempre visible en el selector de año; evita confusiones |
| Cantidad de observaciones | ~8-10 observaciones | 1-2 o 30+ | Suficientes para llenar dashboard, tablas y gráficos sin saturar |
| Estados cubiertos | Todos (pendiente, aprobado, rechazado, error, justificado) | Solo pendientes | Para que todas las vistas (dashboard, supervisión, reportes) tengan datos significativos |
| Asignaciones | Asignación anual (ALL) a demo_registrador | Asignación temporal | Es el caso más común; demo_registrador puede ver establecimientos asignados |
| Protección | Misma que seed_demo: solo en development | Sin protección | Consistente con la seguridad existente |

## Risks / Trade-offs

- **Riesgo:** Los IDs de usuarios y establecimientos pueden variar entre instalaciones.  
  **Mitigación:** El script busca por username (`demo_registrador`, `demo_supervisor`) y por código de establecimiento, no por ID.
- **Riesgo:** Re-ejecutar el script puede duplicar observaciones.  
  **Mitigación:** El script verifica si ya existen observaciones para el registrador demo en el año actual y las omite.
- **Trade-off:** Datos fijos vs. aleatorios. Se opta por datos fijos (contenido predecible) para que el demo sea consistente entre ejecuciones.
