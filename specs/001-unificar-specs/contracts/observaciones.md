# API Contract: Observaciones

**Endpoint**: `api/observaciones.php`

## GET api/observaciones.php?anio=2026&pagina=1&limite=50
**Response 200**: `{ "success": true, "data": { "items": [...], "total": N, "pagina": 1, "limite": 50, "total_paginas": M } }`

## GET api/observaciones.php?id=5
**Response 200**: `{ "success": true, "data": { "id": 5, ... } }`
**Response 404**: `{ "success": false, "error": "Observación no encontrada", "code": 404 }`

## GET api/observaciones.php?action=historial&id=5
**Response 200**: `{ "success": true, "data": [ { "estado_anterior": "...", "estado_nuevo": "...", ... } ] }`

## GET api/observaciones.php?action=stats&anio=2026
**Response 200**: `{ "success": true, "data": { "total": 100, "pendientes": 30, ... } }`

## POST api/observaciones.php
**Request**: `{ "establecimiento_id": 1, "anio": 2026, "mes": 3, "codigo_serie": "...", ... }`
**Response 201**: `{ "success": true, "data": { "id": 5, ... } }`
**Response 400**: `{ "success": false, "error": "...", "code": 400 }`
**Response 403**: `{ "success": false, "error": "No tiene permisos para este establecimiento/mes", "code": 403 }`

## PUT api/observaciones.php?id=5
**Request**: `{ "detalle_observacion": "Corregido", ... }`
**Response 200**: `{ "success": true, "data": { "id": 5, ... } }`
**Response 403**: `{ "success": false, "error": "No puede editar una observación en estado no pendiente", "code": 403 }`

## DELETE api/observaciones.php?id=5
**Response 200**: `{ "success": true }`
**Response 403**: `{ "success": false, "error": "No tiene permisos para eliminar esta observación", "code": 403 }`
