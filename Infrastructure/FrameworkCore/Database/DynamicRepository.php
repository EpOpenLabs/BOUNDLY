<?php

namespace Infrastructure\FrameworkCore\Database;

use Illuminate\Support\Facades\DB;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Exception;

/**
 * Advanced Dynamic Repository with:
 * - Nested relation loading (e.g., ?include=posts.comments.author)
 * - Cursor-based pagination (scalable for large datasets)
 * - Extended filter operators (_like, _gt, _lt, _gte, _lte, _not, _in, _null)
 * - OR filter grouping (?or[name_like]=john&or[email_like]=john)
 */
class DynamicRepository
{
    public function __construct(protected EntityRegistry $registry) {}

    /**
     * Resolves the entity configuration by resource name or SQL table name.
     */
    protected function resolveConfig(string $resource): array
    {
        $config = $this->registry->getEntityConfig($resource);
        if (!$config) {
            $config = $this->registry->findEntityByTable($resource);
        }

        if (!$config) {
            throw new Exception(__('core::messages.resource_not_found', ['resource' => $resource]), 404);
        }

        return $config;
    }

    /**
     * Builds a secured query with soft-delete, filters, OR-groups, and multi-tenancy.
     */
    protected function getQuery(string $resource, array $filters = [])
    {
        $config = $this->resolveConfig($resource);
        $query  = DB::table($config['table']);

        // Apply Soft Deletes
        if ($config['softDelete']) {
            $query->whereNull($config['table'] . '.deleted_at');
        }

        // Multi-Tenancy (scopes to current tenant invisibly)
        if ($config['tenantAware'] && request()->hasHeader('X-Tenant-ID')) {
            $query->where($config['tenantColumn'], request()->header('X-Tenant-ID'));
        }

        // Standard AND filters
        foreach ($filters as $rawField => $value) {
            if (in_array($rawField, ['page', 'per_page', 'include', 'cursor', 'or', 'sort', 'direction'])) {
                continue;
            }
            $this->applyFilter($query, $config, $rawField, $value);
        }

        // OR filter groups: ?or[name_like]=john&or[email_like]=john
        if (!empty($filters['or']) && is_array($filters['or'])) {
            $query->where(function ($q) use ($config, $filters) {
                foreach ($filters['or'] as $rawField => $value) {
                    $this->applyFilter($q, $config, $rawField, $value, 'or');
                }
            });
        }

        // Sorting: ?sort=created_at&direction=desc
        if (!empty($filters['sort'])) {
            $sortField = $filters['sort'];
            if (isset($config['columns'][$sortField]) || $sortField === $config['primaryKey']) {
                $direction = strtolower($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                $query->orderBy($config['table'] . '.' . $sortField, $direction);
            }
        } else {
            $query->orderBy($config['table'] . '.' . $config['primaryKey'], 'asc');
        }

        return $query;
    }

    /**
     * Applies a single filter condition to a query.
     * Supported suffixes: _like, _gt, _lt, _gte, _lte, _not, _in, _null
     */
    protected function applyFilter($query, array $config, string $rawField, mixed $value, string $boolean = 'and'): void
    {
        $field    = $rawField;
        $operator = '=';

        $suffixes = [
            '_like' => ['operator' => 'like', 'transform' => fn($v) => "%{$v}%", 'trim' => 5],
            '_gt'   => ['operator' => '>',    'transform' => null,                'trim' => 3],
            '_lt'   => ['operator' => '<',    'transform' => null,                'trim' => 3],
            '_gte'  => ['operator' => '>=',   'transform' => null,                'trim' => 4],
            '_lte'  => ['operator' => '<=',   'transform' => null,                'trim' => 4],
            '_not'  => ['operator' => '!=',   'transform' => null,                'trim' => 4],
        ];

        foreach ($suffixes as $suffix => $opts) {
            if (str_ends_with($rawField, $suffix)) {
                $field    = substr($rawField, 0, -$opts['trim']);
                $operator = $opts['operator'];
                $value    = $opts['transform'] ? ($opts['transform'])($value) : $value;
                break;
            }
        }

        // _in: ?ids_in=1,2,3
        if (str_ends_with($rawField, '_in')) {
            $field = substr($rawField, 0, -3);
            if (isset($config['columns'][$field]) || $field === $config['primaryKey']) {
                $values = is_array($value) ? $value : explode(',', $value);
                $query->whereIn($field, $values, $boolean);
            }
            return;
        }

        // _null: ?deleted_at_null=1 (check IS NULL / IS NOT NULL)
        if (str_ends_with($rawField, '_null')) {
            $field = substr($rawField, 0, -5);
            if (isset($config['columns'][$field])) {
                if ($value) {
                    $query->whereNull($field, $boolean);
                } else {
                    $query->whereNotNull($field, $boolean);
                }
            }
            return;
        }

        if (isset($config['columns'][$field]) || $field === $config['primaryKey']) {
            $query->where($field, $operator, $value, $boolean);
        }
    }

    /**
     * Standard offset-based paginated list.
     */
    public function paginate(string $resource, int $perPage = 15, array $includes = [], array $filters = [])
    {
        $paginator = $this->getQuery($resource, $filters)->paginate($perPage);
        $config    = $this->resolveConfig($resource);

        $paginator->getCollection()->transform(function ($item) use ($config, $includes) {
            return (object) $this->applyIncludes((array) $item, $config, $includes);
        });

        return $paginator;
    }

    /**
     * Cursor-based pagination: highly efficient for large datasets and infinite scroll.
     * Accepts ?cursor=<last_id> and returns up to $perPage records after that cursor.
     */
    public function cursorPaginate(string $resource, int $perPage = 15, array $includes = [], array $filters = []): array
    {
        $config = $this->resolveConfig($resource);
        $query  = $this->getQuery($resource, $filters);
        $pk     = $config['primaryKey'];
        $cursor = request()->query('cursor');

        if ($cursor) {
            $query->where($config['table'] . '.' . $pk, '>', $cursor);
        }

        // Fetch one extra record to detect if there is a next page
        $results = $query->limit($perPage + 1)->get();
        $hasMore = $results->count() > $perPage;
        $items   = $results->take($perPage);

        $items = $items->map(fn($item) => $this->applyIncludes((array) $item, $config, $includes));

        return [
            'data'        => $items->values(),
            'next_cursor' => $hasMore ? $items->last()->{$pk} ?? null : null,
            'has_more'    => $hasMore,
        ];
    }

    public function all(string $resource, array $includes = [], array $filters = [])
    {
        $collection = $this->getQuery($resource, $filters)->get();
        $config     = $this->resolveConfig($resource);

        return $collection->map(fn($item) => $this->applyIncludes((array) $item, $config, $includes));
    }

    public function find(string $resource, $id, array $filters = [])
    {
        $config = $this->resolveConfig($resource);
        $item   = $this->getQuery($resource, $filters)
            ->where($config['table'] . '.' . $config['primaryKey'], $id)
            ->first();

        return $item ? (array) $item : null;
    }

    public function findWithRelations(string $resource, $id, array $includes = [])
    {
        $item = $this->find($resource, $id);
        if (!$item) return null;

        $config = $this->resolveConfig($resource);
        return $this->applyIncludes($item, $config, $includes);
    }

    // -------------------------------------------------------------------------
    // RELATION LOADING (supports dot-notation nesting: posts.comments.author)
    // -------------------------------------------------------------------------

    protected function applyIncludes(array $item, array $config, array $includes): array
    {
        if (!empty($includes)) {
            $item = $this->loadRelations($item, $config, $includes);
        }
        return $this->filterHidden($item, $config);
    }

    /**
     * Loads relations. Supports dot-notation for nesting.
     * e.g. ['posts', 'posts.comments', 'posts.comments.author']
     */
    protected function loadRelations(array $item, array $config, array $includes): array
    {
        // Group by top-level relation name
        $topLevel = [];
        $nested   = [];

        foreach ($includes as $include) {
            if (str_contains($include, '.')) {
                [$parent, $rest] = explode('.', $include, 2);
                $nested[$parent][] = $rest;
            } else {
                $topLevel[] = $include;
            }
        }

        // Load all unique top-level includes (including ones that also have nesting)
        $allTopLevel = array_unique(array_merge($topLevel, array_keys($nested)));

        foreach ($allTopLevel as $relationName) {
            $item = $this->loadSingleRelation($item, $config, $relationName, $nested[$relationName] ?? []);
        }

        return $item;
    }

    protected function loadSingleRelation(array $item, array $config, string $relationName, array $subIncludes): array
    {
        // BelongsTo (smart key mapping: 'user' or 'user_id')
        $btKey = isset($config['belongsTo'][$relationName])
            ? $relationName
            : (isset($config['belongsTo'][$relationName . '_id']) ? $relationName . '_id' : null);

        if ($btKey) {
            $relation    = $config['belongsTo'][$btKey];
            $foreignCol  = $relation->foreignKey ?: (str_ends_with($btKey, '_id') ? $btKey : $btKey . '_id');
            $relatedConf = $this->registry->findEntityByClass($relation->relatedEntity);

            if ($relatedConf && isset($item[$foreignCol])) {
                $relatedRow = DB::table($relatedConf['table'])
                    ->where($relatedConf['primaryKey'], $item[$foreignCol])
                    ->first();

                if ($relatedRow) {
                    $relatedArr = $this->filterHidden((array) $relatedRow, $relatedConf);
                    // Recursively load sub-includes
                    if (!empty($subIncludes)) {
                        $relatedArr = $this->loadRelations($relatedArr, $relatedConf, $subIncludes);
                    }
                    $item[$relationName] = $relatedArr;
                }
            }
        }

        // HasMany
        if (isset($config['hasMany'][$relationName])) {
            $relation    = $config['hasMany'][$relationName];
            $relatedConf = $this->registry->findEntityByClass($relation->relatedEntity);

            if ($relatedConf) {
                $foreignCol = $relation->foreignKey ?: \Illuminate\Support\Str::singular($config['table']) . '_id';
                $rows       = DB::table($relatedConf['table'])
                    ->where($foreignCol, $item[$config['primaryKey']])
                    ->get();

                $item[$relationName] = $rows->map(function ($row) use ($relatedConf, $subIncludes) {
                    $arr = $this->filterHidden((array) $row, $relatedConf);
                    if (!empty($subIncludes)) {
                        $arr = $this->loadRelations($arr, $relatedConf, $subIncludes);
                    }
                    return $arr;
                })->toArray();
            }
        }

        // HasOne
        if (isset($config['hasOne'][$relationName])) {
            $relation    = $config['hasOne'][$relationName];
            $relatedConf = $this->registry->findEntityByClass($relation->relatedEntity);

            if ($relatedConf) {
                $foreignCol = $relation->foreignKey ?: \Illuminate\Support\Str::singular($config['table']) . '_id';
                $row        = DB::table($relatedConf['table'])
                    ->where($foreignCol, $item[$config['primaryKey']])
                    ->first();

                if ($row) {
                    $arr = $this->filterHidden((array) $row, $relatedConf);
                    if (!empty($subIncludes)) {
                        $arr = $this->loadRelations($arr, $relatedConf, $subIncludes);
                    }
                    $item[$relationName] = $arr;
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

    // -------------------------------------------------------------------------
    // WRITE OPERATIONS
    // -------------------------------------------------------------------------

    public function insert(string $resource, array $data)
    {
        $config         = $this->resolveConfig($resource);
        $userIdentifier = request()->header('X-User-ID') ?? 'System';

        if ($config['tenantAware'] && request()->hasHeader('X-Tenant-ID')) {
            $data[$config['tenantColumn']] = request()->header('X-Tenant-ID');
        }

        if ($config['auditable']) {
            $data['created_by'] = $userIdentifier;
            $data['updated_by'] = $userIdentifier;
        }

        $data['created_at'] = now()->toDateTimeString();
        $data['updated_at'] = now()->toDateTimeString();

        $id                      = DB::table($config['table'])->insertGetId($data);
        $data[$config['primaryKey']] = $id;

        return $this->filterHidden($data, $config);
    }

    public function update(string $resource, $id, array $data)
    {
        $config         = $this->resolveConfig($resource);
        $userIdentifier = request()->header('X-User-ID') ?? 'System';

        if ($config['auditable']) {
            $data['updated_by'] = $userIdentifier;
        }

        $data['updated_at'] = now()->toDateTimeString();

        DB::table($config['table'])->where($config['primaryKey'], $id)->update($data);
        return $this->find($resource, $id);
    }

    public function delete(string $resource, $id): bool
    {
        $config = $this->resolveConfig($resource);
        $query  = DB::table($config['table'])->where($config['primaryKey'], $id);

        if ($config['softDelete']) {
            return $query->update(['deleted_at' => now()]) > 0;
        }

        return $query->delete() > 0;
    }
}
