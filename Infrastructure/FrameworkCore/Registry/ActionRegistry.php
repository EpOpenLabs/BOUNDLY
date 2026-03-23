<?php

namespace Infrastructure\FrameworkCore\Registry;

use ReflectionClass;
use Infrastructure\FrameworkCore\Attributes\Action;

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

    /**
     * Bulk-loads the registry from a pre-built cache array.
     * Used in production to avoid filesystem scanning and reflection.
     */
    public function hydrateFromCache(array $actions): void
    {
        $this->actions = $actions;
    }
}

