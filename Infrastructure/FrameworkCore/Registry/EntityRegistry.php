<?php

namespace Infrastructure\FrameworkCore\Registry;

use Infrastructure\FrameworkCore\Attributes\Behavior\Auditable;
use Infrastructure\FrameworkCore\Attributes\Behavior\SoftDelete;
use Infrastructure\FrameworkCore\Attributes\Behavior\TenantAware;
use Infrastructure\FrameworkCore\Attributes\Relations\BelongsTo;
use Infrastructure\FrameworkCore\Attributes\Relations\HasMany;
use Infrastructure\FrameworkCore\Attributes\Relations\HasOne;
use Infrastructure\FrameworkCore\Attributes\Relations\ManyToMany;
use Infrastructure\FrameworkCore\Attributes\Relations\MorphMany;
use Infrastructure\FrameworkCore\Attributes\Relations\MorphOne;
use Infrastructure\FrameworkCore\Attributes\Relations\MorphTo;
use Infrastructure\FrameworkCore\Attributes\Schema\Column;
use Infrastructure\FrameworkCore\Attributes\Schema\Entity;
use Infrastructure\FrameworkCore\Attributes\Schema\Id;
use Infrastructure\FrameworkCore\Attributes\Security\Hidden;
use ReflectionClass;

class EntityRegistry
{
    protected array $entities = [];

    protected array $morphMapArr = []; // [alias => fullClass]

    protected array $classToMorph = []; // [fullClass => alias]

    /**
     * Scans and registers a class as an entity if it contains the Entity attribute.
     */
    public function registerClass(string $className): void
    {
        if (! class_exists($className)) {
            return;
        }

        $reflection = new ReflectionClass($className);

        $entityAttributes = $reflection->getAttributes(Entity::class);

        if (empty($entityAttributes)) {
            return;
        }

        $entityConfig = $entityAttributes[0]->newInstance();
        $resourceName = $entityConfig->resource ?? $entityConfig->table;

        $tenantAttributes = $reflection->getAttributes(TenantAware::class);
        $isTenantAware = ! empty($tenantAttributes);
        $tenantColumn = $isTenantAware ? $tenantAttributes[0]->newInstance()->tenantColumn : null;

        $isAuditable = ! empty($reflection->getAttributes(Auditable::class));
        $isSoftDelete = ! empty($reflection->getAttributes(SoftDelete::class));

        $columns = [];
        $hasMany = [];
        $belongsTo = [];
        $hasOne = [];
        $manyToMany = [];
        $morphTo = [];
        $morphMany = [];
        $morphOne = [];
        $hidden = [];
        $primaryKey = 'id';

        // Detect Hidden fields at class level
        $hiddenAttributes = $reflection->getAttributes(Hidden::class);
        if (! empty($hiddenAttributes)) {
            $hidden = array_merge($hidden, $hiddenAttributes[0]->newInstance()->fields);
        }

        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();

            // Detect ID and Primary Key
            $idAttr = $property->getAttributes(Id::class);
            if (! empty($idAttr)) {
                $primaryKey = $propertyName;
            }

            // Detect Columns
            $colAttr = $property->getAttributes(Column::class);
            if (! empty($colAttr)) {
                $columns[$propertyName] = $colAttr[0]->newInstance();
            }

            // Detect Relationships
            $hmAttr = $property->getAttributes(HasMany::class);
            if (! empty($hmAttr)) {
                $hasMany[$propertyName] = $hmAttr[0]->newInstance();
            }

            $btAttr = $property->getAttributes(BelongsTo::class);
            if (! empty($btAttr)) {
                $belongsTo[$propertyName] = $btAttr[0]->newInstance();
            }

            $hoAttr = $property->getAttributes(HasOne::class);
            if (! empty($hoAttr)) {
                $hasOne[$propertyName] = $hoAttr[0]->newInstance();
            }

            $mtmAttr = $property->getAttributes(ManyToMany::class);
            if (! empty($mtmAttr)) {
                $manyToMany[$propertyName] = $mtmAttr[0]->newInstance();
            }

            $mToAttr = $property->getAttributes(MorphTo::class);
            if (! empty($mToAttr)) {
                $morphTo[$propertyName] = $mToAttr[0]->newInstance();
            }

            $mmAttr = $property->getAttributes(MorphMany::class);
            if (! empty($mmAttr)) {
                $morphMany[$propertyName] = $mmAttr[0]->newInstance();
            }

            $moAttr = $property->getAttributes(MorphOne::class);
            if (! empty($moAttr)) {
                $morphOne[$propertyName] = $moAttr[0]->newInstance();
            }

            // Detect Hidden fields at property level
            if (! empty($property->getAttributes(Hidden::class))) {
                $hidden[] = $propertyName;
            }
        }

        $this->entities[$resourceName] = [
            'resource' => $resourceName,
            'class' => $className,
            'table' => $entityConfig->table,
            'morphName' => $entityConfig->morphName,
            'primaryKey' => $primaryKey,
            'columns' => $columns,
            'hasMany' => $hasMany,
            'belongsTo' => $belongsTo,
            'hasOne' => $hasOne,
            'manyToMany' => $manyToMany,
            'morphTo' => $morphTo,
            'morphMany' => $morphMany,
            'morphOne' => $morphOne,
            'hidden' => $hidden,
            'tenantAware' => $isTenantAware,
            'tenantColumn' => $tenantColumn,
            'auditable' => $isAuditable,
            'softDelete' => $isSoftDelete,
        ];

        if ($entityConfig->morphName) {
            $this->morphMapArr[$entityConfig->morphName] = $className;
            $this->classToMorph[$className] = $entityConfig->morphName;
        } else {
            // Default to resource name if no morphName is provided
            $this->morphMapArr[$resourceName] = $className;
            $this->classToMorph[$className] = $resourceName;
        }
    }

    public function getEntityConfig(string $resource): ?array
    {
        return $this->entities[$resource] ?? null;
    }

    public function getClassByMorph(string $morphName): string
    {
        return $this->morphMapArr[$morphName] ?? $morphName;
    }

    public function getMorphByClass(string $className): string
    {
        // Try exact match
        if (isset($this->classToMorph[$className])) {
            return $this->classToMorph[$className];
        }

        // Try fuzzy match (with/without leading backslash)
        $clean = ltrim($className, '\\');
        foreach ($this->classToMorph as $fullClass => $morph) {
            if (ltrim($fullClass, '\\') === $clean) {
                return $morph;
            }
        }

        return $className;
    }

    public function getAllEntities(): array
    {
        return $this->entities;
    }

    /**
     * Search for an entity by its SQL table name.
     */
    public function findEntityByTable(string $tableName): ?array
    {
        foreach ($this->entities as $config) {
            if ($config['table'] === $tableName) {
                return $config;
            }
        }

        return null;
    }

    /**
     * Search for an entity by its PHP class name.
     */
    public function findEntityByClass(string $className): ?array
    {
        foreach ($this->entities as $config) {
            if ($config['class'] === $className || str_ends_with($config['class'], '\\'.$className)) {
                return $config;
            }
        }

        return null;
    }

    /**
     * Bulk-loads the registry from a pre-built cache array.
     * Used in production to avoid filesystem scanning and reflection.
     */
    public function hydrateFromCache(array $entities): void
    {
        $this->entities = $entities;
    }
}
