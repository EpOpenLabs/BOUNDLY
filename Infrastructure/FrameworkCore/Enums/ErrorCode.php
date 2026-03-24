<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Enums;

enum ErrorCode: string
{
    case RESOURCE_NOT_FOUND = 'ERR_RESOURCE_NOT_FOUND';
    case RESOURCE_NOT_DEFINED = 'ERR_RESOURCE_NOT_DEFINED';
    case VALIDATION_FAILED = 'ERR_VALIDATION_FAILED';
    case UNAUTHORIZED = 'ERR_UNAUTHORIZED';
    case FORBIDDEN = 'ERR_FORBIDDEN';
    case RATE_LIMITED = 'ERR_RATE_LIMITED';
    case SERVER_ERROR = 'ERR_SERVER_ERROR';
    case METHOD_NOT_ALLOWED = 'ERR_METHOD_NOT_ALLOWED';
    case INVALID_CONTENT_TYPE = 'ERR_INVALID_CONTENT_TYPE';
    case REQUEST_TOO_LARGE = 'ERR_REQUEST_TOO_LARGE';
    case INTERNAL_ERROR = 'ERR_INTERNAL_ERROR';
    case BRUTE_FORCE_BLOCKED = 'ERR_BRUTE_FORCE_BLOCKED';
    case API_KEY_INVALID = 'ERR_API_KEY_INVALID';
    case API_KEY_MISSING = 'ERR_API_KEY_MISSING';
    case API_KEY_INSUFFICIENT_SCOPES = 'ERR_API_KEY_INSUFFICIENT_SCOPES';
    case SUSPICIOUS_INPUT = 'ERR_SUSPICIOUS_INPUT';
    case CORS_VIOLATION = 'ERR_CORS_VIOLATION';
    case IP_RESTRICTED = 'ERR_IP_RESTRICTED';
    case SIGNATURE_INVALID = 'ERR_SIGNATURE_INVALID';
    case TIER_LIMIT_EXCEEDED = 'ERR_TIER_LIMIT_EXCEEDED';
    case SERVICE_UNAVAILABLE = 'ERR_SERVICE_UNAVAILABLE';

    public function httpStatus(): int
    {
        return match ($this) {
            self::RESOURCE_NOT_FOUND,
            self::RESOURCE_NOT_DEFINED => 404,
            self::VALIDATION_FAILED => 422,
            self::UNAUTHORIZED,
            self::API_KEY_MISSING,
            self::API_KEY_INVALID => 401,
            self::FORBIDDEN,
            self::API_KEY_INSUFFICIENT_SCOPES,
            self::IP_RESTRICTED => 403,
            self::RATE_LIMITED,
            self::BRUTE_FORCE_BLOCKED,
            self::TIER_LIMIT_EXCEEDED => 429,
            self::METHOD_NOT_ALLOWED => 405,
            self::INVALID_CONTENT_TYPE,
            self::REQUEST_TOO_LARGE => 415,
            self::SUSPICIOUS_INPUT,
            self::CORS_VIOLATION,
            self::SIGNATURE_INVALID,
            self::SERVER_ERROR,
            self::INTERNAL_ERROR => 500,
            self::SERVICE_UNAVAILABLE => 503,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::RESOURCE_NOT_FOUND => 'Resource not found',
            self::RESOURCE_NOT_DEFINED => 'Resource not defined',
            self::VALIDATION_FAILED => 'Validation failed',
            self::UNAUTHORIZED => 'Unauthorized',
            self::FORBIDDEN => 'Forbidden',
            self::RATE_LIMITED => 'Rate limit exceeded',
            self::SERVER_ERROR => 'Server error',
            self::METHOD_NOT_ALLOWED => 'Method not allowed',
            self::INVALID_CONTENT_TYPE => 'Invalid content type',
            self::REQUEST_TOO_LARGE => 'Request too large',
            self::INTERNAL_ERROR => 'Internal error',
            self::BRUTE_FORCE_BLOCKED => 'Brute force blocked',
            self::API_KEY_INVALID => 'Invalid API key',
            self::API_KEY_MISSING => 'API key missing',
            self::API_KEY_INSUFFICIENT_SCOPES => 'Insufficient API key scopes',
            self::SUSPICIOUS_INPUT => 'Suspicious input detected',
            self::CORS_VIOLATION => 'CORS violation',
            self::IP_RESTRICTED => 'IP restricted',
            self::SIGNATURE_INVALID => 'Invalid signature',
            self::TIER_LIMIT_EXCEEDED => 'Tier limit exceeded',
            self::SERVICE_UNAVAILABLE => 'Service unavailable',
        };
    }
}
