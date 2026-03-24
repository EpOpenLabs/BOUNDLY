<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Enums;

enum SecurityEvent: string
{
    case LOGIN_SUCCESS = 'auth.login.success';
    case LOGIN_FAILED = 'auth.login.failed';
    case LOGOUT = 'auth.logout';
    case TOKEN_EXPIRED = 'auth.token.expired';
    case TOKEN_INVALID = 'auth.token.invalid';
    case RATE_LIMIT_EXCEEDED = 'security.rate_limit.exceeded';
    case UNAUTHORIZED_ACCESS = 'security.unauthorized';
    case FORBIDDEN_ACCESS = 'security.forbidden';
    case API_KEY_CREATED = 'security.apikey.created';
    case API_KEY_REVOKED = 'security.apikey.revoked';
    case SUSPICIOUS_INPUT = 'security.suspicious_input';
    case BRUTE_FORCE_DETECTED = 'security.brute_force.detected';
    case BRUTE_FORCE_BLOCKED = 'security.brute_force.blocked';
    case CORS_VIOLATION = 'security.cors.violation';
    case REQUEST_SIZE_EXCEEDED = 'security.request_size.exceeded';
    case INVALID_CONTENT_TYPE = 'security.content_type.invalid';

    public function severity(): LogLevel
    {
        return match ($this) {
            self::LOGIN_SUCCESS, self::LOGOUT => LogLevel::INFO,
            self::LOGIN_FAILED, self::TOKEN_EXPIRED, self::TOKEN_INVALID => LogLevel::WARNING,
            self::RATE_LIMIT_EXCEEDED,
            self::UNAUTHORIZED_ACCESS,
            self::FORBIDDEN_ACCESS,
            self::CORS_VIOLATION,
            self::INVALID_CONTENT_TYPE,
            self::REQUEST_SIZE_EXCEEDED => LogLevel::WARNING,
            self::API_KEY_CREATED, self::API_KEY_REVOKED => LogLevel::INFO,
            self::SUSPICIOUS_INPUT => LogLevel::CRITICAL,
            self::BRUTE_FORCE_DETECTED, self::BRUTE_FORCE_BLOCKED => LogLevel::ALERT,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::LOGIN_SUCCESS => 'Login Success',
            self::LOGIN_FAILED => 'Login Failed',
            self::LOGOUT => 'User Logout',
            self::TOKEN_EXPIRED => 'Token Expired',
            self::TOKEN_INVALID => 'Token Invalid',
            self::RATE_LIMIT_EXCEEDED => 'Rate Limit Exceeded',
            self::UNAUTHORIZED_ACCESS => 'Unauthorized Access',
            self::FORBIDDEN_ACCESS => 'Forbidden Access',
            self::API_KEY_CREATED => 'API Key Created',
            self::API_KEY_REVOKED => 'API Key Revoked',
            self::SUSPICIOUS_INPUT => 'Suspicious Input Detected',
            self::BRUTE_FORCE_DETECTED => 'Brute Force Attack Detected',
            self::BRUTE_FORCE_BLOCKED => 'Brute Force Attack Blocked',
            self::CORS_VIOLATION => 'CORS Violation',
            self::REQUEST_SIZE_EXCEEDED => 'Request Size Exceeded',
            self::INVALID_CONTENT_TYPE => 'Invalid Content Type',
        };
    }
}
