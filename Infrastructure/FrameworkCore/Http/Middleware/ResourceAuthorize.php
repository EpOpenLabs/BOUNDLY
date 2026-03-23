<?php

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Infrastructure\FrameworkCore\Attributes\Authorize;
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
    public function __construct(protected EntityRegistry $registry) {}

    public function handle(Request $request, Closure $next)
    {
        $resource = $request->route('resource');

        if (!$resource) {
            return $next($request);
        }

        $config = $this->registry->getEntityConfig($resource);

        if (!$config) {
            return $next($request);
        }

        // Reflect the entity class to find #[Authorize] attributes
        $reflection    = new ReflectionClass($config['class']);
        $authAttrs     = $reflection->getAttributes(Authorize::class);
        $currentMethod = strtoupper($request->method());

        foreach ($authAttrs as $authAttr) {
            /** @var Authorize $rule */
            $rule = $authAttr->newInstance();

            // Check if this rule applies to the current HTTP method
            $appliesTo = empty($rule->methods) || in_array($currentMethod, $rule->methods);

            if (!$appliesTo) {
                continue;
            }

            // 1. Enforce Authentication
            $guard = $rule->guard ?: config('boundly.auth.default_guard', 'sanctum');

            if (!auth($guard)->check()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => __('core::messages.unauthenticated'),
                ], 401);
            }

            // 2. Enforce Role (if roles are specified)
            if (!empty($rule->roles)) {
                $user = auth($guard)->user();

                // Compatible with Spatie Permission or a simple 'role' column
                $hasRole = $this->userHasRole($user, $rule->roles);

                if (!$hasRole) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => __('core::messages.unauthorized'),
                    ], 403);
                }
            }
        }

        return $next($request);
    }

    /**
     * Checks if a user has any of the required roles.
     * Compatible with Spatie Permission and a simple 'role' column.
     */
    protected function userHasRole($user, array $roles): bool
    {
        if (!$user) {
            return false;
        }

        // Spatie Laravel Permission integration
        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($roles);
        }

        // Fallback: simple 'role' or 'roles' column
        if (isset($user->role)) {
            return in_array($user->role, $roles);
        }

        if (isset($user->roles) && is_array($user->roles)) {
            return !empty(array_intersect($user->roles, $roles));
        }

        return false;
    }
}
