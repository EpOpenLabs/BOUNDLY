<?php

namespace Infrastructure\FrameworkCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CoreMakeEntityCommand extends Command
{
    protected $signature = 'core:make:entity {name : The name of the Entity (Singular, e.g. Product)}
                                            {--a|auditable : Add the Auditable trait}
                                            {--s|soft-delete : Add the SoftDelete trait}';
    
    protected $description = 'Scaffolds a new Pure DDD Entity with BOUNDLY configuration';

    public function handle()
    {
        $name = trim($this->argument('name'));
        
        // Intelligent naming conventions
        $singularName = Str::studly(Str::singular($name));
        $pluralName   = Str::studly(Str::plural($name));
        $tableName    = Str::snake($pluralName);
        $resourceName = Str::snake($pluralName, '-');

        $namespace = "Domain\\{$pluralName}\\Entities";
        $directory = base_path("Domain/{$pluralName}/Entities");
        $filePath  = $directory . DIRECTORY_SEPARATOR . $singularName . '.php';

        if (file_exists($filePath)) {
            $this->error("Entity '{$singularName}' already exists at {$filePath}");
            return;
        }

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $stub = $this->getStub();

        // Calculate Traits and Attributes (Auditable, SoftDelete)
        $useStatements = [];
        $classAttributes = [];

        if ($this->option('auditable')) {
            $useStatements[]   = "use Infrastructure\FrameworkCore\Attributes\Behavior\Auditable;";
            $classAttributes[] = "#[Auditable]";
        }

        if ($this->option('soft-delete')) {
            $useStatements[]   = "use Infrastructure\FrameworkCore\Attributes\Behavior\SoftDelete;";
            $classAttributes[] = "#[SoftDelete]";
        }

        $useString  = empty($useStatements) ? "" : implode("\n", $useStatements) . "\n";
        $attrString = empty($classAttributes) ? "" : implode("\n", $classAttributes) . "\n";

        $content = str_replace(
            ['{{NAMESPACE}}', '{{CLASS}}', '{{TABLE}}', '{{RESOURCE}}', '{{USE_TRAITS}}', '{{CLASS_ATTRIBUTES}}'],
            [$namespace, $singularName, $tableName, $resourceName, $useString, $attrString],
            $stub
        );

        file_put_contents($filePath, ltrim($content));
        
        $this->info("✨ DDD Entity scaffolded successfully.");
        $this->line("  <fg=green>[CREATED]</> " . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath));
        $this->line("\n  <fg=gray>Run</> <fg=yellow>php artisan core:watch</> <fg=gray>and add properties to magically evolve your database.</>");
    }

    protected function getStub(): string
    {
        return <<<'PHP'
<?php

namespace {{NAMESPACE}};

use Infrastructure\FrameworkCore\Attributes\Schema\Entity;
use Infrastructure\FrameworkCore\Attributes\Schema\Id;
use Infrastructure\FrameworkCore\Attributes\Schema\Column;
use Domain\Shared\Entities\AggregateRoot;
{{USE_TRAITS}}
/**
 * Auto-generated Pure Domain Entity for {{CLASS}}.
 * Add properties with #[Column] attributes to evolve the schema.
 */
#[Entity(table: '{{TABLE}}', resource: '{{RESOURCE}}')]
{{CLASS_ATTRIBUTES}}class {{CLASS}}
{
    use AggregateRoot;

    #[Id]
    private int $id;

    #[Column(type: 'string', length: 150)]
    private string $name;

    // TODO: Add more properties here...

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
}

PHP;
    }
}
