## Why

El README.md está desactualizado respecto a los cambios implementados en las últimas iteraciones (v2.3.0). Faltan referencias a los nuevos archivos en la estructura del proyecto, las nuevas funcionalidades de supervisión, y la entrada en el historial de versiones. Adicionalmente, `openspec/config.yaml` no tiene configurado el contexto del proyecto, lo que limita la calidad de los artefactos generados automáticamente.

## What Changes

- **README.md**: Actualizar versión a 2.3.0, agregar entrada v2.3.0 en historial, documentar nuevas funcionalidades (selector de estado en aprobación, campos en modal de detalle), agregar archivos faltantes en estructura (Version.php, ReportQueue.php, UserAudit.php, versioning.php)
- **openspec/config.yaml**: Agregar contexto del proyecto (tech stack, convenciones, dominio) usando la información del README

## Capabilities

### New Capabilities
<!-- None — documentation/config only -->

### Modified Capabilities
<!-- None — no requirement changes -->

## Impact

- `README.md`: Actualización de documentación (no afecta código)
- `openspec/config.yaml`: Configuración de OpenSpec (no afecta código)
