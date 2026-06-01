# API Contract: Autenticación

**Endpoint**: `api/auth.php`

## POST api/auth.php?action=login
**Request**: `{ "username": "...", "password": "..." }`
**Response 200**: `{ "success": true, "data": { "usuario": {...}, "token_csrf": "..." } }`
**Response 401**: `{ "success": false, "error": "Credenciales inválidas", "code": 401 }`
**Response 429**: `{ "success": false, "error": "Demasiados intentos. Espere 30s", "code": 429 }`

## POST api/auth.php?action=logout
**Response 200**: `{ "success": true }`

## GET api/auth.php?action=check
**Response 200**: `{ "success": true, "data": { "usuario": {...}, "token_csrf": "..." } }`
**Response 401**: `{ "success": false, "error": "Sesión expirada", "code": 401 }`

## POST api/auth.php?action=change_year
**Request**: `{ "anio": 2026 }`
**Response 200**: `{ "success": true, "data": { "anio": 2026 } }`
