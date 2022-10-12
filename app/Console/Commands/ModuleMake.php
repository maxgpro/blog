<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;

class ModuleMake extends Command
{
    private $files;

    /**
     * ModuleMake constructor.
     * @param $files
     */
    public function __construct(Filesystem $filesystem) {
        parent::__construct();
        $this->files = $filesystem;
    }

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'make:module {name} {--all} {--api} {--controller} {--model} {--migration} {--view}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create modular files.';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle()
    {
        if ($this->option('all')) {
            $this->input->setOption('api', true);
            $this->input->setOption('controller', true);
            $this->input->setOption('model', true);
            $this->input->setOption('migration', true);
            $this->input->setOption('view', true);
        }

        if ($this->option('api')) {
            $this->createApiController();
        }

        if ($this->option('controller')) {
            $this->createController();
        }

        if ($this->option('model')) {
            $this->createModel();
        }

        if ($this->option('migration')) {
            $this->createMigration();
        }

        if ($this->option('view')) {
            $this->createView();
        }
    }

    private function createController()
    {
        $controller = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->getControllerPath($this->argument('name'));

        if ($this->alreadyExists($path)) {
            $this->error('Controller already exists!');
        } else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/controller.model.api.stub'));

            $stub = str_replace(
                [
                    'DummyNamespace',
                    'DummyRootNamespace',
                    'DummyClass',
                    'DummyFullModelClass',
                    'DummyModelClass',
                    'DummyModelVariable',
                ],
                [
                    "App\\Modules\\".trim($this->argument('name'))."\\Controllers",
                    $this->laravel->getNamespace(),
                    $controller.'Controller',
                    "App\\Modules\\".trim($this->argument('name'))."\\Models\\{$modelName}",
                    $modelName,
                    lcfirst(($modelName))
                ],
                $stub
            );
            $this->files->put($path, $stub);
            $this->info('Controller created successfully.');
        }
        $this->updateModularConfig();
        $this->createRoutes($controller, $modelName);
    }

    private function createApiController()
    {
        $controller = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->getApiControllerPath($this->argument('name'));

        if ($this->alreadyExists($path)) {
            $this->error('API Controller already exists!');
        } else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/controller.model.api.stub'));

            $stub = str_replace(
                [
                    'DummyNamespace',
                    'DummyRootNamespace',
                    'DummyClass',
                    'DummyFullModelClass',
                    'DummyModelClass',
                    'DummyModelVariable',
                ],
                [
                    "App\\Modules\\".trim($this->argument('name'))."\\Controllers\\Api",
                    $this->laravel->getNamespace(),
                    $controller.'Controller',
                    "App\\Modules\\".trim($this->argument('name'))."\\Models\\{$modelName}",
                    $modelName,
                    lcfirst(($modelName))
                ],
                $stub
            );
            $this->files->put($path, $stub);
            $this->info('API Controller created successfully.');
        }
        $this->updateModularConfig();
        $this->createApiRoutes($controller, $modelName);
    }

    private function createModel()
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $this->call('make:model',[
            'name' => "App\\Modules\\".trim($this->argument('name'))."\\Models\\".$model
        ]);
    }

    private function createMigration()
    {
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));

        try {
            $this->call('make:migration', [
                'name'      => "create_{$table}_table",
                '--create'  => $table,
                '--path'    => "App\\Modules\\".trim($this->argument('name'))."\\Migrations"
            ]);
        }
        catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }



    private function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    private function getControllerPath($name): string
    {
        $controller = Str::singular(Str::studly(class_basename($name)));
        return $this->laravel['path'].'/Modules/'.str_replace('\\', '/', $name)."/Controllers/"."{$controller}Controller.php";

    }

    private function getApiControllerPath($name): string
    {
        $controller = Str::singular(Str::studly(class_basename($name)));
        return $this->laravel['path'].'/Modules/'.str_replace('\\', '/', $name)."/Controllers/Api/"."{$controller}Controller.php";

    }


    private function createRoutes(String $controller, String $modelName) : void
    {
        $routePath = $this->getRoutesPath($this->argument('name'));

        if ($this->alreadyExists($routePath)) {
            $this->error('Routes already exists!');
        } else {
            $this->makeDirectory($routePath);
            $stub = $this->files->get(base_path('resources/stubs/routes.web.stub'));
            $stub = str_replace(
                [
                    'DummyClass',
                    'DummyRoutePrefix',
                    'DummyModelVariable',
                ],
                [
                    $controller.'Controller',
                    Str::plural(Str::snake(lcfirst($modelName), '-')),
                    lcfirst($modelName)
                ],
                $stub
            );

            $this->files->put($routePath, $stub);
            $this->info('Routes created successfully.');
        }
    }

    private function createApiRoutes(String $controller, String $modelName) : void
    {
        $routePath = $this->getApiRoutesPath($this->argument('name'));

        if ($this->alreadyExists($routePath)) {
            $this->error('Routes already exists!');
        } else {
            $this->makeDirectory($routePath);
            $stub = $this->files->get(base_path('resources/stubs/routes.api.stub'));
            $stub = str_replace(
                [
                    'DummyClass',
                    'DummyRoutePrefix',
                    'DummyModelVariable',
                ],
                [
                    'Api\\'.$controller.'Controller',
                    Str::plural(Str::snake(lcfirst($modelName), '-')),
                    lcfirst($modelName)
                ],
                $stub
            );

            $this->files->put($routePath, $stub);
            $this->info('API Routes created successfully.');
        }
    }


    private function getRoutesPath($name) : string
    {
        return $this->laravel['path'].'/Modules/'.str_replace('\\', '/', $name)."/Routes/web.php";
    }

    private function getApiRoutesPath($name) : string
    {
        return $this->laravel['path'].'/Modules/'.str_replace('\\', '/', $name)."/Routes/api.php";
    }


    #[Pure] protected function alreadyExists($path) : bool
    {
        return $this->files->exists($path);
    }


    private function createView()
    {
        $paths = $this->getViewPath($this->argument('name'));

        foreach ($paths as $path) {
            $view = Str::studly(class_basename($this->argument('name')));

            if ($this->alreadyExists($path)) {
                $this->error('View already exists!');
            } else {
                $this->makeDirectory($path);
                $stub = $this->files->get(base_path('resources/stubs/view.stub'));
                $stub = str_replace(
                    [
                        '',
                    ],
                    [
                    ],
                    $stub
                );

                $this->files->put($path, $stub);
                $this->info('View created successfully.');
            }
        }
    }

    protected function getViewPath($name) : object
    {
        $arrFiles = collect([
            'create',
            'edit',
            'index',
            'show',
        ]);

        //str_replace('\\', '/', $name)
        $paths = $arrFiles->map(function($item) use ($name){
//            return base_path('resources/views/'.str_replace('\\', '/', $name).'/'.$item.".blade.php");
            return $this->laravel['path'].'/Modules/'.str_replace('\\', '/', $name).'/Views/'.$item.".blade.php";
        });

        return $paths;
    }



    private function updateModularConfig() {
        $group = explode('\\', $this->argument('name'))[0];
        $module = Str::studly(class_basename($this->argument('name')));
        $modular = $this->files->get(base_path('config/modular.php'));
        $matches = [];
        preg_match("/'modules' => \[.*?'{$group}' => \[(.*?)\]/s", $modular, $matches);

        if(count($matches) == 2) {
            if(!preg_match("/'{$module}'/", $matches[1])) {
                $parts = preg_split("/('modules' => \[.*?'{$group}' => \[)/s", $modular, 2, PREG_SPLIT_DELIM_CAPTURE);
                if(count($parts) == 3) {
                    $configStr = $parts[0].$parts[1]."\n            '$module',".$parts[2];
                    $this->files->put(base_path('config/modular.php'), $configStr);
                }
            }
        }
    }
}
