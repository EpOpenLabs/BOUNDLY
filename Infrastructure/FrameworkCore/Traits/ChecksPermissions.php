<?php

namespace Infrastructure\FrameworkCore\Traits;

/**
 * Trait to check user roles across different framework components.
 */
trait ChecksPermissions
{
    /**
     * Checks if a user has any of the required roles.
     * Compatible with Spatie Permission and a simple 'role' column.
     */
    protected function userHasRole($user, array $roles): bool
    {
        if (empty($roles)) {
            return true;
        }

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
