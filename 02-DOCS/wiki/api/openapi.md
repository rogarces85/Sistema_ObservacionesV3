# Resumen del Contrato API (OpenAPI)

> **Ubicación:** `02-DOCS/wiki/api/openapi.md`
> **Fuente original:** `docs/spec/openapi.yaml` (línea base autoritativa)
> **Última actualización:** 2026-11-13

---

## 1. Visión general

El contrato formal de la API está en `docs/spec/openapi.yaml` (OpenAPI 3.1.0, validado). Cubre los **15 endpoints** en `api/*.php`. Este archivo es un resumen navegable para humanos.

> **⚠️ Autoridad:** Donde este resumen discrepe con `docs/spec/openapi.yaml`, gana el YAML.

---

## 2. Endpoints por recurso

| Archivo | Recurso | Acciones principales | Autenticación | Roles |
|---------|---------|---------------------|---------------|-------|
| `api/auth.php` | Autenticación | `login`, `logout`, `check`, `set_year` | Sesión / credenciales | Todos |
| `api/observations.php` | Observaciones | `list`, `create`, `get`, `update`, `delete`, `history` | Sesión + CSRF | Registrador (propias), Supervisor (todas) |
| `api/supervision.php` | Supervisión | `approve`, `cancel`, `get`, `delete` (soft) | Sesión + CSRF | Solo Supervisor |
| `api/reports.php` | Reportes | `get` (datos agregados para gráficos) | Sesión | Según alcance |
| `api/export.php` | Exportación | `excel`, `pdf` (síncrono) | Sesión + CSRF | Supervisor |
| `api/import.php` | Importación | `preview`, `confirm` (Excel) | Sesión + CSRF | Supervisor |
| `api/import_template.php` | Plantilla | `get` (descarga .xlsx) | Sesión | Supervisor |
| `api/locations.php` | Ubicaciones | `comunas`, `establecimientos` | Sesión | Supervisor |
| `api/assignments.php` | Asignaciones | `list`, `create`, `update`, `delete`, `temporal`, `copy_year` | Sesión + CSRF | Supervisor |
| `api/users.php` | Usuarios | `list`, `create`, `update`, `activate`, `deactivate`, `delete`, `reset_password`, `audit` | Sesión + CSRF | Supervisor |
| `api/deleted.php` | Papelera | `list`, `restore`, `permanent_delete`, `permanent_delete_multiple` | Sesión + CSRF | Supervisor |
| `api/informe_errores.php` | Informe errores | `trimestral`, `anual` (JSON/PDF) | Sesión | Supervisor |
| `api/notifications.php` | Notificaciones | `list`, `mark_read` | Sesión | Según rol |
| `api/report_queue.php` | Cola reportes | `queue`, `status`, `download` | Sesión + CSRF | Supervisor |
| `api/boletin.php` | Boletín | `generate`, `download` | Sesión | Supervisor |

---

## 3. Formato de respuesta estándar

Todos los endpoints JSON responden:

```json
{
  "success": true,
  "data": { ... },
  "message": "Descripción legible"
}
```

En error:
```json
{
  "success": false,
  "data": null,
  "message": "Descripción del error"
}
```

Códigos HTTP: `200` OK, `400` Bad Request, `401` No autenticado, `403` No autorizado, `404` No encontrado, `500` Error servidor.

---

## 4. Autenticación y sesión

- Basada en `$_SESSION` nativa de PHP.
- `api/auth.php?action=login` crea sesión, regenera ID, setea `logged_in`, `user_id`, `rol`, `anio_trabajo`.
- `api/auth.php?action=check` valida sesión viva (usado por frontend para keep-alive).
- `api/auth.php?action=set_year` cambia `anio_trabajo` en sesión.
- Cookie de sesión: `httponly`, `samesite=Lax`, `secure` en producción.

---

## 5. CSRF

**Obligatorio en todo request mutante** (POST, PUT, DELETE) desde el navegador.

- Token generado al login, almacenado en `$_SESSION['csrf_token']`.
- Se envía vía header `X-CSRF-Token` o campo POST `csrf_token`.
- Validación en `includes/csrf.php::validateRequest()`.
- Si falla → `403` con `success: false`.

---

## 6. Modelos de datos principales

### Observación
```json
{
  "id": 123,
  "establecimiento_id": 45,
  "mes": "Enero",
  "anio": 2026,
  "tipo_error": "ERROR",
  "codigo_hoja": "H01",
  "detalle_observacion": "Texto...",
  "estado": "pendiente",
  "registrador_id": 10,
  "fecha_registro": "2026-01-15 10:30:00",
  "fecha_revision": null,
  "supervisor_id": null,
  "respuesta_establecimiento": null
}
```

**Estados:** `pendiente`, `aprobado`, `rechazado`, `error`, `justificado`

**Tipos error:** `S/OBSERVACION`, `ERROR`, `REVISAR`, `F/PLAZO`

### Usuario
```json
{
  "id": 10,
  "usuario": "registrador1",
  "nombre": "Juan Pérez",
  "email": "juan@ejemplo.cl",
  "rol": "registrador",
  "activo": true,
  "ultimo_acceso": "2026-11-10 08:00:00"
}
```

**Roles:** `registrador`, `supervisor`

### Asignación
```json
{
  "id": 1,
  "usuario_id": 10,
  "establecimiento_id": 45,
  "anio": 2026,
  "meses": "1,2,3,4,5,6,7,8,9,10,11,12",
  "tipo_asignacion": "anual",
  "fecha_inicio": "2026-01-01",
  "fecha_fin": null
}
```

**Tipos:** `anual`, `temporal`

---

## 7. Reglas de negocio expuestas en API

1. **Dual-typed months:** `observaciones.mes` = nombre (`"Enero"`); `asignaciones.meses` = CSV números (`"1,2,3"`) o `"ALL"`. Ver `EstablecimientoAsignacion::tieneMes()`.
2. **Temporal > Anual:** `tieneAsignacionParaMes()` revisa temporales antes. Una temporal a otro usuario bloquea al titular anual para ese est/mes.
3. **Soft delete:** DELETE en observaciones y supervision mueve a `observaciones_eliminadas` (recuperable). Solo `deleted.php?action=permanent_delete` con `confirm_irreversible: true` borra irreversiblemente.
4. **Registrador edita propias en cualquier estado** → resetea a `pendiente`.
5. **Aprobar requiere `estado_resultante`:** `sin_observacion` → `aprobado` + `S/OBSERVACION`; `error` → `error` + `ERROR`. No hay `reject` directo (403).
6. **`codigo_hoja` obligatorio** salvo `tipo_error === 'S/OBSERVACION'`.
7. **Import valida asignación mensual** igual que creación manual.

---

## 8. Paginación y filtros

- `list` endpoints aceptan: `page`, `per_page` (default 50), `search`, `estado`, `mes`, `anio`, `establecimiento_id`, `comuna_id`.
- Respuesta incluye: `data[]`, `pagination: { total, per_page, current_page, last_page }`.

---

## 9. Exportación

- Síncrona: `api/export.php?format=excel|pdf&...filtros...`
- Asíncrona: `api/report_queue.php` encola → `worker_reportes.php` procesa → `api/report_queue.php?action=download&id=...`

---

## 10. Referencia completa

Ver `docs/spec/openapi.yaml` para:
- Esquemas completos (components/schemas)
- Parámetros de query y body por endpoint
- Códigos de respuesta por operación
- Ejemplos de request/response