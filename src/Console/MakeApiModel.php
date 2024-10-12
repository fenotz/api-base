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

    protected $filesystem;


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
        $name = $this->argument('name');

        // Crear el modelo con migración
        Artisan::call('make:model', ['name' => "{$name}", '--migration' => true]);

        // Crear StoreRequest
        Artisan::call('make:request', ['name' => "{$name}/Store{$name}Request"]);

        // Crear UpdateRequest
        Artisan::call('make:request', ['name' => "{$name}/Update{$name}Request"]);

        // Crear el controlador extendiendo el BaseApiController
        $controllerPath = app_path("Http/Controllers/{$name}/{$name}Controller.php");
        $this->createController($name, $controllerPath);

        // Crear policy
        Artisan::call('make:policy', ['name' => "{$name}Policy", '--model' => "{$name}"]);

        // Crear seeder
        Artisan::call('make:seeder', ['name' => "{$name}Seeder"]);

        // Crear factory
        Artisan::call('make:factory', ['name' => "{$name}Factory", '--model' => "{$name}"]);

        // Crear test
        Artisan::call('make:test', ['name' => "{$name}Test"]);

        // Llamar a install:api
        Artisan::call('install:api');

        $this->info("Modelo, Requests, Policy, Seeder, Factory, Test y Controlador de {$name} creados con éxito.");
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
