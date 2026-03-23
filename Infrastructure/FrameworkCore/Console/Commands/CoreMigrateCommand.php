<?php

namespace Infrastructure\FrameworkCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Illuminate\Support\Facades\DB;

/**
 * Smart Schema Migration with:
 * - History tracking via 'boundly_migrations' table (idempotent runs)
 * - Dry-Run mode: shows SQL without executing anything
 * - Non-destructive: NEVER drops columns or tables automatically
 * - Full audit and soft-delete column injection
 */
class CoreMigrateCommand extends Command
{
    protected $signature   = 'core:migrate {--lang=en} {--dry-run : Show what would change without applying it}';
    protected $description = 'Scans Domain entities and synchronizes the database schema safely.';

    public function __construct(protected EntityRegistry $registry)
    {
        parent::__construct();
    }

    public function handle()
    {
        app()->setLocale($this->option('lang'));

        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE — No changes will be applied to the database.');
        }

        $this->info(__('core::messages.scanning_entities'));

        $entities = $this->registry->getAllEntities();

        if (empty($entities)) {
            $this->warn(__('core::messages.no_entities_found'));
            return;
        }

        // Ensure migration history table exists
        if (!$isDryRun) {
            $this->ensureMigrationTable();
        }

        Schema::disableForeignKeyConstraints();

        foreach ($entities as $resourceName => $config) {
            $tableName   = $config['table'];
            $fingerprint = $this->buildFingerprint($config);

            // Check history: skip if nothing changed
            if (!$isDryRun && $this->hasAlreadyMigrated($tableName, $fingerprint)) {
                $this->line("  <fg=gray>[SKIP]</> {$tableName} — no changes detected.");
                continue;
            }

            if (!Schema::hasTable($tableName)) {
                $this->processNewTable($tableName, $config, $isDryRun);
            } else {
                $this->processExistingTable($tableName, $config, $isDryRun);
            }

            // Record migration in history
            if (!$isDryRun) {
                $this->recordMigration($tableName, $fingerprint);
            }
        }

        // Process Many-To-Many Pivot Tables
        $this->processPivotTables($entities, $isDryRun);

        Schema::enableForeignKeyConstraints();

        if ($isDryRun) {
            $this->warn('🔍 Dry run complete. No changes were made.');
        } else {
            $this->info(__('core::messages.magic_migration_done'));
        }
    }

    protected function processNewTable(string $tableName, array $config, bool $isDryRun): void
    {
        $this->info("  <fg=green>[CREATE]</> {$tableName} (from {$config['class']})");

        if ($isDryRun) {
            foreach ($config['columns'] as $colName => $colAttr) {
                $this->line("    + {$colName} ({$colAttr->type})");
            }
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($config) {
            $table->id($config['primaryKey']);

            foreach ($config['columns'] as $colName => $colAttr) {
                if ($colName === $config['primaryKey']) continue;
                $this->addColumn($table, $colName, $colAttr);
            }

            foreach ($config['belongsTo'] as $relName => $relAttr) {
                $foreignCol = $relAttr->foreignKey ?: $relName . '_id';
                if (!isset($config['columns'][$foreignCol])) {
                    $table->foreignId($foreignCol)->nullable();
                }
            }

            if ($config['auditable']) {
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
            }
            if ($config['softDelete']) {
                $table->softDeletes();
            }

            $table->timestamps();
        });
    }

    protected function processExistingTable(string $tableName, array $config, bool $isDryRun): void
    {
        $currentColumns = collect(Schema::getColumnListing($tableName))->flip();

        $changes = [];

        // Detect new columns
        foreach ($config['columns'] as $colName => $colAttr) {
            if ($colName === $config['primaryKey']) continue;
            if (!isset($currentColumns[$colName])) {
                $changes[] = ['op' => 'ADD', 'col' => $colName, 'attr' => $colAttr];
            } else {
                $changes[] = ['op' => 'CHANGE', 'col' => $colName, 'attr' => $colAttr];
            }
        }

        // Detect missing audit/soft-delete columns
        if ($config['auditable'] && !isset($currentColumns['created_by'])) {
            $changes[] = ['op' => 'ADD_AUDIT', 'col' => 'created_by'];
        }
        if ($config['auditable'] && !isset($currentColumns['updated_by'])) {
            $changes[] = ['op' => 'ADD_AUDIT', 'col' => 'updated_by'];
        }
        if ($config['softDelete'] && !isset($currentColumns['deleted_at'])) {
            $changes[] = ['op' => 'ADD_SOFT_DELETE'];
        }

        foreach ($config['belongsTo'] as $relName => $relAttr) {
            $foreignCol = $relAttr->foreignKey ?: $relName . '_id';
            if (!isset($currentColumns[$foreignCol])) {
                $changes[] = ['op' => 'ADD_FK', 'col' => $foreignCol];
            }
        }

        if (empty($changes)) {
            return;
        }

        foreach ($changes as $change) {
            $marker = $change['op'] === 'CHANGE' ? '<fg=yellow>[CHANGE]</>' : '<fg=cyan>[ADD]</>';
            $this->line("  {$marker} {$tableName}.{$change['col']}  ({$change['op']})");
        }

        if ($isDryRun) return;

        Schema::table($tableName, function (Blueprint $table) use ($config, $currentColumns, $changes) {
            foreach ($changes as $change) {
                match ($change['op']) {
                    'ADD'          => $this->addColumn($table, $change['col'], $change['attr']),
                    'CHANGE'       => $this->addColumn($table, $change['col'], $change['attr'], change: true),
                    'ADD_AUDIT'    => $table->string($change['col'])->nullable(),
                    'ADD_SOFT_DELETE' => $table->softDeletes(),
                    'ADD_FK'       => $table->foreignId($change['col'])->nullable(),
                    default        => null,
                };
            }
        });
    }

    protected function processPivotTables(array $entities, bool $isDryRun): void
    {
        $processedPivots = [];

        foreach ($entities as $config) {
            foreach ($config['manyToMany'] as $relName => $relAttr) {
                $relatedConf = $this->registry->findEntityByClass($relAttr->relatedEntity);
                if (!$relatedConf) continue;

                $pivotTable = $relAttr->pivotTable;
                if (!$pivotTable) {
                    $names = [$config['table'], $relatedConf['table']];
                    sort($names);
                    $pivotTable = \Illuminate\Support\Str::singular($names[0]) . '_' . \Illuminate\Support\Str::singular($names[1]);
                }

                if (in_array($pivotTable, $processedPivots)) continue;
                $processedPivots[] = $pivotTable;

                if (!Schema::hasTable($pivotTable)) {
                    $this->info("  <fg=green>[CREATE PIVOT]</> {$pivotTable} (for {$config['class']} <-> {$relatedConf['class']})");
                    if (!$isDryRun) {
                        $fk1 = $relAttr->foreignPivotKey ?: \Illuminate\Support\Str::singular($config['table']) . '_id';
                        $fk2 = $relAttr->relatedPivotKey ?: \Illuminate\Support\Str::singular($relatedConf['table']) . '_id';
                        
                        Schema::create($pivotTable, function (Blueprint $table) use ($fk1, $fk2) {
                            $table->foreignId($fk1);
                            $table->foreignId($fk2);
                            $table->primary([$fk1, $fk2]);
                        });
                    }
                }
            }
        }
    }

    protected function addColumn(Blueprint $table, string $colName, $colAttr, bool $change = false): void
    {
        $type     = $colAttr->type ?? 'string';
        $length   = $colAttr->length;
        $nullable = $colAttr->nullable ?? false;
        $default  = $colAttr->default;

        $column = $table->$type($colName, $length);
        if ($nullable)          $column->nullable();
        if ($default !== null)  $column->default($default);
        if ($change)            $column->change();
    }

    // -------------------------------------------------------------------------
    // Migration History
    // -------------------------------------------------------------------------

    protected function ensureMigrationTable(): void
    {
        if (!Schema::hasTable('boundly_migrations')) {
            Schema::create('boundly_migrations', function (Blueprint $table) {
                $table->id();
                $table->string('table_name')->index();
                $table->string('fingerprint');
                $table->timestamp('applied_at')->useCurrent();
            });
            $this->line('  <fg=gray>[INIT]</> Created boundly_migrations tracking table.');
        }
    }

    protected function hasAlreadyMigrated(string $tableName, string $fingerprint): bool
    {
        return DB::table('boundly_migrations')
            ->where('table_name', $tableName)
            ->where('fingerprint', $fingerprint)
            ->exists();
    }

    protected function recordMigration(string $tableName, string $fingerprint): void
    {
        DB::table('boundly_migrations')->updateOrInsert(
            ['table_name' => $tableName],
            ['fingerprint' => $fingerprint, 'applied_at' => now()]
        );
    }

    /**
     * Generates a hash of the entity's schema config.
     * If this doesn't change between runs, the table is considered up-to-date.
     */
    protected function buildFingerprint(array $config): string
    {
        $data = [
            'columns'    => array_map(fn($c) => (array) $c, $config['columns']),
            'belongsTo'  => array_map(fn($r) => (array) $r, $config['belongsTo']),
            'hasMany'    => array_map(fn($r) => (array) $r, $config['hasMany']),
            'hasOne'     => array_map(fn($r) => (array) $r, $config['hasOne']),
            'manyToMany' => array_map(fn($r) => (array) $r, $config['manyToMany'] ?? []),
            'auditable'  => $config['auditable'],
            'softDelete' => $config['softDelete'],
        ];
        return md5(serialize($data));
    }
}
