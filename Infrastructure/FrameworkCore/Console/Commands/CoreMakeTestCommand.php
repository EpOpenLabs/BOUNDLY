<?php

namespace Infrastructure\FrameworkCore\Console\Commands;

use Illuminate\Console\Command;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;

class CoreMakeTestCommand extends Command
{
    protected $signature = 'core:make:test {resource? : The resource or class name}';
    protected $description = 'Generates an automated API test stub for a Domain Entity or Application Action';
    protected $registry;
    protected $actionRegistry;

    public function __construct(EntityRegistry $registry, \Infrastructure\FrameworkCore\Registry\ActionRegistry $actionRegistry)
    {
        parent::__construct();
        $this->registry = $registry;
        $this->actionRegistry = $actionRegistry;
    }
    
    // public function __construct(protected EntityRegistry $registry)
    // {
    //     parent::__construct();
    // }

    public function handle()
    {
        $resource = $this->argument('resource');

        if ($resource) {
            $config = $this->registry->getEntityConfig($resource);
            if (!$config) $config = $this->registry->findEntityByClass($resource);
            if (!$config) $config = $this->registry->findEntityByTable($resource);

            if (!$config) {
                $this->error("Entity for resource '{$resource}' not found!");
                return;
            }

            $this->generateTest($config, $resource);
        } else {
            $this->info("Scanning entities and generating tests...");
            $entities = $this->registry->getAllEntities();
            
            if (empty($entities)) {
                $this->warn("No entities found to test.");
                return;
            }

            foreach ($entities as $resourceKey => $config) {
                $this->generateTest($config, $resourceKey);
            }
        }

        $this->info("\nDone! Run your tests using 'php artisan test'");
    }

    protected function generateTest(array $config, string $resourceName)
    {
        $classParts = explode('\\', ltrim($config['class'], '\\'));
        $className  = array_pop($classParts);
        
        // Remove "Entities" folder from namespace if it exists, to place Tests next to it
        if (end($classParts) === 'Entities') {
            array_pop($classParts);
        }
        
        $baseNamespace = implode('\\', $classParts);
        $testNamespace = $baseNamespace . '\\Tests';
        $testClassName = $className . 'ApiTest';

        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $testNamespace);
        $directory    = base_path($relativePath);
        $filePath     = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $testClassName . '.php';

        if (file_exists($filePath)) {
            $this->line("  <fg=yellow>[SKIP]</> {$testClassName} already exists.");
            return;
        }

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $stub = $this->getStub();
        
        // Generate payload dynamically based on Column types
        $payloadArr = [];
        foreach ($config['columns'] as $colName => $colAttr) {
            if ($colName === $config['primaryKey']) continue;
            
            $val = match(true) {
                $colName === 'email' => "'test@example.com'",
                $colName === 'password' => "'password'",
                in_array(strtolower((string)$colAttr->type), ['integer', 'biginteger']) => 123,
                strtolower((string)$colAttr->type) === 'boolean' => 'true',
                strtolower((string)$colAttr->type) === 'date' => "'2026-01-01'",
                in_array(strtolower((string)$colAttr->type), ['datetime', 'timestamp']) => "'2026-01-01 10:00:00'",
                in_array(strtolower((string)$colAttr->type), ['decimal', 'float']) => '10.50',
                default => "'Test String'"
            };
            $payloadArr[] = "            '{$colName}' => {$val},";
        }

        $payloadStr = implode("\n", $payloadArr);
        $endpoint   = config('boundly.api_prefix', 'api') . '/' . $resourceName;
        
        // Generate custom Action tests
        $actionTests = "";
        $resourceActions = $this->actionRegistry->getActionsByResource($resourceName);
        foreach ($resourceActions as $method => $actionClass) {
            $actionParts = explode('\\', $actionClass);
            $actionName  = array_pop($actionParts);
            $actionTests .= "\n    /**\n     * Custom Action Test: {$actionName}\n     * (Mapped to {$method} /{$endpoint})\n     */\n    public function test_custom_action_{$actionName}()\n    {\n        // \$this->withoutMiddleware(\\Infrastructure\\FrameworkCore\\Http\\Middleware\\ResourceAuthorize::class);\n\n        \$payload = [\n{$payloadStr}\n        ];\n\n        \$response = \$this->" . strtolower($method) . "Json('/{$endpoint}', \$payload);\n        \n        // TODO: CUSTOM ASSERTIONS for this action!\n        \$response->assertStatus(" . ($method === 'POST' ? '201' : '200') . ");\n    }\n";
        }
        
        $content = str_replace(
            ['{{NAMESPACE}}', '{{CLASS}}', '{{ENDPOINT}}', '{{PAYLOAD}}', '{{TABLE}}', '{{ACTION_TESTS}}'],
            [$testNamespace, $testClassName, $endpoint, ltrim($payloadStr), $config['table'], $actionTests],
            $stub
        );

        file_put_contents($filePath, $content);
        $this->line("  <fg=green>[CREATED]</> {$testClassName} -> " . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath));
    }

    protected function getStub(): string
    {
        return <<<'PHP'
<?php

namespace {{NAMESPACE}};

use Infrastructure\FrameworkCore\Testing\BoundlyTestCase;

class {{CLASS}} extends BoundlyTestCase
{
    /**
     * Test mapping to GET /{{ENDPOINT}}
     */
    public function test_can_list_items()
    {
        // $this->withoutMiddleware(\Infrastructure\FrameworkCore\Http\Middleware\ResourceAuthorize::class);

        $response = $this->getJson('/{{ENDPOINT}}');
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    /**
     * Test mapping to POST /{{ENDPOINT}}
     */
    public function test_can_create_item()
    {
        // $this->withoutMiddleware(\Infrastructure\FrameworkCore\Http\Middleware\ResourceAuthorize::class);

        $payload = [
{{PAYLOAD}}
        ];

        $response = $this->postJson('/{{ENDPOINT}}', $payload);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('{{TABLE}}', $payload);
    }
{{ACTION_TESTS}}}
PHP;
    }
}
