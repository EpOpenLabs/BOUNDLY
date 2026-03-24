<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services;

class InputSanitizer
{
    protected array $config;

    protected bool $enabled;

    protected bool $stripHtml;

    protected bool $stripScripts;

    protected bool $escapeSqlWildcards;

    protected array $allowedTags;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? $this->getConfig();
        $this->enabled = $this->config['enabled'] ?? false;
        $this->stripHtml = $this->config['strip_html'] ?? true;
        $this->stripScripts = $this->config['strip_scripts'] ?? true;
        $this->escapeSqlWildcards = $this->config['escape_sql_wildcards'] ?? true;
        $this->allowedTags = $this->parseAllowedTags($this->config['allowed_tags'] ?? '');
    }

    protected function getConfig(): array
    {
        if (function_exists('config') && app()->bound('config')) {
            return config('boundly.sanitization', []);
        }

        return [];
    }

    protected function parseAllowedTags(string $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        $tags = strip_tags($tags);
        if (empty($tags)) {
            return [];
        }

        $allowed = [];
        if (preg_match_all('/<([a-z]+)/i', $tags, $matches)) {
            $allowed = array_map('strtolower', $matches[1]);
        }

        return $allowed;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function sanitize(string $input): string
    {
        if (! $this->enabled) {
            return $input;
        }

        $sanitized = $this->sanitizeJavascript($input);
        $sanitized = $this->sanitizeHtml($sanitized);

        if ($this->escapeSqlWildcards) {
            $sanitized = $this->sanitizeSqlWildcards($sanitized);
        }

        return $sanitized;
    }

    public function sanitizeHtml(string $input): string
    {
        if (! $this->enabled || ! $this->stripHtml) {
            return $input;
        }

        if (empty($this->allowedTags)) {
            return strip_tags($input);
        }

        $tags = '<'.implode('><', $this->allowedTags).'>';

        return strip_tags($input, $tags);
    }

    public function sanitizeJavascript(string $input): string
    {
        if (! $this->enabled || ! $this->stripScripts) {
            return $input;
        }

        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',
            '/on\w+\s*=\s*["\'][^"\']*["\']/i',
            '/javascript\s*:/i',
            '/<object\b[^>]*>(.*?)<\/object>/is',
            '/<embed\b[^>]*>/is',
            '/<applet\b[^>]*>(.*?)<\/applet>/is',
        ];

        $sanitized = preg_replace($patterns, '', $input);

        return $sanitized !== null ? $sanitized : $input;
    }

    public function sanitizeSqlWildcards(string $input): string
    {
        if (! $this->enabled || ! $this->escapeSqlWildcards) {
            return $input;
        }

        $replacements = [
            '%' => '\%',
            '_' => '\_',
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $input
        );
    }

    public function sanitizeFilename(string $input): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $input);
        $sanitized = preg_replace('/_+/', '_', $sanitized ?? $input);
        $sanitized = trim($sanitized ?? $input, '_');

        return $sanitized ?: 'file';
    }

    public function sanitizeEmail(string $input): string
    {
        return filter_var($input, FILTER_SANITIZE_EMAIL) ?: '';
    }

    public function sanitizeUrl(string $input): string
    {
        return filter_var($input, FILTER_SANITIZE_URL) ?: '';
    }

    public function sanitizeInteger($input): int
    {
        return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    public function sanitizeFloat($input): float
    {
        return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public function sanitizeArray(array $input): array
    {
        return array_map(fn ($value) => is_string($value) ? $this->sanitize($value) : $value, $input);
    }

    public function detectSuspiciousInput(string $input): bool
    {
        $suspiciousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/onerror\s*=/i',
            '/onload\s*=/i',
            '/onclick\s*=/i',
            '/union\s+select/i',
            '/union\s+all\s+select/i',
            "/'\s+or\s+'1'\s*=\s*'1/i",
            '/\bor\b.*\b1\b.*=.*\b1\b/i',
            '/\band\b.*\b1\b.*=.*\b1\b/i',
            '/drop\s+table/i',
            '/drop\s+database/i',
            '/xp_cmdshell/i',
            '/exec\s*\(/i',
            '/eval\s*\(/i',
            '/base64_decode\s*\(/i',
            '/<\?php/i',
            '/<\?=\s*\$/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    public function getSuspiciousDetails(string $input): array
    {
        $detected = [];
        $suspiciousPatterns = [
            'XSS Script' => '/<script/i',
            'JavaScript URI' => '/javascript:/i',
            'Event Handler' => '/on\w+\s*=/i',
            'SQL Union Select' => '/union\s+(all\s+)?select/i',
            'SQL Injection' => "/'\s+or\s+'1'\s*=\s*'1/i",
            'SQL Drop Table' => '/drop\s+(table|database)/i',
            'OS Command Injection' => '/xp_cmdshell|exec\s*\(/i',
            'PHP Code' => '/<\?php|<\?=/i',
        ];

        foreach ($suspiciousPatterns as $name => $pattern) {
            if (preg_match($pattern, $input)) {
                $detected[] = $name;
            }
        }

        return $detected;
    }
}
