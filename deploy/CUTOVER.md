# Corte de Ambiente de Desarrollo (B16)

Checklist para cerrar el acceso al ambiente de desarrollo/QA
cuando se abra produccion. Solo aplica si existe un ambiente
paralelo (staging, QA, dev) distinto al de produccion.

## Pre-requisitos

- Produccion en operacion al menos 7 dias sin incidentes
  criticos.
- Equipo de soporte capacitado (B15).
- Backups verificados (al menos un restore de prueba exitoso).
- DNS de produccion estable y certificado HTTPS emitido.
- Documentacion actualizada (DEPLOY, OPERATIONS, SECURITY, CHANGELOG).

## Decision: que hacer con el ambiente dev/QA

| Opcion | Pros | Contras | Recomendado si |
|---|---|---|---|
| Apagarlo completamente | Ahorra recursos, evita confusion | Pierde ambiente para pruebas futuras | Produccion esta consolidada y no hay planes de staging |
| Mantenerlo en modo read-only | Permite consultar historico | Requiere mantenimiento, costo de almacenamiento | Se necesita referencia historica o auditoria |
| Redirigir a un clon sanitizado | Permite pruebas sin afectar prod | Costo de infraestructura | Hay ciclo de releases continuo |
| Solo dejarlo a devs locales | Control total | Pierde la opcion de demo en vivo | El equipo de desarrollo es pequeno |

Recomendacion: para sistemas en produccion critica (salud), se
recomienda mantener un clon sanitizado en modo solo-lectura, NO
un ambiente de desarrollo activo.

## Pasos del corte (segun opcion elegida)

### Opcion A: Apagar dev completamente

```
# En el servidor de desarrollo
sudo systemctl stop apache2
sudo systemctl disable apache2
sudo systemctl stop mysql
sudo systemctl disable mysql
sudo ufw deny in proto tcp from any to any port 80,443,3306
```

- Documentar fecha y hora del corte.
- Mantener los archivos y la BD por 30 dias (para auditoria).
- Borrar snapshots y backups propios del ambiente dev.

### Opcion B: Modo read-only

Modificar el `config.php` del ambiente dev para evitar mutaciones:

```php
define('REM_READ_ONLY', true);
```

Y en cada API verificar:

```php
if (defined('REM_READ_ONLY') && REM_READ_ONLY
    && !in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Ambiente en modo lectura.']);
    exit;
}
```

Ademas: redirigir login a produccion via banner.

### Opcion C: Clon sanitizado

- Levantar un servidor nuevo con la misma imagen base.
- Importar un dump reciente de produccion, sanitizando:
  - Cambiar todos los passwords a uno temporal.
  - Reemplazar RUTs y datos personales.
  - Resetear `historial_usuarios` y `logs` recientes.
- Bloquear envio de emails (SMTP en null).
- Marcar claramente como "ENTORNO DE PRUEBAS" en header y login.

## Limpieza de datos sensibles en dev

Antes de cualquier opcion que mantenga datos del ambiente dev:

```sql
-- Eliminar usuarios demo
DELETE FROM usuarios WHERE username LIKE 'demo%'
    OR password_hash = '<hash de admin123>';

-- Resetear passwords de usuarios que tambien existen en produccion
UPDATE usuarios
SET password_hash = '$2y$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    fecha_actualizacion = NOW()
WHERE username IN (SELECT username FROM usuarios_prod);
```

Reemplazar el hash con el de `password_hash('cambiar123', PASSWORD_DEFAULT)`.

## Borrado de ramas de desarrollo

Una vez que produccion esta estable, las ramas `001-unificar-specs`,
`002-mejorar-reportes-analiticos`, `003-f4-categoria-ux`,
`004-tabler-dashboard-review`, `005-fix-button-actions` y
`backup-main-local-before-integration` son historicas.

Accion:
- Mantenerlas en `origin` para trazabilidad.
- Documentar que `main` es la unica rama soportada.
- Cerrar cualquier PR abierto contra `main` que no sea la auditoria.

## Comunicacion a usuarios

Correo o memo oficial con:

- Fecha y hora del corte del ambiente dev.
- Nueva URL de produccion.
- Politica de primer-login (cambio obligatorio de contrasena).
- Canal de soporte: email, telefono, horario.
- Runbook publico: enlace a OPERATIONS.md (si se decide publicar).

Plantilla:

```
Asunto: [REM] Produccion en operacion - cambio de URL

Estimado/a usuario/a:

A partir del [FECHA], el Sistema de Observaciones REM
estara disponible unicamente en la siguiente URL:

  https://rem.example.cl/

El ambiente de desarrollo/QA sera dado de baja.
Si actualmente tiene una cuenta en el ambiente de desarrollo,
debe cambiar su contrasena en el primer acceso a produccion.

Para soporte: ops@rem.example.cl
Horario: lunes a viernes, 08:00 a 18:00.

Runbook de operacion: [enlace a OPERATIONS.md publico]
```

## Validacion post-corte

- Confirmar que ningun usuario esta entrando al dev.
- Revisar logs de acceso del dev durante 7 dias para detectar
  accesos residuales.
- Cerrar puertos de red del servidor dev en el firewall.
- Cambiar DNS del dev (si existe) para apuntar a un mensaje
  informativo de baja.
- Mantener snapshot del servidor dev por 30 dias para
  auditoria/recuperacion.

## Rollback del corte

Si por algun motivo se requiere reabrir dev:

1. Restaurar desde snapshot guardado.
2. Cambiar DNS nuevamente.
3. Reactivar servicios.
4. Coordinar con usuarios el regreso a dos ambientes.

## Checklist final

- [ ] Produccion estable >= 7 dias
- [ ] Capacitacion completada (B15)
- [ ] Backups verificados con restore
- [ ] DNS de produccion activo y certificado valido
- [ ] Documentacion actualizada
- [ ] Opcion de dev seleccionada y justificada
- [ ] Datos sensibles limpiados
- [ ] Comunicacion a usuarios enviada
- [ ] Acceso a dev cortado (firewall/servicios)
- [ ] Logs del dev monitoreados por 7 dias
- [ ] Decisiones registradas en CHANGELOG.md
