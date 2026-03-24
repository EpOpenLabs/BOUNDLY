<?php

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Infrastructure\FrameworkCore\Attributes\Behavior\Authorize;
use Infrastructure\FrameworkCore\Attributes\Behavior\Policy;
use Infrastructure\FrameworkCore\Database\DynamicRepository;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Infrastructure\FrameworkCore\Traits\ChecksPermissions;
use Infrastructure\FrameworkCore\Traits\ResolvesAuthentication;
use ReflectionClass;

/**
 * Middleware that reads the #[Authorize] attribute from the Entity definition
 * and enforces authentication and role restrictions — transparently.
 *
 * The programmer NEVER configures routes or middleware manually.
 * They simply add #[Authorize(...)] to the Entity class.
 */
class ResourceAuthorize
{
    use ChecksPermissions;
    use ResolvesAuthentication;

    public function __construct(
        protected EntityRegistry $registry,
        protected DynamicRepository $repository
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $resource = $request->route('resource');

        if (! $resource) {
            return $next($request);
        }

        $config = $this->registry->getEntityConfig($resource);

        if (! $config) {
            return $next($request);
        }

        // Reflect the entity class to find #[Authorize] attributes
        $reflection = new ReflectionClass($config['class']);
        $authAttrs = $reflection->getAttributes(Authorize::class);
        $currentMethod = strtoupper($request->method());

        foreach ($authAttrs as $authAttr) {
            /** @var Authorize $rule */
            $rule = $authAttr->newInstance();

            // Check if this rule applies to the current HTTP method
            $appliesTo = empty($rule->methods) || in_array($currentMethod, $rule->methods);

            if (! $appliesTo) {
                continue;
            }

            // 1. Enforce Authentication
            $guard = $rule->guard ?: config('boundly.auth.default_guard', 'sanctum');

            if (! auth($guard)->check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('core::messages.unauthenticated'),
                ], 401);
            }

            // 2. Enforce Role (if roles are specified)
            if (! empty($rule->roles)) {
                $user = auth($guard)->user();

                // Compatible with Spatie Permission or a simple 'role' column
                $hasRole = $this->userHasRole($user, $rule->roles);

                if (! $hasRole) {
                    return response()->json([
                        'status' => 'error',
                        'message' => __('core::messages.unauthorized'),
                    ], 403);
                }
            }
        }

        // --- Policy Check ---
        $policyAttr = $reflection->getAttributes(Policy::class);
        if (! empty($policyAttr)) {
            /** @var Policy $policy */
            $policy = $policyAttr[0]->newInstance();
            $id = $request->route('id');
            $verb = $request->method();

            $policyMethod = $this->mapVerbToPolicyMethod($verb, (bool) $id);

            // Instance or Class based authorization?
            if ($id && in_array($policyMethod, ['view', 'update', 'delete', 'restore', 'forceDelete'])) {
                $instance = $this->repository->find($resource, $id);
                if ($instance) {
                    Gate::authorize($policyMethod, [$config['class'], (object) $instance]);
                }
            } else {
                Gate::authorize($policyMethod, $config['class']);
            }
        }

        return $next($request);
    }

    protected function mapVerbToPolicyMethod(string $verb, bool $hasId): string
    {
        return match ($verb) {
            'GET' => $hasId ? 'view' : 'viewAny',
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'viewAny',
        };
    }
}
