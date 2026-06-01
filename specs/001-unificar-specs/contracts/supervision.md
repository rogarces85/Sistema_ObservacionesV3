# API Contract: Supervisión

**Endpoint**: `api/supervision.php`

## GET api/supervision.php?action=get_filtered&pagina=1&limite=50
**Params**: estado, establecimiento_id, usuario_registro_id, search, anio, mes
**Response 200**: `{ "success": true, "data": { "items": [...], "total": N, "pagina": 1, "limite": 50, "total_paginas": M } }`

## GET api/supervision.php?action=get_detail&id=5
**Response 200**: `{ "success": true, "data": { observacion: {...}, historial: [...] } }`

## POST api/supervision.php?action=approve
**Request**: `{ "id": 5, "estado_resultante": "aprobado", "clasificacion": "...", "detalle_error": "..." }`
**Response 200**: `{ "success": true }`

## POST api/supervision.php?action=cancel
**Request**: `{ "id": 5, "comentario": "..." }`
**Response 200**: `{ "success": true }`

## POST api/supervision.php?action=delete
**Request**: `{ "id": 5 }`
**Response 200**: `{ "success": true, "data": { "movido_a_papelera": true } }`

## POST api/supervision.php?action=update_status
**Request**: `{ "id": 5, "nuevo_estado": "...", "comentario": "..." }`
**Response 200**: `{ "success": true }`

## POST api/supervision.php?action=approve (masivo)
**Request**: `{ "ids": [5, 6, 7], "estado_resultante": "aprobado" }`
**Response 200**: `{ "success": true, "data": { "procesados": 3, "fallos": [] } }`
