<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResponseCacheMiddleware
{
    protected string $store;
    protected int $ttl;
    protected array $excludePaths;

    public function __construct()
    {
        $config = config('boundly.cache.response', []);
        $this->store = $config['store'] ?? 'file';
        $this->ttl = $config['ttl'] ?? 60;
        $this->excludePaths = $config['exclude_paths'] ?? [];
    }

    public function handle(Request $request, Closure $next, ?int $ttl = null): Response
    {
        if (! $this->shouldCache($request)) {
            return $next($request);
        }

        $cacheKey = $this->buildCacheKey($request);
        $effectiveTtl = $ttl ?? $this->ttl;

        $cached = Cache::store($this->store)->get($cacheKey);

        if ($cached !== null) {
            return response()->json(
                $cached['data'],
                $cached['status'],
                array_merge($cached['headers'], ['X-Cache' => 'HIT'])
            );
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::store($this->store)->put(
                $cacheKey,
                [
                    'data' => json_decode($response->getContent(), true),
                    'status' => $response->getStatusCode(),
                    'headers' => $response->headers->all(),
                ],
                $effectiveTtl * 60
            );

            $response->headers->set('X-Cache', 'MISS');
        }

        return $response;
    }

    protected function shouldCache(Request $request): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        foreach ($this->excludePaths as $path) {
            if ($request->is($path)) {
                return false;
            }
        }

        return true;
    }

    protected function buildCacheKey(Request $request): string
    {
        return sprintf(
            'response:%s:%s:%s',
            $request->path(),
            $request->query->all() ? md5(json_encode($request->query->all())) : 'no-params',
            $request->header('Accept-Language', 'none')
        );
    }

    public function invalidate(string $path, array $query = []): void
    {
        $pattern = $query ? md5(json_encode($query)) : 'no-params';
        $key = "response:{$path}:{$pattern}:*";
        Cache::store($this->store)->forget($key);
    }

    public function invalidateAll(): void
    {
        $store = Cache::store($this->store);
        $store->forget('response:*');
    }
}
