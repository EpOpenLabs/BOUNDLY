<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Attributes\Security\ApiKey as ApiKeyAttribute;
use Infrastructure\FrameworkCore\Contracts\Authentication\ApiKeyProviderInterface;
use Infrastructure\FrameworkCore\Services\SecurityLogger;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function __construct(
        protected SecurityLogger $logger,
        protected ?ApiKeyProviderInterface $apiKeyProvider = null
    ) {}

    public function handle(Request $request, Closure $next, ?string $header = null, ?string $scopes = null): Response
    {
        $headerName = $header ?? 'X-Api-Key';
        $requiredScopes = $scopes ? explode(',', $scopes) : [];

        $apiKey = $request->header($headerName);

        if (empty($apiKey)) {
            return $this->buildMissingKeyResponse();
        }

        if ($this->apiKeyProvider === null) {
            return $this->buildProviderNotConfiguredResponse();
        }

        if (! $this->apiKeyProvider->validateKey($apiKey)) {
            $this->logger->logUnauthorizedAccess(null, $request, [
                'reason' => 'invalid_api_key',
                'key_prefix' => $this->getKeyPrefix($apiKey),
            ]);

            return $this->buildInvalidKeyResponse();
        }

        $user = $this->apiKeyProvider->getUserFromKey($apiKey);
        $scopes = $this->apiKeyProvider->getKeyScopes($apiKey);

        if (! empty($requiredScopes) && ! $this->hasRequiredScopes($scopes, $requiredScopes)) {
            $this->logger->logForbiddenAccess($user?->getAuthIdentifier(), $request, [
                'reason' => 'insufficient_api_key_scopes',
                'required' => $requiredScopes,
                'provided' => $scopes,
            ]);

            return $this->buildInsufficientScopesResponse($requiredScopes);
        }

        $request->attributes->set('api_key_user', $user);
        $request->attributes->set('api_key_user_id', $user?->getAuthIdentifier());
        $request->attributes->set('api_key_scopes', $scopes);

        return $next($request);
    }

    protected function hasRequiredScopes(array $providedScopes, array $requiredScopes): bool
    {
        foreach ($requiredScopes as $required) {
            if (! in_array($required, $providedScopes, true)) {
                return false;
            }
        }

        return true;
    }

    protected function getKeyPrefix(string $key): string
    {
        return strlen($key) > 8 ? substr($key, 0, 8) : $key;
    }

    protected function buildMissingKeyResponse(): Response
    {
        return response()->json([
            'status' => 'error',
            'message' => __('core::messages.api_key_required'),
            'error' => [
                'code' => 'API_KEY_MISSING',
            ],
        ], 401);
    }

    protected function buildProviderNotConfiguredResponse(): Response
    {
        return response()->json([
            'status' => 'error',
            'message' => __('core::messages.api_key_provider_not_configured'),
            'error' => [
                'code' => 'API_KEY_PROVIDER_ERROR',
            ],
        ], 500);
    }

    protected function buildInvalidKeyResponse(): Response
    {
        return response()->json([
            'status' => 'error',
            'message' => __('core::messages.api_key_invalid'),
            'error' => [
                'code' => 'API_KEY_INVALID',
            ],
        ], 401);
    }

    protected function buildInsufficientScopesResponse(array $requiredScopes): Response
    {
        return response()->json([
            'status' => 'error',
            'message' => __('core::messages.api_key_insufficient_scopes'),
            'error' => [
                'code' => 'API_KEY_INSUFFICIENT_SCOPES',
                'required_scopes' => $requiredScopes,
            ],
        ], 403);
    }

    public function setApiKeyProvider(ApiKeyProviderInterface $provider): void
    {
        $this->apiKeyProvider = $provider;
    }
}
