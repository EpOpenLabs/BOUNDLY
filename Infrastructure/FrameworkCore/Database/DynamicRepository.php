<?php

namespace Infrastructure\FrameworkCore\Database;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Infrastructure\FrameworkCore\Attributes\Behavior\Transactional;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Infrastructure\FrameworkCore\Traits\ChecksPermissions;
use Infrastructure\FrameworkCore\Validation\EntityValidator;

/**
 * Advanced Dynamic Repository with:
 * - Nested relation loading (e.g., ?include=posts.comments.author)
 * - Cursor-based pagination (scalable for large datasets)
 * - Extended filter operators (_like, _gt, _lt, _gte, _lte, _not, _in, _null)
 * - OR filter grouping (?or[name_like]=john&or[email_like]=john)
 * - Transactional support via #[Transactional] attribute
 */
class DynamicRepository
{
    use ChecksPermissions;

    public function __construct(
        protected EntityRegistry $registry,
        protected EntityValidator $validator
    ) {}

    /**
     * Resolves the entity configuration by resource name or SQL table name.
     */
    protected function resolveConfig(string $resource): array
    {
        $config = $this->registry->getEntityConfig($resource);
        if (! $config) {
            $config = $this->registry->findEntityByTable($resource);
        }

        if (! $config) {
            throw new Exception(__('core::messages.resource_not_found', ['resource' => $resource]), 404);
        }

        return $config;
    }

    /**
     * Checks if the entity is marked with the #[Transactional] attribute.
     */
    protected function isTransactional(array $config): bool
    {
        if (! isset($config['class'])) {
            return false;
        }

        $reflection = new \ReflectionClass($config['class']);
        
        return $reflection->getAttributes(Transactional::class) !== [];
    }

    /**
     * Gets the Transactional attribute configuration for an entity.
     */
    protected function getTransactionalConfig(array $config): ?Transactional
    {
        if (! isset($config['class'])) {
            return null;
        }

        $reflection = new \ReflectionClass($config['class']);
        $attributes = $reflection->getAttributes(Transactional::class);

        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * Builds a secured query with soft-delete, filters, OR-groups, and multi-tenancy.
     */
    protected function getQuery(string $resource, array $filters = [])
    {
        $config = $this->resolveConfig($resource);
        $query = DB::table($config['table']);

        // Apply Soft Deletes
        if ($config['softDelete']) {
            $query->whereNull($config['table'].'.deleted_at');
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
        if (! empty($filters['or']) && is_array($filters['or'])) {
            $query->where(function ($q) use ($config, $filters) {
                foreach ($filters['or'] as $rawField => $value) {
                    $this->applyFilter($q, $config, $rawField, $value, 'or');
                }
            });
        }

        // Sorting: ?sort=created_at&direction=desc
        if (! empty($filters['sort'])) {
            $sortField = $filters['sort'];
            if (isset($config['columns'][$sortField]) || $sortField === $config['primaryKey']) {
                $direction = strtolower($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                $query->orderBy($config['table'].'.'.$sortField, $direction);
            }
        } else {
            $query->orderBy($config['table'].'.'.$config['primaryKey'], 'asc');
        }

        return $query;
    }

    /**
     * Applies a single filter condition to a query.
     * Supported suffixes: _like, _gt, _lt, _gte, _lte, _not, _in, _null
     */
    protected function applyFilter($query, array $config, string $rawField, mixed $value, string $boolean = 'and'): void
    {
        $field = $rawField;
        $operator = '=';
        $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $suffixes = [
            '_like' => ['operator' => $likeOp,  'transform' => fn ($v) => "%{$v}%", 'trim' => 5],
            '_gt' => ['operator' => '>',      'transform' => null,               'trim' => 3],
            '_lt' => ['operator' => '<',    'transform' => null,                'trim' => 3],
            '_gte' => ['operator' => '>=',   'transform' => null,                'trim' => 4],
            '_lte' => ['operator' => '<=',   'transform' => null,                'trim' => 4],
            '_not' => ['operator' => '!=',   'transform' => null,                'trim' => 4],
        ];

        foreach ($suffixes as $suffix => $opts) {
            if (str_ends_with($rawField, $suffix)) {
                $field = substr($rawField, 0, -$opts['trim']);
                $operator = $opts['operator'];
                $value = $opts['transform'] ? ($opts['transform'])($value) : $value;
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
        $config = $this->resolveConfig($resource);

        $items = $this->loadEagerRelations($paginator->items(), $config, $includes);
        $paginator->getCollection()->transform(function ($item, $key) use ($items) {
            return (object) $items[$key];
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
        $query = $this->getQuery($resource, $filters);
        $pk = $config['primaryKey'];
        $cursor = request()->query('cursor');

        if ($cursor) {
            $query->where($config['table'].'.'.$pk, '>', $cursor);
        }

        // Fetch one extra record to detect if there is a next page
        $results = $query->limit($perPage + 1)->get();
        $hasMore = $results->count() > $perPage;
        $items = $results->take($perPage);

        $loadedItems = $this->loadEagerRelations($items, $config, $includes);

        return [
            'data' => array_values($loadedItems),
            'next_cursor' => $hasMore ? $items->last()->{$pk} ?? null : null,
            'has_more' => $hasMore,
        ];
    }

    public function all(string $resource, array $includes = [], array $filters = [])
    {
        $collection = $this->getQuery($resource, $filters)->get();
        $config = $this->resolveConfig($resource);

        return $this->loadEagerRelations($collection, $config, $includes);
    }

    public function find(string $resource, $id, array $filters = [])
    {
        $config = $this->resolveConfig($resource);
        $item = $this->getQuery($resource, $filters)
            ->where($config['table'].'.'.$config['primaryKey'], $id)
            ->first();

        if (! $item) {
            return null;
        }

        return (array) $item;
    }

    public function findWithRelations(string $resource, $id, array $includes = [])
    {
        $item = $this->find($resource, $id);
        if (! $item) {
            return null;
        }

        $config = $this->resolveConfig($resource);
        $results = $this->loadEagerRelations([$item], $config, $includes);

        return $results[0] ?? null;
    }

    // -------------------------------------------------------------------------
    // RELATION LOADING (Eager Loading to prevent N+1)
    // -------------------------------------------------------------------------

    protected function loadEagerRelations(iterable $items, array $config, array $includes): array
    {
        DB::enableQueryLog();
        $itemsArray = is_array($items) ? $items : (method_exists($items, 'toArray') ? $items->toArray() : (array) $items);
        $itemsArray = array_map(fn ($item) => (array) $item, $itemsArray);

        if (empty($itemsArray)) {
            return [];
        }

        if (empty($includes)) {
            return array_map(fn ($item) => $this->filterHidden($item, $config), $itemsArray);
        }

        // Group by top-level relation name
        $topLevel = [];
        $nested = [];

        foreach ($includes as $include) {
            if (str_contains($include, '.')) {
                [$parent, $rest] = explode('.', $include, 2);
                $nested[$parent][] = $rest;
            } else {
                $topLevel[] = $include;
            }
        }

        $allTopLevel = array_unique(array_merge($topLevel, array_keys($nested)));

        foreach ($allTopLevel as $relationName) {
            $itemsArray = $this->loadSingleRelationEagerly($itemsArray, $config, $relationName, $nested[$relationName] ?? []);
        }

        return array_map(fn ($item) => $this->filterHidden($item, $config), $itemsArray);
    }

    protected function loadSingleRelationEagerly(array $itemsArray, array $config, string $relationName, array $subIncludes): array
    {
        // 1. BelongsTo
        $btKey = isset($config['belongsTo'][$relationName])
            ? $relationName
            : (isset($config['belongsTo'][$relationName.'_id']) ? $relationName.'_id' : null);

        if ($btKey) {
            $relation = $config['belongsTo'][$btKey];
            $foreignCol = $relation->foreignKey ?: (str_ends_with($btKey, '_id') ? $btKey : $btKey.'_id');
            $relatedConf = $this->registry->findEntityByClass($relation->relatedEntity);

            if ($relatedConf) {
                $foreignKeys = array_unique(array_filter(array_column($itemsArray, $foreignCol)));

                if (! empty($foreignKeys)) {
                    $relatedRows = DB::table($relatedConf['table'])
                        ->whereIn($relatedConf['primaryKey'], $foreignKeys)
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->toArray();

                    if (! empty($subIncludes)) {
                        $relatedRows = $this->loadEagerRelations($relatedRows, $relatedConf, $subIncludes);
                    }

                    $relatedById = array_column($relatedRows, null, $relatedConf['primaryKey']);

                    foreach ($itemsArray as &$item) {
                        $fk = $item[$foreignCol] ?? null;
                        $item[$relationName] = $fk && isset($relatedById[$fk]) ? $relatedById[$fk] : null;
                    }
                } else {
                    foreach ($itemsArray as &$item) {
                        $item[$relationName] = null;
                    }
                }
            }

            return $itemsArray;
        }

        // 2. HasMany
        if (isset($config['hasMany'][$relationName])) {
            $relation = $config['hasMany'][$relationName];
            $relatedConf = $this->registry->findEntityByClass($relation->relatedEntity);

            if ($relatedConf) {
                $foreignCol = $relation->foreignKey ?: Str::singular($config['table']).'_id';
                $parentIds = array_unique(array_filter(array_column($itemsArray, $config['primaryKey'])));

                if (! empty($parentIds)) {
                    $relatedRows = DB::table($relatedConf['table'])
                        ->whereIn($foreignCol, $parentIds)
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->toArray();

                    if (! empty($subIncludes)) {
                        $relatedRows = $this->loadEagerRelations($relatedRows, $relatedConf, $subIncludes);
                    }

                    $grouped = [];
                    foreach ($relatedRows as $row) {
                        $grouped[$row[$foreignCol]][] = $row;
                    }

                    foreach ($itemsArray as &$item) {
                        $pk = $item[$config['primaryKey']] ?? null;
                        $item[$relationName] = $pk && isset($grouped[$pk]) ? $grouped[$pk] : [];
                    }
                } else {
                    foreach ($itemsArray as &$item) {
                        $item[$relationName] = [];
                    }
                }
            }

            return $itemsArray;
        }

        // 3. HasOne
        if (isset($config['hasOne'][$relationName])) {
            $relation = $config['hasOne'][$relationName];
            $relatedConf = $this->registry->findEntityByClass($relation->relatedEntity);

            if ($relatedConf) {
                $foreignCol = $relation->foreignKey ?: Str::singular($config['table']).'_id';
                $parentIds = array_unique(array_filter(array_column($itemsArray, $config['primaryKey'])));

                if (! empty($parentIds)) {
                    $relatedRows = DB::table($relatedConf['table'])
                        ->whereIn($foreignCol, $parentIds)
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->toArray();

                    if (! empty($subIncludes)) {
                        $relatedRows = $this->loadEagerRelations($relatedRows, $relatedConf, $subIncludes);
                    }

                    $grouped = [];
                    foreach ($relatedRows as $row) {
                        $grouped[$row[$foreignCol]][] = $row;
                    }

                    foreach ($itemsArray as &$item) {
                        $pk = $item[$config['primaryKey']] ?? null;
                        $item[$relationName] = $pk && isset($grouped[$pk]) ? $grouped[$pk][0] : null;
                    }
                } else {
                    foreach ($itemsArray as &$item) {
                        $item[$relationName] = null;
                    }
                }
            }

            return $itemsArray;
        }

        // 4. ManyToMany
        if (isset($config['manyToMany'][$relationName])) {
            $relation = $config['manyToMany'][$relationName];
            $relatedConf = $this->registry->findEntityByClass($relation->relatedEntity);

            if ($relatedConf) {
                $pivotTable = $relation->pivotTable ?: (Str::singular(min($config['table'], $relatedConf['table'])).'_'.Str::singular(max($config['table'], $relatedConf['table'])));
                $fk1 = $relation->foreignPivotKey ?: Str::singular($config['table']).'_id';
                $fk2 = $relation->relatedPivotKey ?: Str::singular($relatedConf['table']).'_id';

                $parentIds = array_unique(array_filter(array_column($itemsArray, $config['primaryKey'])));

                if (! empty($parentIds)) {
                    $pivotRows = DB::table($pivotTable)->whereIn($fk1, $parentIds)->get();
                    $relatedIds = $pivotRows->pluck($fk2)->unique()->toArray();

                    if (! empty($relatedIds)) {
                        $relatedRows = DB::table($relatedConf['table'])
                            ->whereIn($relatedConf['primaryKey'], $relatedIds)
                            ->get()
                            ->map(fn ($row) => (array) $row)
                            ->toArray();

                        if (! empty($subIncludes)) {
                            $relatedRows = $this->loadEagerRelations($relatedRows, $relatedConf, $subIncludes);
                        }

                        $relatedById = array_column($relatedRows, null, $relatedConf['primaryKey']);

                        $grouped = [];
                        foreach ($pivotRows as $pivot) {
                            $pId = $pivot->{$fk1};
                            $rId = $pivot->{$fk2};
                            if (isset($relatedById[$rId])) {
                                $grouped[$pId][] = $relatedById[$rId];
                            }
                        }

                        foreach ($itemsArray as &$item) {
                            $pk = $item[$config['primaryKey']] ?? null;
                            $item[$relationName] = $pk && isset($grouped[$pk]) ? $grouped[$pk] : [];
                        }
                    } else {
                        foreach ($itemsArray as &$item) {
                            $item[$relationName] = [];
                        }
                    }
                } else {
                    foreach ($itemsArray as &$item) {
                        $item[$relationName] = [];
                    }
                }
            }

            return $itemsArray;
        }

        // 5. MorphTo
        if (isset($config['morphTo'][$relationName])) {
            $relation = $config['morphTo'][$relationName];
            $morphName = $relation->name ?: $relationName;
            $idCol = "{$morphName}_id";
            $typeCol = "{$morphName}_type";

            // Group items by type to perform separate queries
            $byType = [];
            foreach ($itemsArray as $index => $item) {
                if (! empty($item[$idCol]) && ! empty($item[$typeCol])) {
                    $byType[$item[$typeCol]][$item[$idCol]][] = $index;
                } else {
                    $itemsArray[$index][$relationName] = null;
                }
            }

            foreach ($byType as $morphType => $idsToIndices) {
                $ids = array_keys($idsToIndices);
                $fullClass = $this->registry->getClassByMorph($morphType);
                $relatedConf = $this->registry->findEntityByClass($fullClass);

                if ($relatedConf) {
                    $relatedRows = DB::table($relatedConf['table'])
                        ->whereIn($relatedConf['primaryKey'], $ids)
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->toArray();

                    if (! empty($subIncludes)) {
                        $relatedRows = $this->loadEagerRelations($relatedRows, $relatedConf, $subIncludes);
                    }

                    $relatedById = array_column($relatedRows, null, $relatedConf['primaryKey']);

                    foreach ($idsToIndices as $id => $indices) {
                        foreach ($indices as $idx) {
                            $itemsArray[$idx][$relationName] = $relatedById[$id] ?? null;
                        }
                    }
                }
            }

            return $itemsArray;
        }

        // 6. MorphMany
        if (isset($config['morphMany'][$relationName])) {
            $relation = $config['morphMany'][$relationName];
            $relatedConf = $this->registry->findEntityByClass($relation->relatedEntity);

            if ($relatedConf) {
                $morphName = $relation->relation;
                $idCol = "{$morphName}_id";
                $typeCol = "{$morphName}_type";
                $parentIds = array_unique(array_filter(array_column($itemsArray, $config['primaryKey'])));

                if (! empty($parentIds)) {
                    $morphAlias = $this->registry->getMorphByClass($config['class']);
                    $relatedRows = DB::table($relatedConf['table'])
                        ->where($typeCol, $morphAlias)
                        ->whereIn($idCol, $parentIds)
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->toArray();

                    if (! empty($subIncludes)) {
                        $relatedRows = $this->loadEagerRelations($relatedRows, $relatedConf, $subIncludes);
                    }

                    $grouped = [];
                    foreach ($relatedRows as $row) {
                        $grouped[$row[$idCol]][] = $row;
                    }

                    foreach ($itemsArray as &$item) {
                        $pk = $item[$config['primaryKey']] ?? null;
                        $item[$relationName] = $pk && isset($grouped[$pk]) ? $grouped[$pk] : [];
                    }
                } else {
                    foreach ($itemsArray as &$item) {
                        $item[$relationName] = [];
                    }
                }
            }

            return $itemsArray;
        }

        // 7. MorphOne
        if (isset($config['morphOne'][$relationName])) {
            $relation = $config['morphOne'][$relationName];
            $relatedConf = $this->registry->findEntityByClass($relation->relatedEntity);

            if ($relatedConf) {
                $morphName = $relation->relation;
                $idCol = "{$morphName}_id";
                $typeCol = "{$morphName}_type";
                $parentIds = array_unique(array_filter(array_column($itemsArray, $config['primaryKey'])));

                if (! empty($parentIds)) {
                    $morphAlias = $this->registry->getMorphByClass($config['class']);
                    $relatedRows = DB::table($relatedConf['table'])
                        ->where($typeCol, $morphAlias)
                        ->whereIn($idCol, $parentIds)
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->toArray();

                    if (! empty($subIncludes)) {
                        $relatedRows = $this->loadEagerRelations($relatedRows, $relatedConf, $subIncludes);
                    }

                    $grouped = [];
                    foreach ($relatedRows as $row) {
                        $grouped[$row[$idCol]] = $row;
                    }

                    foreach ($itemsArray as &$item) {
                        $pk = $item[$config['primaryKey']] ?? null;
                        $item[$relationName] = $pk && isset($grouped[$pk]) ? $grouped[$pk] : null;
                    }
                } else {
                    foreach ($itemsArray as &$item) {
                        $item[$relationName] = null;
                    }
                }
            }

            return $itemsArray;
        }

        return $itemsArray;
    }

    protected function filterHidden(array $data, array $config): array
    {
        /** @var Authenticatable|null $user */
        $user = auth()->guard()->user();

        // 1. Filter explicitly hidden fields
        foreach ($config['hidden'] ?? [] as $hiddenField) {
            unset($data[$hiddenField]);
        }

        // 2. Filter fields with restricted roles
        foreach ($config['columns'] as $colName => $colAttr) {
            if (! empty($colAttr->roles) && ! $this->userHasRole($user, $colAttr->roles)) {
                unset($data[$colName]);
            }
        }

        return $data;
    }

    // -------------------------------------------------------------------------
    // WRITE OPERATIONS
    // -------------------------------------------------------------------------

    public function insert(string $resource, array $data, array $includes = [])
    {
        $config = $this->resolveConfig($resource);

        if ($this->isTransactional($config)) {
            return DB::transaction(function () use ($resource, $data, $includes, $config) {
                return $this->doInsert($resource, $data, $includes, $config);
            });
        }

        return $this->doInsert($resource, $data, $includes, $config);
    }

    protected function doInsert(string $resource, array $data, array $includes, array $config): array
    {
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

        $manyToManyData = [];
        $hasRelData = [];
        $morphRelData = [];

        foreach ($config['manyToMany'] ?? [] as $relName => $relAttr) {
            if (isset($data[$relName])) {
                $manyToManyData[$relName] = ['attr' => $relAttr, 'ids' => $data[$relName]];
                unset($data[$relName]);
            }
        }

        foreach (array_merge($config['hasMany'] ?? [], $config['hasOne'] ?? []) as $relName => $relAttr) {
            if (isset($data[$relName])) {
                $hasRelData[$relName] = ['attr' => $relAttr, 'data' => $data[$relName]];
                unset($data[$relName]);
            }
        }

        foreach (array_merge($config['morphMany'] ?? [], $config['morphOne'] ?? []) as $relName => $relAttr) {
            if (isset($data[$relName])) {
                $morphRelData[$relName] = ['attr' => $relAttr, 'data' => $data[$relName]];
                unset($data[$relName]);
            }
        }

        $insertData = $this->validator->sanitize($data, $config);
        $id = DB::table($config['table'])->insertGetId($insertData);

        foreach ($manyToManyData as $relName => $relInfo) {
            $this->syncManyToMany($config, $id, $relInfo['attr'], $relInfo['ids']);
        }

        foreach ($hasRelData as $relName => $relInfo) {
            $attr = $relInfo['attr'];
            $items = is_array($relInfo['data']) && ! isset($relInfo['data'][0]) ? [$relInfo['data']] : $relInfo['data'];
            $fk = $attr->foreignKey ?: Str::singular($config['table']).'_id';
            $relatedEntityConf = $this->registry->findEntityByClass($attr->relatedEntity);
            if ($relatedEntityConf) {
                foreach ($items as $childData) {
                    $childData[$fk] = $id;
                    $this->insert($relatedEntityConf['resource'], $childData);
                }
            }
        }

        foreach ($morphRelData as $relName => $relInfo) {
            $attr = $relInfo['attr'];
            $items = is_array($relInfo['data']) && ! isset($relInfo['data'][0]) ? [$relInfo['data']] : $relInfo['data'];
            $morphName = $attr->relation;
            $idCol = "{$morphName}_id";
            $typeCol = "{$morphName}_type";
            $morphType = $this->registry->getMorphByClass($config['class']);
            $relatedEntityConf = $this->registry->findEntityByClass($attr->relatedEntity);
            if ($relatedEntityConf) {
                foreach ($items as $childData) {
                    $childData[$idCol] = $id;
                    $childData[$typeCol] = $morphType;
                    $this->insert($relatedEntityConf['resource'], $childData);
                }
            }
        }

        return $this->findWithRelations($resource, $id, $includes);
    }

    public function update(string $resource, $id, array $data, array $includes = [])
    {
        $config = $this->resolveConfig($resource);

        if ($this->isTransactional($config)) {
            return DB::transaction(function () use ($resource, $id, $data, $includes, $config) {
                return $this->doUpdate($resource, $id, $data, $includes, $config);
            });
        }

        return $this->doUpdate($resource, $id, $data, $includes, $config);
    }

    protected function doUpdate(string $resource, $id, array $data, array $includes, array $config): array
    {
        $userIdentifier = request()->header('X-User-ID') ?? 'System';

        if ($config['auditable']) {
            $data['updated_by'] = $userIdentifier;
        }

        $data['updated_at'] = now()->toDateTimeString();

        $manyToManyData = [];
        foreach ($config['manyToMany'] ?? [] as $relName => $relAttr) {
            if (array_key_exists($relName, $data)) {
                $manyToManyData[$relName] = ['attr' => $relAttr, 'ids' => $data[$relName]];
                unset($data[$relName]);
            }
        }

        if (count($data) > 0) {
            DB::table($config['table'])->where($config['primaryKey'], $id)->update($data);
        }

        foreach ($manyToManyData as $relName => $relInfo) {
            $this->syncManyToMany($config, $id, $relInfo['attr'], $relInfo['ids']);
        }

        return $this->findWithRelations($resource, $id, $includes);
    }

    public function delete(string $resource, $id): bool
    {
        $config = $this->resolveConfig($resource);
        $query = DB::table($config['table'])->where($config['primaryKey'], $id);

        if ($config['softDelete']) {
            return $query->update(['deleted_at' => now()]) > 0;
        }

        // Auto-cleanup ManyToMany pivot entries to maintain integrity
        foreach ($config['manyToMany'] ?? [] as $relName => $relAttr) {
            $relatedConf = $this->registry->findEntityByClass($relAttr->relatedEntity);
            if ($relatedConf) {
                $pivotTable = $relAttr->pivotTable ?: (Str::singular(min($config['table'], $relatedConf['table'])).'_'.Str::singular(max($config['table'], $relatedConf['table'])));
                $fk1 = $relAttr->foreignPivotKey ?: Str::singular($config['table']).'_id';
                DB::table($pivotTable)->where($fk1, $id)->delete();
            }
        }

        return $query->delete() > 0;
    }

    protected function syncManyToMany(array $config, $parentId, $relAttr, $relatedIds): void
    {
        $relatedConf = $this->registry->findEntityByClass($relAttr->relatedEntity);
        if (! $relatedConf) {
            return;
        }

        $pivotTable = $relAttr->pivotTable ?: (Str::singular(min($config['table'], $relatedConf['table'])).'_'.Str::singular(max($config['table'], $relatedConf['table'])));
        $fk1 = $relAttr->foreignPivotKey ?: Str::singular($config['table']).'_id';
        $fk2 = $relAttr->relatedPivotKey ?: Str::singular($relatedConf['table']).'_id';

        $relatedIds = is_array($relatedIds) ? $relatedIds : [];

        // Truncate existing mappings for this parent element
        DB::table($pivotTable)->where($fk1, $parentId)->delete();

        if (empty($relatedIds)) {
            return;
        }

        $inserts = [];
        foreach (array_unique($relatedIds) as $rId) {
            $inserts[] = [
                $fk1 => $parentId,
                $fk2 => $rId,
            ];
        }

        DB::table($pivotTable)->insert($inserts);
    }
}
