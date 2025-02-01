<?php

namespace LaravelPredatorApiUtils\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceAndRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service-repository 
                            {name : The name of the model, service, and repository} 
                            {--m|migration : Create a migration for the model} 
                            {--c|controller : Create a controller for the model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a model, service, repository, and optionally a migration and controller';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Create the model
        $modelArgs = ['name' => $name];
        if ($this->option('migration')) {
            $modelArgs['--migration'] = true;
        }
        $this->call('make:model', $modelArgs);

        // Optionally create the controller
        if ($this->option('controller')) {
            $controllerName = "{$name}Controller";
            $controllerPath = app_path("Http/Controllers/{$controllerName}.php"); // Ensure correct directory
            $controllerContent = $this->getControllerTemplate($name);

            File::ensureDirectoryExists(dirname($controllerPath)); // Ensure the directory exists
            File::put($controllerPath, $controllerContent); // Write the controller file

            $this->info("Controller created: {$controllerName}");
        }


        // Create the service
        $servicePath = app_path("Services/{$name}Service.php");
        $controllerContent = $this->getServiceTemplate($name);
        File::ensureDirectoryExists(dirname($servicePath));
        File::put($servicePath, $controllerContent);
        $this->info("Service created: {$servicePath}");

        // Create the repository
        $repositoryPath = app_path("Repositories/{$name}Repository.php");
        $repositoryContent = $this->getRepositoryTemplate($name);
        File::ensureDirectoryExists(dirname($repositoryPath));
        File::put($repositoryPath, $repositoryContent);
        $this->info("Repository created: {$repositoryPath}");
    }

    private function getServiceTemplate($name)
    {
        $repositoryName = "{$name}Repository";
        $lowerRepositoryName = lcfirst($repositoryName);

        return <<<EOT
        <?php

        namespace App\Services;

        use App\Repositories\\{$repositoryName};

        class {$name}Service
        {
            protected {$repositoryName} \${$lowerRepositoryName};

            /**
             * Create a new class instance.
             */
            public function __construct({$repositoryName} \${$lowerRepositoryName})
            {
                \$this->{$lowerRepositoryName} = \${$lowerRepositoryName};
            }
        }
        EOT;
    }


    private function getRepositoryTemplate($name)
    {
        $lowerName = strtolower($name);
        return <<<EOT
        <?php

        namespace App\Repositories;

        use App\Models\\{$name};
        use LaravelPredatorApiUtils\Repositories\BaseRepository;

        class {$name}Repository extends BaseRepository
        {
            public function __construct({$name} \${$lowerName})
            {
                parent::__construct(\${$lowerName});
            }
        }
        EOT;
    }

    private function getControllerTemplate($name)
    {
        $serviceName = "{$name}Service";
        $lowerServiceName = lcfirst($serviceName);

        return <<<EOT
    <?php

    namespace App\Http\Controllers;

    use App\Services\\{$serviceName};
    use Illuminate\Http\Request;

    class {$name}Controller extends Controller
    {
        protected {$serviceName} \${$lowerServiceName};

        /**
         * Create a new controller instance.
         */
        public function __construct({$serviceName} \${$lowerServiceName})
        {
            \$this->{$lowerServiceName} = \${$lowerServiceName};
        }

        /**
         * Example method for listing resources.
         */
        public function index()
        {
            // Use the service for logic
        }

        /**
         * Example method for showing a single resource.
         */
        public function show(\$id)
        {
            // Use the service for logic
        }
    }
    EOT;
    }
}
