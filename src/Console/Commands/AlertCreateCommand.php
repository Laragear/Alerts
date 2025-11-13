<?php

namespace Laragear\Alerts\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use function app_path;
use function dirname;
use function file_exists;
use function file_get_contents;
use function is_dir;
use function sprintf;
use function str_replace;
use function strtolower;
use function trim;
use function windows_os;

class AlertCreateCommand extends GeneratorCommand implements PromptsForMissingInput
{
    protected const STUB_FILE = '/stubs/alert.stub';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'alert:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a Alert class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Alert';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return;
        }

        if ($this->hasOption('no-view') || strtolower($this->option('view')) === 'false') {
            return;
        }

        $this->writeBladeView();
    }

    /**
     * Write the Markdown template for the mailable.
     *
     * @return void
     */
    protected function writeBladeView(): void
    {
        $separator = '/';

        if (windows_os()) {
            $separator = '\\';
        }

        $path = $this->viewPath(str_replace('.', $separator, 'alerts.'. $this->getViewName()).'.blade.php');

        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true);
        }

        $this->files->put($path, file_get_contents(__DIR__.'/../../../stubs/alert-view.stub'));

        $this->components->info(sprintf('%s [%s] created successfully.', 'View', $path));
    }

    /**
     * Returns the name of the view.
     */
    protected function getViewName(): string
    {
        return $this->option('view') ?? Str::snake($this->argument('name'));
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        $replace = $this->hasOption('no-view')
            ? 'parent::toHtml()'
            : 'view(alerts.'. $this->getViewName() .', $this->all())';

        return str_replace('{{ view }}', $replace, $class);
    }

    /**
     * @inheritDoc
     */
    protected function getStub(): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim(static::STUB_FILE, '/')))
            ? $customPath
            : __DIR__.'/..'.static::STUB_FILE;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        if (is_dir(app_path('Views\\Alerts'))) {
            $rootNamespace .= '\\Views';
        }

        return $rootNamespace.'\\Alerts';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the alert already exists'],
            ['view', 'v', InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE, 'Specify a view name, or use "--no-view" or "false" to skip creating it.'],
        ];
    }
}
