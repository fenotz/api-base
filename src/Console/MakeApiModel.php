<?php

namespace Fenox\ApiBase\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class MakeApiModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:apimodel {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un modelo, migración, requests, controlador, policy, seeder, test y factory dentro de la carpeta del modelo';

    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $name = $this->argument('name');

            // Generar el modelo con migración
            $this->info("Generating model and migration for {$name}...");
            Artisan::call('make:model', ['name' => "{$name}", '--migration' => true]);
            $this->info("Model and migration for {$name} created successfully.");

            // Crear StoreRequest
            $this->info("Generating StoreRequest for {$name}...");
            Artisan::call('make:request', ['name' => "FenoxApiRequests/{$name}/Store{$name}Request"]);
            $this->info("StoreRequest for {$name} created successfully.");

            // Crear UpdateRequest
            $this->info("Generating UpdateRequest for {$name}...");
            Artisan::call('make:request', ['name' => "FenoxApiRequests/{$name}/Update{$name}Request"]);
            $this->info("UpdateRequest for {$name} created successfully.");

            // Crear el controlador extendiendo el BaseApiController
            $controllerPath = app_path("Http/Controllers/FenoxApiControllers/{$name}Controller.php");
            $this->info("Generating controller for {$name}...");
            $this->createController($name, $controllerPath);
            $this->info("Controller for {$name} created successfully.");

            // Crear policy
            $this->info("Generating policy for {$name}...");
            Artisan::call('make:policy', ['name' => "{$name}Policy", '--model' => "{$name}"]);
            $this->info("Policy for {$name} created successfully.");

            // Crear seeder
            $this->info("Generating seeder for {$name}...");
            Artisan::call('make:seeder', ['name' => "{$name}Seeder"]);
            $this->info("Seeder for {$name} created successfully.");

            // Crear factory
            $this->info("Generating factory for {$name}...");
            Artisan::call('make:factory', ['name' => "{$name}Factory", '--model' => "{$name}"]);
            $this->info("Factory for {$name} created successfully.");

            // Crear test
            $this->info("Generating test for {$name}...");
            Artisan::call('make:test', ['name' => "{$name}Test"]);
            $this->info("Test for {$name} created successfully.");

            $this->info("All components for {$name} have been created successfully.");
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * @param $name
     * @param $controllerPath
     * @return void
     */
    protected function createController($name, $controllerPath): void
    {
        // Crear la carpeta si no existe
        $directory = dirname($controllerPath);
        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);
        }

        // Contenido del controlador
        $controllerContent = "<?php

namespace App\Http\Controllers\\{$name};

use App\Http\Requests\\{$name}\\Store{$name}Request;
use App\Http\Requests\\{$name}\\Update{$name}Request;
use App\Models\\{$name};
use Fenox\ApiBase\Controllers\BaseApiController;

class {$name}Controller extends BaseApiController
{
    protected \$model = {$name}::class;
    protected string \$sortBy = 'name';
    protected int \$paginate = 10; // set to 0 if you don't want paginate
    protected \$storeRequest = Store{$name}Request::class;
    protected \$updateRequest = Update{$name}Request::class;
}
";

        // Crear el archivo controlador
        $this->filesystem->put($controllerPath, $controllerContent);
    }
}
