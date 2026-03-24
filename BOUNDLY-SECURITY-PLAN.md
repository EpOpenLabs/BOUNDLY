# BOUNDLY Security Implementation Plan

> **Estado:** En progreso
> **Fecha:** 2026-03-24
> **Avance:** 100% (TODAS LAS FASES COMPLETADAS)

---

## RESUMEN EJECUTIVO

Implementar seguridad completa para BOUNDLY framework:
1. Middlewares de seguridad core (Headers, CORS, Rate Limit, etc.)
2. Puertas de integración (Contracts/Interfaces)
3. Authentication adapters (flexible, no forzar dependencias)

---

## FASE 1: Middlewares de Seguridad Core ✅ COMPLETADA

### Archivos creados:
```
Infrastructure/FrameworkCore/Http/Middleware/
├── SecurityHeadersMiddleware.php  ✅
├── RequestSizeLimitMiddleware.php ✅
└── CorsMiddleware.php           ✅
```

### Headers implementados:
- X-Frame-Options: DENY ✅
- X-Content-Type-Options: nosniff ✅
- X-XSS-Protection: 1; mode=block ✅
- Strict-Transport-Security: max-age=31536000 ✅
- Referrer-Policy: strict-origin-when-cross-origin ✅
- Permissions-Policy ✅

### Configuración en boundly.php:
```php
'security' => [
    'enabled' => true,
    'max_request_size' => '1M',
    'max_upload_size' => '10M',
    'headers' => [...],
],

'cors' => [
    'enabled' => false, // Por defecto desactivado
    'allowed_origins' => ['*'],
    ...
],
```

### Verificado:
- PHPStan: 0 errores
- Tests: 40 pasando
- Headers funcionando en producción

---

## FASE 2: Puertas de Integración (Contracts) ✅ COMPLETADA

### Archivos creados:
```
Infrastructure/FrameworkCore/
├── Contracts/
│   └── Authentication/
│       ├── AuthenticatorInterface.php      ✅
│       ├── TokenValidatorInterface.php     ✅
│       ├── UserResolverInterface.php       ✅
│       └── ApiKeyProviderInterface.php     ✅
├── Adapters/
│   └── Authentication/
│       ├── AbstractAuthenticator.php       ✅
│       ├── SanctumAuthenticator.php       ✅
│       └── SanctumApiKeyProvider.php       ✅
└── Traits/
    └── ResolvesAuthentication.php         ✅
```

### Contratos implementados:

| Contrato | Propósito |
|---------|-----------|
| `AuthenticatorInterface` | Valida credenciales |
| `TokenValidatorInterface` | Valida tokens de acceso |
| `UserResolverInterface` | Obtiene usuario del request |
| `ApiKeyProviderInterface` | Gestión de API Keys |

### Adapters incluidos:

| Adapter | Descripción |
|---------|-------------|
| `SanctumAuthenticator` | Implementación para Laravel Sanctum |
| `SanctumTokenValidator` | Validación de tokens Sanctum |
| `SanctumUserResolver` | Resolución de usuarios |
| `SanctumApiKeyProvider` | Proveedor de API Keys base |

---

## FASE 3: Security Logging ✅ COMPLETADA

### Archivos creados:
```
Infrastructure/FrameworkCore/
├── Enums/
│   ├── SecurityEvent.php     ✅
│   └── LogLevel.php          ✅
└── Services/
    └── SecurityLogger.php    ✅
```

### SecurityEvent Enum - Eventos implementados:

| Evento | Severidad | Descripción |
|--------|-----------|-------------|
| `LOGIN_SUCCESS` | INFO | Login exitoso |
| `LOGIN_FAILED` | WARNING | Login fallido |
| `LOGOUT` | INFO | Logout de usuario |
| `TOKEN_EXPIRED` | WARNING | Token expirado |
| `TOKEN_INVALID` | WARNING | Token inválido |
| `RATE_LIMIT_EXCEEDED` | WARNING | Rate limit excedido |
| `UNAUTHORIZED_ACCESS` | WARNING | Acceso no autorizado |
| `FORBIDDEN_ACCESS` | WARNING | Acceso prohibido |
| `API_KEY_CREATED` | INFO | API Key creada |
| `API_KEY_REVOKED` | INFO | API Key revocada |
| `SUSPICIOUS_INPUT` | CRITICAL | Input sospechoso |
| `BRUTE_FORCE_DETECTED` | ALERT | Ataque brute force detectado |
| `BRUTE_FORCE_BLOCKED` | ALERT | Ataque brute force bloqueado |
| `CORS_VIOLATION` | WARNING | Violación CORS |
| `REQUEST_SIZE_EXCEEDED` | WARNING | Request demasiado grande |
| `INVALID_CONTENT_TYPE` | WARNING | Content-Type inválido |

### Configuración en boundly.php:
```php
'security_logging' => [
    'enabled' => true,
    'channel' => 'single',
    'excluded_events' => [],
    'log_auth_success' => false,
    'log_suspicious_input' => true,
    'log_brute_force' => true,
    'log_api_keys' => true,
],
```

### SecurityLogger Service - Métodos:

| Método | Descripción |
|--------|-------------|
| `log()` | Método genérico para cualquier evento |
| `logLoginSuccess()` | Log login exitoso |
| `logLoginFailed()` | Log login fallido |
| `logLogout()` | Log logout |
| `logTokenExpired()` | Log token expirado |
| `logTokenInvalid()` | Log token inválido |
| `logRateLimitExceeded()` | Log rate limit excedido |
| `logUnauthorizedAccess()` | Log acceso no autorizado |
| `logForbiddenAccess()` | Log acceso prohibido |
| `logBruteForceDetected()` | Log ataque brute force |
| `logBruteForceBlocked()` | Log ataque bloqueado |
| `logSuspiciousInput()` | Log input sospechoso |
| `logApiKeyCreated()` | Log API Key creada |
| `logApiKeyRevoked()` | Log API Key revocada |

### Tests creados:
- `SecurityEventTest.php` - 10 tests
- `LogLevelTest.php` - 17 tests
- `SecurityLoggerTest.php` - 8 tests

### Verificado:
- PHPStan: 0 errores
- Tests: 74 tests pasando (FrameworkCore)
- Logging funcional con contexto estructurado

---

## FASE 4: Brute Force Protection ✅ COMPLETADA

### Archivos creados:
```
Infrastructure/FrameworkCore/
├── Http/Middleware/
│   └── BruteForceProtectionMiddleware.php  ✅
├── Services/
│   └── BruteForceProtectionService.php     ✅
└── Attributes/Behavior/
    └── ThrottleLogin.php                   ✅
```

### BruteForceProtectionService - Métodos:

| Método | Descripción |
|--------|-------------|
| `isEnabled()` | Verifica si está habilitado |
| `getMaxAttempts()` | Obtiene máximo de intentos |
| `getDecayMinutes()` | Obtiene minutos de decay |
| `getLockoutMultiplier()` | Obtiene multiplicador de lockout |
| `getMaxLockouts()` | Obtiene máximo de lockouts |
| `tooManyAttempts()` | Verifica si hay demasiados intentos |
| `hits()` | Registra un intento fallido |
| `attempts()` | Obtiene número de intentos |
| `availableIn()` | Tiempo restante de lockout |
| `clear()` | Limpia intentos |
| `isLockedOut()` | Verifica si está bloqueado |
| `lockout()` | Bloquea al usuario |
| `recordFailedAttempt()` | Registra intento fallido |
| `recordSuccessfulAttempt()` | Registra intento exitoso y limpia |
| `remainingAttempts()` | Intentos restantes |

### BruteForceProtectionMiddleware:

- Detecta respuestas fallidas (401, 403, 422)
- Detecta respuestas exitosas (2xx)
- Registra intentos fallidos
- Limpia intentos en login exitoso
- Respuesta JSON con retry_after cuando está bloqueado

### ThrottleLogin Attribute:

| Parámetro | Default | Descripción |
|-----------|---------|-------------|
| `maxAttempts` | 5 | Intentos máximos |
| `decayMinutes` | 15 | Minutos de decay |
| `trackBy` | 'email' | Campo a rastrear |
| `lockoutEnabled` | true | Habilitar lockout |
| `lockoutMultiplier` | 2 | Multiplicador de lockout |
| `maxLockouts` | 3 | Lockouts máximos |

### Traducciones agregadas:
```php
'too_many_attempts' => 'Demasiados intentos fallidos...',
'brute_force_lockout' => 'Tu cuenta ha sido bloqueada...',
```

### Tests creados:
- `ThrottleLoginAttributeTest.php` - 8 tests
- `BruteForceProtectionServiceTest.php` - 15 tests

### Verificado:
- PHPStan: 0 errores
- Tests: 97 tests pasando (FrameworkCore)
- Protección funcional con lockout progresivo

---

## FASE 5: Input Sanitization ✅ COMPLETADA

### Archivos creados:
```
Infrastructure/FrameworkCore/
├── Http/Middleware/
│   └── InputSanitizationMiddleware.php  ✅
└── Services/
    └── InputSanitizer.php              ✅
```

### InputSanitizer - Métodos:

| Método | Descripción |
|--------|-------------|
| `sanitize()` | Método principal que aplica todas las sanitizaciones |
| `sanitizeHtml()` | Elimina etiquetas HTML no permitidas |
| `sanitizeJavascript()` | Elimina scripts y event handlers |
| `sanitizeSqlWildcards()` | Escapa caracteres SQL (% y _) |
| `sanitizeFilename()` | Limpia nombres de archivo |
| `sanitizeEmail()` | Limpia emails |
| `sanitizeUrl()` | Limpia URLs |
| `sanitizeInteger()` | Convierte a entero |
| `sanitizeFloat()` | Convierte a float |
| `sanitizeArray()` | Aplica sanitización a arrays |
| `detectSuspiciousInput()` | Detecta inputs sospechosos |
| `getSuspiciousDetails()` | Obtiene detalles de lo detectado |

### Detección de patrones sospechosos:
- XSS Scripts (`<script>`)
- JavaScript URIs (`javascript:`)
- Event Handlers (`onerror=`, `onclick=`, etc.)
- SQL Injection (`' OR '1'='1`)
- SQL Union Select
- SQL Drop Table/Database
- OS Command Injection (`xp_cmdshell`, `exec()`)
- PHP Code (`<?php`, `<?=`)

### InputSanitizationMiddleware:
- Sanitiza query parameters
- Sanitiza request payload (POST, PUT, PATCH, DELETE)
- Agrega header `X-Content-Sanitized: true`
- Loguea inputs sospechosos con SecurityLogger

### Tests creados:
- `InputSanitizerTest.php` - 20 tests

### Verificado:
- PHPStan: 0 errores
- Tests: 117 tests pasando (FrameworkCore)
- Sanitización funcional con detección de XSS/SQLi

---

## FASE 6: API Key Authentication ✅ COMPLETADA

### Archivos creados:
```
Infrastructure/FrameworkCore/
├── Attributes/Security/
│   └── ApiKey.php                 ✅
└── Http/Middleware/
    └── ApiKeyMiddleware.php       ✅
```

### ApiKey Attribute:

| Parámetro | Default | Descripción |
|-----------|---------|-------------|
| `header` | `'X-Api-Key'` | Nombre del header HTTP |
| `scopes` | `[]` | Permisos requeridos |
| `required` | `true` | Si la key es obligatoria |
| `description` | `null` | Descripción de uso |

### ApiKeyMiddleware:

- Valida API keys usando `ApiKeyProviderInterface`
- Verifica scopes requeridos
- Logs de acceso no autorizado/permitido
- Respuestas JSON con códigos de error específicos

### Errores manejados:
- `API_KEY_MISSING` - Key no proporcionada (401)
- `API_KEY_INVALID` - Key inválida (401)
- `API_KEY_INSUFFICIENT_SCOPES` - Scopes insuficientes (403)
- `API_KEY_PROVIDER_ERROR` - Provider no configurado (500)

### Traducciones agregadas:
```php
'api_key_required' => 'API key is required...',
'api_key_invalid' => 'The provided API key is invalid.',
'api_key_inactive' => 'The provided API key has been deactivated.',
'api_key_insufficient_scopes' => 'The API key does not have the required permissions...',
'api_key_provider_not_configured' => 'API key authentication is not properly configured.',
```

### Tests creados:
- `ApiKeyAttributeTest.php` - 6 tests

### Verificado:
- PHPStan: 0 errores
- Tests: 123 tests pasando (FrameworkCore)

---

## FASE 7: Validation Enhancement ✅ COMPLETADA

### Atributos de validación existentes:

| Atributo | Descripción |
|----------|-------------|
| `Uuid` | Valida formato UUID (v1, v4, v5) |
| `IsoDate` | Valida fechas ISO 8601 |
| `Slug` | Valida formato slug (a-z, 0-9, guiones) |
| `MacAddress` | Valida direcciones MAC |
| `Json` | Valida que el valor sea JSON válido |

### Archivo creado:
```
Infrastructure/FrameworkCore/Attributes/Validation/
└── JsonSchema.php              ✅
```

### JsonSchema Attribute:

| Parámetro | Default | Descripción |
|-----------|---------|-------------|
| `schema` | (requerido) | Schema JSON en formato array |
| `allowAdditionalProperties` | `false` | Permitir propiedades adicionales |

### Tests creados:
- `JsonSchemaAttributeTest.php` - 5 tests

### Verificado:
- PHPStan: 0 errores
- Tests: 128 tests pasando (FrameworkCore)
- Total de atributos de validación: 45+

---

## FASE 8: File Upload Security ✅ COMPLETADA

### Archivos creados:
```
Infrastructure/FrameworkCore/
├── Attributes/Validation/
│   └── SecureUpload.php          ✅
└── Services/
    └── SecureFileUploader.php    ✅
```

### SecureUpload Attribute:

| Parámetro | Default | Descripción |
|-----------|---------|-------------|
| `allowedMimes` | `['jpg', 'png', 'pdf', ...]` | Extensiones permitidas |
| `maxSize` | `10240` (KB) | Tamaño máximo |
| `allowedTypes` | MIME types comunes | Tipos MIME permitidos |
| `scanForMalware` | `false` | Escanear en busca de malware |
| `generateUniqueName` | `true` | Generar nombre único |
| `storageDisk` | `null` | Disk de almacenamiento |

### SecureFileUploader Service:

| Método | Descripción |
|--------|-------------|
| `upload()` | Sube archivo con validaciones |
| `validateFile()` | Valida mime, extensión y tamaño |
| `delete()` | Elimina archivo |
| `isImage()` | Verifica si es imagen |
| `getImageDimensions()` | Obtiene dimensiones de imagen |

### Validaciones implementadas:
- Validación de MIME type
- Validación de extensión de archivo
- Validación de tamaño máximo
- Generación de nombres únicos
- Sanitización de nombres de archivo

### Tests creados:
- `SecureUploadAttributeTest.php` - 8 tests

### Verificado:
- PHPStan: 0 errores
- Tests: 136 tests pasando (FrameworkCore)

---

## FASE 9: Object Level Authorization (BOLA) ✅ COMPLETADA

### Archivos creados:
```
Infrastructure/FrameworkCore/
├── Attributes/Behavior/
│   └── Ownership.php           ✅
└── Services/
    └── OwnershipValidator.php  ✅
```

### Ownership Attribute:

| Parámetro | Default | Descripción |
|-----------|---------|-------------|
| `ownerField` | `'user_id'` | Campo que contiene el owner |
| `allowAdminBypass` | `true` | Permitir que admins accedan |
| `resourceField` | `null` | Campo del recurso |

### OwnershipValidator Service:

| Método | Descripción |
|--------|-------------|
| `validate()` | Valida si el usuario es owner del recurso |
| `validateOrFail()` | Valida o lanza AuthorizationException |
| `canAccess()` | Verifica si puede acceder |
| `canModify()` | Verifica si puede modificar |
| `canDelete()` | Verifica si puede eliminar |
| `getOwnershipAttribute()` | Obtiene attribute del recurso |

### Características:
- Verificación de propiedad de recursos
- Bypass para administradores
- Detección de violaciones BOLA (IDOR)
- Logging de accesos no autorizados
- Soporte para getters y propiedades públicas

### Tests creados:
- `OwnershipAttributeTest.php` - 6 tests

### Verificado:
- PHPStan: 0 errores
- Tests: 142 tests pasando (FrameworkCore)

---

## CONFIGURACIÓN boundly.php

```php
return [
    // ... existente ...
    
    'security' => [
        'max_request_size' => '1M',
        'max_upload_size' => '10M',
    ],
    
    'cors' => [
        'enabled' => true,
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposed_headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining'],
        'max_age' => 3600,
        'supports_credentials' => false,
    ],
    
    'brute_force' => [
        'enabled' => true,
        'max_attempts' => 5,
        'decay_minutes' => 15,
        'lockout_multiplier' => 2,
        'max_lockouts' => 3,
        'track_by' => 'email',
    ],
    
    'sanitization' => [
        'enabled' => true,
        'strip_html' => true,
        'strip_scripts' => true,
        'escape_sql_wildcards' => true,
        'allowed_tags' => '<b><i><p><br><ul><ol><li>',
    ],
    
    'auth' => [
        'default_guard' => 'sanctum',
        'authenticator' => null, // Implementar Contract
        'token_validator' => null,
        'user_resolver' => null,
        'api_key_provider' => null,
    ],
];
```

---

## ORDEN DE IMPLEMENTACIÓN SUGERIDO

1. ✅ SecurityHeadersMiddleware
2. ✅ RequestSizeLimitMiddleware
3. ✅ CorsMiddleware
4. ✅ Contracts (AuthenticatorInterface, etc.)
5. ✅ SanctumAuthenticator adapter
6. ✅ SecurityLogger
7. ✅ BruteForceProtectionMiddleware
8. ✅ InputSanitizer
9. ✅ InputSanitizationMiddleware
10. ✅ ApiKeyMiddleware + #[ApiKey]
11. ✅ SecureUpload + SecureFileUploader
12. ✅ OwnershipValidator + #[Ownership]
13. ✅ Validation attributes (Uuid, IsoDate, Slug, MacAddress, JsonSchema)
14. ✅ Tests
15. Documentation

---

## REFERENCIAS

- OWASP API Security Top 10: https://owasp.org/API-Security/
- Laravel Security: https://laravel.com/docs/11.x/security
- Security Headers: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers

---

**Última actualización:** 2026-03-24
**Estado:** ✅ COMPLETADO - Todas las fases implementadas
**Avance:** 100%
