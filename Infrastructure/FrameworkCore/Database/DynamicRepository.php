<?php

namespace Infrastructure\FrameworkCore\Database;

use Illuminate\Support\Facades\DB;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Exception;

class DynamicRepository
{
    public function __construct(protected EntityRegistry $registry) {}

    /**
     * Resolves the entity configuration by resource name (e.g., 'users')
     * or directly by SQL table name (e.g., 'users').
     */
    protected function resolveConfig(string $resource): array
    {
        $config = $this->registry->getEntityConfig($resource);
        if (!$config) {
            $config = $this->registry->findEntityByTable($resource);
        }

        if (!$config) {
            throw new Exception(__('core::messages.resource_not_found', ['resource' => $resource]));
        }

        return $config;
    }

    /**
     * Returns a prepared DB Query Builder and injects
     * Multitenant, SoftDelete, and Filter security logic.
     */
    protected function getQuery(string $resource, array $filters = [])
    {
        $config = $this->resolveConfig($resource);
        $query = DB::table($config['table']);

        // Apply Soft Deletes if supported
        if ($config['softDelete']) {
            $query->whereNull('deleted_at');
        }

        // Apply Dynamic URL Filters (Pro Operator Support)
        foreach ($filters as $rawField => $value) {
            if (in_array($rawField, ['page', 'per_page', 'include'])) {
                continue;
            }

            // Operator detection: field_like, field_gt, etc.
            $field = $rawField;
            $operator = '=';

            if (str_ends_with($rawField, '_like')) {
                $field = substr($rawField, 0, -5);
                $operator = 'like';
                $value = "%{$value}%";
            } elseif (str_ends_with($rawField, '_gt')) {
                $field = substr($rawField, 0, -3);
                $operator = '>';
            } elseif (str_ends_with($rawField, '_lt')) {
                $field = substr($rawField, 0, -3);
                $operator = '<';
            } elseif (str_ends_with($rawField, '_gte')) {
                $field = substr($rawField, 0, -4);
                $operator = '>=';
            } elseif (str_ends_with($rawField, '_lte')) {
                $field = substr($rawField, 0, -4);
                $operator = '<=';
            }

            // If column exists, apply filter
            if (isset($config['columns'][$field]) || $field === $config['primaryKey']) {
                $query->where($field, $operator, $value);
            }
        }
        
        // Invisible Multitenant Integration:
        // Scopes the query to the specific tenant ID
        if ($config['tenantAware'] && request()->hasHeader('X-Tenant-ID')) {
             $query->where($config['tenantColumn'], request()->header('X-Tenant-ID'));
        }

        return $query;
    }

    public function paginate(string $resource, int $perPage = 15, array $includes = [], array $filters = [])
    {
        $paginator = $this->getQuery($resource, $filters)->paginate($perPage);
        $config = $this->resolveConfig($resource);

        // Transform collection to apply relations and filtering
        $paginator->getCollection()->transform(function($item) use ($config, $includes) {
            $arrayItem = (array)$item;
            return (object) $this->applyIncludes($arrayItem, $config, $includes);
        });

        return $paginator;
    }

    public function all(string $resource, array $includes = [], array $filters = [])
    {
        $collection = $this->getQuery($resource, $filters)->get();
        $config = $this->resolveConfig($resource);

        return $collection->map(function ($item) use ($config, $includes) {
            return $this->applyIncludes((array)$item, $config, $includes);
        });
    }

    public function find(string $resource, $id, array $filters = [])
    {
        $config = $this->resolveConfig($resource);
        $item = $this->getQuery($resource, $filters)->where($config['primaryKey'], $id)->first();
        
        return $item ? (array) $item : null;
    }

    public function findWithRelations(string $resource, $id, array $includes = [])
    {
        $item = $this->find($resource, $id);
        if (!$item) return null;

        $config = $this->resolveConfig($resource);
        return $this->applyIncludes($item, $config, $includes);
    }

    protected function applyIncludes(array $item, array $config, array $includes): array
    {
        if (!empty($includes)) {
            $item = $this->loadRelations($item, $config, $includes);
        }
        return $this->filterHidden($item, $config);
    }

    protected function loadRelations(array $item, array $config, array $includes): array
    {
        foreach ($includes as $relationName) {
            // Smart Mapping: Search for 'user' or 'user_id'
            $actualRelationKey = isset($config['belongsTo'][$relationName]) 
                ? $relationName 
                : (isset($config['belongsTo'][$relationName . '_id']) ? $relationName . '_id' : null);

            // Case BelongsTo
            if ($actualRelationKey) {
                $relation = $config['belongsTo'][$actualRelationKey];
                $foreignCol = $relation->foreignKey ?: (str_ends_with($actualRelationKey, '_id') ? $actualRelationKey : $actualRelationKey . '_id');
                $relatedConfig = $this->registry->findEntityByClass($relation->relatedEntity);
                
                if ($relatedConfig && isset($item[$foreignCol])) {
                    $relatedData = DB::table($relatedConfig['table'])
                        ->where($relatedConfig['primaryKey'], $item[$foreignCol])
                        ->first();
                    
                    if ($relatedData) {
                        $item[$relationName] = $this->filterHidden((array)$relatedData, $relatedConfig);
                    }
                }
            }

            // Case HasMany
            if (isset($config['hasMany'][$relationName])) {
                $relation = $config['hasMany'][$relationName];
                $relatedConfig = $this->registry->findEntityByClass($relation->relatedEntity);
                
                if ($relatedConfig) {
                    $foreignCol = $relation->foreignKey ?: \Illuminate\Support\Str::singular($config['table']) . '_id';

                    $results = DB::table($relatedConfig['table'])
                        ->where($foreignCol, $item[$config['primaryKey']])
                        ->get();
                    
                    $item[$relationName] = $results->map(function($r) use ($relatedConfig) {
                        return $this->filterHidden((array)$r, $relatedConfig);
                    })->toArray();
                }
            }

            // Case HasOne
            if (isset($config['hasOne'][$relationName])) {
                $relation = $config['hasOne'][$relationName];
                $relatedConfig = $this->registry->findEntityByClass($relation->relatedEntity);

                if ($relatedConfig) {
                    $foreignCol = $relation->foreignKey ?: \Illuminate\Support\Str::singular($config['table']) . '_id';

                    $relatedData = DB::table($relatedConfig['table'])
                        ->where($foreignCol, $item[$config['primaryKey']])
                        ->first();

                    if ($relatedData) {
                        $item[$relationName] = $this->filterHidden((array)$relatedData, $relatedConfig);
                    }
                }
            }
        }
        return $item;
    }

    protected function filterHidden(array $data, array $config): array
    {
        foreach ($config['hidden'] ?? [] as $hiddenField) {
            unset($data[$hiddenField]);
        }
        return $data;
    }

    public function insert(string $resource, array $data)
    {
        $config = $this->resolveConfig($resource);
        $userIdentifier = request()->header('X-User-ID') ?? 'System';
        
        // Auto-inject tenant
        if ($config['tenantAware'] && request()->hasHeader('X-Tenant-ID')) {
            $data[$config['tenantColumn']] = request()->header('X-Tenant-ID');
        }

        // Auto-audit creation
        if ($config['auditable']) {
            $data['created_by'] = $userIdentifier;
            $data['updated_by'] = $userIdentifier;
        }

        // Auto-timestamps
        $data['created_at'] = now()->toDateTimeString();
        $data['updated_at'] = now()->toDateTimeString();

        $id = $this->getQuery($resource)->insertGetId($data);
        $data[$config['primaryKey']] = $id;

        return $this->filterHidden($data, $config);
    }

    public function update(string $resource, $id, array $data)
    {
        $config = $this->resolveConfig($resource);
        $userIdentifier = request()->header('X-User-ID') ?? 'System';

        // Auto-audit update
        if ($config['auditable']) {
            $data['updated_by'] = $userIdentifier;
        }

        // Auto-timestamps
        $data['updated_at'] = now()->toDateTimeString();

        $this->getQuery($resource)->where($config['primaryKey'], $id)->update($data);
        return $this->find($resource, $id);
    }

    public function delete(string $resource, $id): bool
    {
        $config = $this->resolveConfig($resource);
        $query = DB::table($config['table'])->where($config['primaryKey'], $id);

        if ($config['softDelete']) {
            return $query->update(['deleted_at' => now()]) > 0;
        }

        return $query->delete() > 0;
    }
}
