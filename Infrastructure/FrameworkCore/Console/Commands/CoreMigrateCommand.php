<?php

namespace Infrastructure\FrameworkCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Illuminate\Support\Facades\DB;

class CoreMigrateCommand extends Command
{
    protected $signature = 'core:migrate {--lang=en}';
    protected $description = 'Scans Domain entities and synchronizes the database schema.';

    public function __construct(protected EntityRegistry $registry)
    {
        parent::__construct();
    }

    public function handle()
    {
        // Set Local Language
        app()->setLocale($this->option('lang'));

        $this->info(__('core::messages.scanning_entities'));

        $entities = $this->registry->getAllEntities();

        if (empty($entities)) {
            $this->warn(__('core::messages.no_entities_found'));
            return;
        }

        // Disable FK checks to allow schema transformations without blocking
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($entities as $resourceName => $config) {
            $tableName = $config['table'];

            if (!Schema::hasTable($tableName)) {
                $this->info(__('core::messages.creating_magic_table', ['table' => $tableName, 'class' => $config['class']]));

                Schema::create($tableName, function (Blueprint $table) use ($config) {
                    $table->id($config['primaryKey']);

                    foreach ($config['columns'] as $colName => $colAttr) {
                        if ($colName === $config['primaryKey']) {
                            continue;
                        }

                        $type = $colAttr->type ?? 'string';
                        $length = $colAttr->length;
                        $nullable = $colAttr->nullable ?? false;
                        $default = $colAttr->default;

                        $column = $table->$type($colName, $length);
                        
                        if ($nullable) $column->nullable();
                        if ($default !== null) $column->default($default);
                    }

                    // Relationships Injection
                    foreach ($config['belongsTo'] as $relName => $relAttr) {
                        $foreignCol = $relAttr->foreignKey ?: $relName . '_id';
                        if (!isset($config['columns'][$foreignCol])) {
                            $table->foreignId($foreignCol)->nullable();
                        }
                    }

                    // Audit Traits Injection
                    if ($config['auditable']) {
                        $table->string('created_by')->nullable();
                        $table->string('updated_by')->nullable();
                    }
                    if ($config['softDelete']) {
                        $table->softDeletes();
                    }
                    
                    $table->timestamps();
                });
            } else {
                // Smart Evolution Engine: Only touch what changed
                $currentColumns = collect(DB::select("SHOW COLUMNS FROM {$tableName}"))
                    ->keyBy('Field');

                Schema::table($tableName, function (Blueprint $table) use ($config, $tableName, $currentColumns) {
                    // Check Audit Columns in Evolution
                    if ($config['auditable'] && !isset($currentColumns['created_by'])) {
                        $table->string('created_by')->nullable();
                        $this->info(" [+] Discovery: Added audit column created_by -> {$tableName}");
                    }
                    if ($config['auditable'] && !isset($currentColumns['updated_by'])) {
                        $table->string('updated_by')->nullable();
                        $this->info(" [+] Discovery: Added audit column updated_by -> {$tableName}");
                    }
                    if ($config['softDelete'] && !isset($currentColumns['deleted_at'])) {
                        $table->softDeletes();
                        $this->info(" [+] Discovery: Added soft-delete column -> {$tableName}");
                    }

                    foreach ($config['columns'] as $colName => $colAttr) {
                        if ($colName === $config['primaryKey']) {
                            continue;
                        }

                        $type = $colAttr->type ?? 'string';
                        $length = $colAttr->length;
                        $nullable = $colAttr->nullable ?? false;
                        $default = $colAttr->default;

                        // If column DOES NOT exist, create it. If it exists, EVOLVE it.
                        if (!isset($currentColumns[$colName])) {
                            $column = $table->$type($colName, $length);
                            if ($nullable) $column->nullable();
                            if ($default !== null) $column->default($default);
                            $this->info(" [+] Discovery: Added new column {$colName} -> {$tableName}");
                        } else {
                            // Column exists, evolve its metadata
                            $column = $table->$type($colName, $length)->change();
                            if ($nullable) $column->nullable();
                            if ($default !== null) $column->default($default);
                            
                            // Log only if actual changes are perceived (simplified check)
                            // $this->info(" [*] Evolution: Updated column {$colName} in {$tableName}");
                        }
                    }

                    // Relations Discovery in Evolution
                    foreach ($config['belongsTo'] as $relName => $relAttr) {
                        $foreignCol = $relAttr->foreignKey ?: $relName . '_id';
                        if (!isset($currentColumns[$foreignCol])) {
                            $table->foreignId($foreignCol)->nullable();
                            $this->info(" [R] Relation Discovery: Added FK {$foreignCol} -> {$tableName}");
                        }
                    }
                });
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->info(__('core::messages.magic_migration_done'));
    }
}
