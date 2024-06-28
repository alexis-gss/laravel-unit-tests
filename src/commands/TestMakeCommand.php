<?php

namespace LaravelUnitTests\commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class TestMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:laravel-unit-test
        {name : The name of the model}
        {--only= : Select only specific action(s)}
        {--force : Overload the existing version}';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'uwil:make:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new custom tests class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = "Targeted model";

    /**
     * List of actions writen in the tag 'only'.
     *
     * @var string
     */
    protected $actions = "";

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath("/../tests/Template/{$this->actions}-Test.php");
    }

    /**
     * Get actions from the tag 'only'.
     *
     * @return void
     */
    protected function getActionsFromTagOnly(): void
    {
        if ($this->hasOption('force') && $this->option('only') !== null) {
            $option = explode(",", $this->option('only'));
            sort($option);
            /**
             * I = Index
             * C = Create
             * R = Read
             * U = Update
             * D = Delete
             */
            $actions = [
                "I"     => ['index'],
                "C"     => ['create'],
                "R"     => ['read'],
                "U"     => ['update'],
                "D"     => ['delete'],
                "IR"    => ['index', 'read'],
                "RU"    => ['read', 'update'],
                "IRU"   => ['index', 'read', 'update'],
                "CRUD"  => ['create', 'delete', 'read', 'update'],
                "ICRUD" => ['create', 'delete', 'index', 'read', 'update']
            ];

            if (!array_search($option, $actions)) {
                $this->components->error("The 'only' option are invalid, please refer to the documentation.");
                exit();
            };
            $this->actions = array_search($option, $actions);
        } else {
            $this->actions = "ICRUD";
        } //end if
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath(string $stub): string
    {
        $customPath = $this->laravel->basePath(trim($stub, '/'));
        return file_exists($customPath) ? $customPath : __DIR__ . $stub;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @phpcs:disable Squiz.Commenting.FunctionComment.WrongStyle
     */
    // @phpstan-ignore-next-line
    public function handle(): void
    {
        // phpcs:enable
        $this->getActionsFromTagOnly();

        $this->validName();

        $name = $this->qualifyClass($this->getNameInput());

        $this->validModel($this->getNameInput());

        $path = $this->getPath($name);

        $this->validTest();

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        if (windows_os()) {
            $path = str_replace('/', '\\', $path);
        }

        $this->components->info(sprintf('Tests [%s] created successfully.', $path));
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     * @phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
     */
    protected function buildClass($name)
    {
        // phpcs:enable
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceModelSnakeCase($stub, $name)
            ->replaceModelCamelCase($stub, $name)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the model key-word on stub (plural0.
     *
     * @param string $stub
     * @param string $name
     * @return $this
     * @phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
     */
    protected function replaceModelSnakeCase(&$stub, $name)
    {
        // phpcs:enable
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $snakeCaseClass = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class));

        $stub = str_replace(['{{ snakeCaseClass }}', '{{snakeCaseClass}}'], $snakeCaseClass, $stub);

        return $this;
    }


    /**
     * Replace the model key-word on stub (plural0.
     *
     * @param string $stub
     * @param string $name
     * @return $this
     * @phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
     */
    protected function replaceModelCamelCase(&$stub, $name)
    {
        // phpcs:enable
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        $stub = str_replace(['{{ camelCaseClass }}', '{{camelCaseClass}}'], lcfirst($class), $stub);

        return $this;
    }

    /**
     * Valid the model name entered.
     *
     * @return void
     */
    protected function validName(): void
    {
        // First we need to ensure that the given name is not a reserved word within the PHP
        // language and that the class name will actually be valid. If it is not valid we
        // can error now and prevent from polluting the filesystem using invalid files.
        if ($this->isReservedName($this->getNameInput())) {
            $this->components->error('The name "' . $this->getNameInput() . '" is reserved by PHP.');

            exit();
        }
    }

    /**
     * Valid the model.
     *
     * @param string $name
     * @return void
     */
    protected function validModel(string $name): void
    {
        if (!in_array($name, $this->possibleModels())) {
            $this->components->error('Please provide a valid model name');

            exit();
        }
    }

    /**
     * Valid the test.
     *
     * @return void
     */
    protected function validTest(): void
    {
        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if (
            (!$this->hasOption('force') || !$this->option('force'))
            && $this->alreadyExists($this->getNameInput())
        ) {
            $this->components->error('Tests for this model already exist.');

            exit();
        }
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     * @phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        // phpcs:enable
        return $rootNamespace . '\Back';
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     * @phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
     */
    protected function getPath($name): string
    {
        // phpcs:enable
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return base_path('tests') . str_replace('\\', '/', $name) . 'Test.php';
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace(): string
    {
        return 'Tests';
    }
}
