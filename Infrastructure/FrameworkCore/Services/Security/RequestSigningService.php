<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services\Security;

use Illuminate\Http\Request;

class RequestSigningService
{
    protected string $algorithm;
    protected string $secretKey;
    protected int $timestampTolerance;
    protected bool $enabled;

    public function __construct()
    {
        $config = config('boundly.security.request_signing', []);
        $this->enabled = $config['enabled'] ?? false;
        $this->algorithm = $config['algorithm'] ?? 'sha256';
        $this->secretKey = $config['secret_key'] ?? env('REQUEST_SIGNING_SECRET', '');
        $this->timestampTolerance = $config['timestamp_tolerance'] ?? 300;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function signRequest(Request $request): string
    {
        $payload = $this->buildPayload($request);

        return hash_hmac($this->algorithm, $payload, $this->secretKey);
    }

    public function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Signature');
        if (! $signature) {
            return false;
        }

        if (! $this->verifyTimestamp($request)) {
            return false;
        }

        $expectedSignature = $this->signRequest($request);

        return hash_equals($expectedSignature, $signature);
    }

    public function buildPayload(Request $request): string
    {
        $parts = [
            $request->method(),
            $request->path(),
            $request->header('X-Timestamp', (string) time()),
            $request->header('Content-Type', ''),
            $request->getContent(),
        ];

        return implode("\n", $parts);
    }

    public function generateHeaders(Request $request): array
    {
        $timestamp = time();

        return [
            'X-Timestamp' => (string) $timestamp,
            'X-Signature' => $this->signRequest($request),
        ];
    }

    protected function verifyTimestamp(Request $request): bool
    {
        $timestamp = $request->header('X-Timestamp');

        if (! $timestamp) {
            return false;
        }

        $requestTime = (int) $timestamp;
        $currentTime = time();

        return abs($currentTime - $requestTime) <= $this->timestampTolerance;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function getTimestampTolerance(): int
    {
        return $this->timestampTolerance;
    }
}
