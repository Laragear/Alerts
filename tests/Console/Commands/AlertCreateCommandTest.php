<?php

namespace Tests\Console\Commands;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

use function app_path;
use function resource_path;

class AlertCreateCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(app_path('Alerts'));
        File::deleteDirectory(app_path('Views/Alerts'));
        File::deleteDirectory($this->app->viewPath('alerts'));
    }

    public function test_does_nothing_when_handle_returns_false(): void
    {
        $this->artisan('alert:create', [
            'name' => 'class',
        ]);

        static::assertFileDoesNotExist(app_path('Alerts/ClassAlert.php'));
    }

    public function test_creates_alert_with_default_view_name(): void
    {
        $this->artisan('alert:create', [
            'name' => 'MyCustomAlert',
        ]);

        static::assertFileExists(app_path('Alerts/MyCustomAlert.php'));

        $content = File::get(app_path('Alerts/MyCustomAlert.php'));

        static::assertStringContainsString('namespace App\Alerts;', $content);
        static::assertStringContainsString('class MyCustomAlert', $content);
        static::assertStringContainsString('view(\'alerts.my-custom-alert\', $this->all());', $content);

        static::assertFileExists($this->app->viewPath('alerts/my-custom-alert.blade.php'));

        $content = File::get($this->app->viewPath('alerts/my-custom-alert.blade.php'));

        static::assertStringContainsString(<<<'BLADE'
<div class="alert">
    {{ $body }}
</div>

BLADE, $content);
    }

    public function test_creates_alert_with_default_view_name_and_views_namespace(): void
    {
        File::ensureDirectoryExists(app_path('Views/Alerts'));

        $this->artisan('alert:create', [
            'name' => 'MyCustomAlert',
        ]);

        static::assertFileExists(app_path('Views/Alerts/MyCustomAlert.php'));

        $content = File::get(app_path('Views/Alerts/MyCustomAlert.php'));

        static::assertStringContainsString('namespace App\Views\Alerts;', $content);
        static::assertStringContainsString('class MyCustomAlert', $content);
        static::assertStringContainsString('view(\'alerts.my-custom-alert\', $this->all());', $content);

        static::assertFileExists($this->app->viewPath('alerts/my-custom-alert.blade.php'));
    }

    public function test_creates_alert_with_custom_view_name(): void
    {
        $this->artisan('alert:create', [
            'name' => 'MyCustomAlert',
            '--view' => 'test-alert',
        ]);

        $content = File::get(app_path('Alerts/MyCustomAlert.php'));

        static::assertStringContainsString('view(\'alerts.test-alert\', $this->all());', $content);

        static::assertFileExists($this->app->viewPath('alerts/test-alert.blade.php'));
    }


    public function test_creates_alert_with_custom_view_name_in_subdirectory(): void
    {
        $this->artisan('alert:create', [
            'name' => 'MyCustomAlert',
            '--view' => 'test.my-alert',
        ]);

        $content = File::get(app_path('Alerts/MyCustomAlert.php'));

        static::assertStringContainsString('view(\'alerts.test.my-alert\', $this->all());', $content);

        static::assertFileExists($this->app->viewPath('alerts/test/my-alert.blade.php'));
    }

    public function test_creates_alert_without_view(): void
    {
        $this->artisan('alert:create', [
            'name' => 'MyCustomAlert',
            '--no-view' => true,
        ]);

        $content = File::get(app_path('Alerts/MyCustomAlert.php'));

        static::assertStringContainsString('parent::toHtml();', $content);

        static::assertFileDoesNotExist($this->app->viewPath('alerts/my-custom-alert.blade.php'));
    }

    public function test_creates_alert_without_view_using_false(): void
    {
        $this->artisan('alert:create', [
            'name' => 'MyCustomAlert',
            '--view' => false,
        ]);

        $content = File::get(app_path('Alerts/MyCustomAlert.php'));

        static::assertStringContainsString('parent::toHtml();', $content);
    }

    public function test_creates_alert_without_view_using_false_as_name(): void
    {
        $this->artisan('alert:create', [
            'name' => 'MyCustomAlert',
            '--view' => 'false',
        ]);

        $content = File::get(app_path('Alerts/MyCustomAlert.php'));

        static::assertStringContainsString('parent::toHtml();', $content);
    }
}
