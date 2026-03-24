<?php

namespace Infrastructure\FrameworkCore\Registry;

use ReflectionClass;
use Infrastructure\FrameworkCore\Attributes\UseCase\Action;

class ActionRegistry
{
    protected array $actions = [];

    public function registerClass(string $className): void
    {
        if (!class_exists($className)) {
            return;
        }

        $reflection = new ReflectionClass($className);
        $attributes = $reflection->getAttributes(Action::class);

        if (empty($attributes)) {
            return;
        }

        $actionConfig = $attributes[0]->newInstance();
        $key = strtoupper($actionConfig->method) . '_' . $actionConfig->resource;
        
        $this->actions[$key] = $className;
    }

    public function getActionClass(string $resource, string $method): ?string
    {
        $key = strtoupper($method) . '_' . $resource;
        return $this->actions[$key] ?? null;
    }

    public function getAllActions(): array
    {
        return $this->actions;
    }

    public function getActionsByResource(string $resource): array
    {
        $found = [];
        foreach ($this->actions as $key => $className) {
            if (str_ends_with($key, '_' . $resource)) {
                $method = explode('_', $key)[0];
                $found[$method] = $className;
            }
        }
        return $found;
    }

    /**
     * Bulk-loads the registry from a pre-built cache array.
     * Used in production to avoid filesystem scanning and reflection.
     */
    public function hydrateFromCache(array $actions): void
    {
        $this->actions = $actions;
    }
}

