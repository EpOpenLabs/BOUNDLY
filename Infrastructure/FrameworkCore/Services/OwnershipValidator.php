<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Infrastructure\FrameworkCore\Attributes\Behavior\Ownership;

class OwnershipValidator
{
    protected SecurityLogger $logger;

    public function __construct(SecurityLogger $logger)
    {
        $this->logger = $logger;
    }

    public function validate(
        Authenticatable $user,
        object $resource,
        ?Ownership $ownership = null,
        ?string $userId = null
    ): bool {
        $ownership = $ownership ?? new Ownership();
        $userId = $userId ?? $user->getAuthIdentifier();

        if ($this->isAdmin($user) && $ownership->allowsAdminBypass()) {
            return true;
        }

        $resourceOwner = $this->getResourceOwner($resource, $ownership);

        if ($resourceOwner === null) {
            return false;
        }

        $isOwner = $this->ownersMatch($userId, $resourceOwner);

        if (! $isOwner) {
            $this->logger->logForbiddenAccess((string) $userId, null, [
                'reason' => 'bola_violation',
                'resource_class' => get_class($resource),
                'resource_owner' => $resourceOwner,
                'requested_by' => $userId,
            ]);
        }

        return $isOwner;
    }

    public function validateOrFail(
        Authenticatable $user,
        object $resource,
        ?Ownership $ownership = null
    ): void {
        if (! $this->validate($user, $resource, $ownership)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'You do not have permission to access this resource.'
            );
        }
    }

    public function canAccess(
        Authenticatable $user,
        object $resource,
        ?Ownership $ownership = null
    ): bool {
        return $this->validate($user, $resource, $ownership);
    }

    public function canModify(
        Authenticatable $user,
        object $resource,
        ?Ownership $ownership = null
    ): bool {
        return $this->validate($user, $resource, $ownership);
    }

    public function canDelete(
        Authenticatable $user,
        object $resource,
        ?Ownership $ownership = null
    ): bool {
        return $this->validate($user, $resource, $ownership);
    }

    protected function getResourceOwner(object|array $resource, Ownership $ownership): mixed
    {
        $ownerField = $ownership->getOwnerField();

        if (is_array($resource)) {
            return $resource[$ownerField] ?? null;
        }

        if (isset($resource->{$ownerField})) {
            return $resource->{$ownerField};
        }

        $getter = 'get' . ucfirst($ownerField);
        if (method_exists($resource, $getter)) {
            return $resource->{$getter}();
        }

        return null;
    }

    protected function ownersMatch(string|int $userId, mixed $resourceOwner): bool
    {
        if ($resourceOwner === null) {
            return false;
        }

        return (string) $userId === (string) $resourceOwner;
    }

    protected function isAdmin(Authenticatable $user): bool
    {
        if (method_exists($user, 'isAdmin')) {
            return $user->isAdmin();
        }

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin');
        }

        if (property_exists($user, 'is_admin')) {
            return (bool) $user->is_admin;
        }

        if (isset($user->is_admin)) {
            return (bool) $user->is_admin;
        }

        return false;
    }

    public function getOwnershipAttribute(object $resource): ?Ownership
    {
        $reflection = new \ReflectionClass($resource);

        $attributes = $reflection->getAttributes(Ownership::class);

        if (! empty($attributes)) {
            return $attributes[0]->newInstance();
        }

        return null;
    }
}
