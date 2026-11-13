---
type: constitution
title: respaldo_observaciones — Constitution
description: Written by rsc onboard; ratified to start v1.0.0.
tags: [sdd, constitution]
timestamp: 2026-10-30T14:44:15Z
topic: sdd
status: ratified
version: 1.0.0
---

# respaldo_observaciones — Constitution

## Known facts (from onboarding)

- **Goal**: Sistema de registro y observaciones de redes
- **Project kind**: software · **software scope**: small
- **How code changes land**: straight on the default branch (`--workflow main`).
- **Stack**: PHP 8.2, MySQL 8+, Tabler CSS v3, Apache 2.4

## 1. Stack canon

1. **PHP**: ^8.2 (versión mínima; se recomienda 8.3 para nuevas características)
2. **MySQL**: ^8.0 (via PDO; wrapper en `models/` para queries seguras)
3. **CSS framework**: Tabler v3 (CDN + overrides en `assets/css/`)
4. **Servidor**: Apache 2.4 (con `mod_rewrite` y `.htaccess`)
5. **PHP extensiones obligatorias**: `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`
6. **Entornos**: `.env.example` (nunca commitear `.env` real); `config/` para overrides

## 2. Quality bar

1. **Tests unitarios**: 100% de coverage en `models/` y `views/` (PHPUnit + assertions críticas)
2. **Lint**: `phpcs` (PSR-12) en cada commit; `phpmd` rule set incluida en `.github/workflows/`
3. **Type checking**: PHPStan level 5+ opcional pero recomendado en CI
4. **Codigo duplication**: < 3% duplicado (detectado por `phpmd` / `phpcpd`)
5. **CI gate**: `verify.sh` debe pasar antes de cualquier merge a main

## 3. Conventions

1. **Naming**: 
   - Archivos: `snake_case` (ej: `exporter.php`)
   - Clases: `StudlyCase` (ej: `Database.php`)
   - Funciones: `lowercase_with_underscores` (ej: `calcular_total()`)
2. **Directory structure**:
   - `models/` - lógica de BD (Singleton, DAO pattern)
   - `views/` - renderizado Tabler (PHP templates heredados)
   - `controllers/` - flow control (mínima lógica de negocio)
   - `assets/` - CSS/JS/tokens
3. **Git commits**:
   - `gitmoji: ✨` para features, `🐛` para bugs, `📦` para docs
   - Commit messages en pasado: "Add observación export" no "Adds"
4. **Error handling**:
   - Excepciones propias en `models/` (extiende `RuntimeException`)
   - Nunca `die()` en capa de presentación
   - Log a `views/` con contexto suficiente
5. **Security**:
   - Input validation en todos los inputs usuario (models/)
   - Preparadas todas queries SQL (PDO prepared statements)
   - CSRF tokens en forms (session-based)
   - Headers de seguridad: `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`
6. **Deprecation**:
   - Registry en `02-DOCS/wiki/sdd/deprecation-log.md`
   - `rsc deprecation` skill para retire paths con compatibilidad

## 4. Branching & shipping

- **Workflow**: straight on `main` (definido en onboarding `--workflow main`)
- **Pull requests**: obligatorias para cualquier cambio que modifique `models/` o `views/`
- **Code review**: al menos 1 approval + passing `verify` gate
- **Feature branches**: `git checkout -b feature/<short-desc>`; merged via squash PR
- **Hotfix**: `git checkout -b hotfix/<issue>; main`; patch + PR

## 5. Security & privacy floor

1. **Authentication**: sesión PHP nativa + regen_id después login; passwords mínimo 8 chars, 1 mayúscula, 1 número
2. **Authorization**: verificación RBAC en cada endpoint sensible (models/)
3. **SQL injection**: PDO prepared statements obligatorios; `raw queries` prohibidos en `controllers/`
4. **XSS**: output escapado con `htmlspecialchars()` en todos los views; `htmlentities()` para atributos HTML
5. **CSRF**: tokens hidden en forms POST; validación en `controllers/`
6. **Passwords**: nunca stored en claro; `password_hash()` / `password_verify()` (PHP 8.2+)
7. **Data privacy**: datos sensibles (email, nombres) en BD enmascarados por defecto en logs
8. **Headers de seguridad** (apache2 conf o .htaccess):
   ```
   Header always set X-Content-Type-Options "nosniff"
   Header always set X-Frame-Options "DENY"
   Header always set Referrer-Policy "strict-origin-when-cross-origin"
   ```

## 6. UX / accessibility floor (if there is a UI)

1. **Contrast**: razón mínima 4.5:1 para texto normal, 3:1 para grande (WCAG AA)
2. **Keyboard navigation**: todos los interactive elements focusables (tabindex gestionado)
3. **Screen readers**: labels descriptivos en todos los form inputs; `aria-label` o `aria-labelledby` cuando sea necesario
4. **Focus indicator**: outline visible en focus (personalizado en `assets/css/tabler-override.css`)
5. **Dark mode**: soporte via `data-bs-theme` en `<html>`; tokens CSS respetados (no valores fijos light-only)
6. **Responsive**: minimum viewport 320px; components reflowen graceful antes de breakpoints

## 7. Performance budgets (where they matter)

1. **Página principal**: Time to First Byte < 800ms (con caché de queries repetidas)
2. **Listado de observaciones**: < 2s para < 100 registros (con paginación LIMIT 50 offset)
3. **Generación PDF**: < 5s por reporte (librería `tecnickcom/tcpdf`; optimizar fonts incrustadas)
4. **API endpoints**: respuesta < 300ms (queries indexadas; `EXPLAIN` en desarrollo)
5. **Optimizaciones**:
   - `opcache.enable=1` en producción
   - Composer `autoload Optimize` (`--optimize-autoloader`)
   - Compression gzip en Apache (`mod_deflate` o `mod_zlib`)
   - Imágenes en WebP/AVIF con fallback JPEG/PNG

## 8. Knowledge & decisions

- **Tabler selection**: evaluado vs Bootstrap 5, Tailwind, Material UI. Ganó Tabler por:
  - Sin dependencia de build toolchain (CDN sufficient)
  - Documentación health/public-sector friendly
  - Menor peso KB comparado con Bootstrap/Tailwind
- **PHP sobre Node/Python**: decisión de stack existente; equipo con experiencia PHP; migración futura evaluada en `rsc reassess`
- **Apache sobre Nginx/IIS**: heredado del entorno de producción; switches evaluados pero no críticos
- **Fase siguiente**: `specify` phase para definir primera feature (usuario/observación CRUD) antes de plan

## Definition of Done (the merge bar `verify` runs against)

Un feature/bugfix está listo para merge cuando:

1. ✅ **Task checklist** completada (cada item marcado done contra proof observado)
2. ✅ **Tests unitarios** passing en `models/` coverage ≥ 80%
3. ✅ **PHPStan** level 5 sin errors nuevos (si está habilitado en CI)
4. ✅ **phpcs** PASSED (PSR-12, 0 errors)
5. ✅ **Security review**: ningún nuevo vulnerability introducido ( revisar OWASP Top 10 list )
6. ✅ **Docs updated**: `02-DOCS/wiki/` reflecta el cambio (nuevo endpoint, nuevo campo, etc.)
7. ✅ **Amendment log** actualizado (ver sección 8 anterior)
8. ✅ **`verify.sh` gate** pasa completamente (lint + type + test + audit)
9. ✅ **No breaking changes** en API pública (si aplica) o **backward compatibility** declarada

## Amendment log (append-only)

| Date | Version | Change | Why |
|------|---------|--------|-----|
| 2026-10-30 | 1.0.0 | Constitution ratified after onboard | Establishes floor rules for SDD phases; prevents drift; sets quality & security bars |
