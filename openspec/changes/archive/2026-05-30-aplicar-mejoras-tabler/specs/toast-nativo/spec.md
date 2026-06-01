## ADDED Requirements

### Requirement: Sistema de notificaciones con toasts nativos
El sistema SHALL usar toasts nativos de Bootstrap 5/Tabler en lugar del sistema custom de `notifications.js`.

#### Scenario: Toast de éxito
- **WHEN** una operación se completa exitosamente
- **THEN** SHALL mostrar un toast verde con ícono de check y el mensaje de éxito
- **AND** el toast SHALL auto-ocultarse después de 4 segundos

#### Scenario: Toast de error
- **WHEN** una operación falla
- **THEN** SHALL mostrar un toast rojo con ícono de error y el mensaje de error
- **AND** el toast SHALL permanecer visible hasta que el usuario lo cierre manualmente

#### Scenario: Toast de advertencia
- **WHEN** se necesita alertar al usuario sin bloquear
- **THEN** SHALL mostrar un toast amarillo con ícono de advertencia

#### Scenario: Toast informativo
- **WHEN** se necesita informar al usuario
- **THEN** SHALL mostrar un toast azul con ícono de información

### Requirement: Contenedor de toasts posicionado
Los toasts SHALL renderizarse en un contenedor fijo en la esquina superior derecha.

#### Scenario: Posición de toasts
- **WHEN** se muestra un toast
- **THEN** SHALL aparecer en la esquina superior derecha del viewport
- **AND** múltiples toasts SHALL apilarse verticalmente
- **AND** el contenedor SHALL tener `z-index` suficiente para estar sobre modales

### Requirement: API compatible con notificaciones existentes
El nuevo sistema de toasts SHALL mantener la misma API pública (`showSuccess`, `showError`, `showWarning`, `showInfo`).

#### Scenario: Compatibilidad de API
- **WHEN** el código existente llama a `showSuccess("mensaje")`
- **THEN** SHALL mostrar un toast nativo de éxito
- **AND** no SHALL requerir cambios en el código que llama a estas funciones
