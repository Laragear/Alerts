<?php

namespace Tests\Renderers;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Laragear\Alerts\Bag;
use Tests\TestCase;
use function alert;

class TailwindRendererTest extends TestCase
{
    use InteractsWithViews;

    protected Bag $bag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bag = $this->app[Bag::class];
    }

    protected function defineEnvironment($app)
    {
        $app->make('config')->set('alerts.default', 'tailwind');
    }

    public function test_renders_empty_if_no_alerts_done(): void
    {
        static::assertSame(
            '<div class="container"></div>',
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_renders_tailwind_alert(): void
    {
        alert(
            'A Tailwind alert',
            'success',
            'danger',
            'warning',
            'info',
            'light',
            'dark',
        );

        static::assertSame(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 danger bg-gray-800 ring-white/10 text-gray-300 bg-blue-100 ring-blue-500/20 text-blue-900 bg-white ring-gray-900/5 text-gray-900 bg-green-100 ring-green-500/20 text-green-900 bg-yellow-100 ring-yellow-500/20 text-yellow-900">
        A Tailwind alert
</div>
    </div>
</div>
EOT,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_renders_dismissible_alert(): void
    {
        alert('A Tailwind Alert', 'light', 'dark')->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 bg-gray-800 ring-white/10 text-gray-300 bg-white ring-gray-900/5 text-gray-900 transition-opacity opacity-100">
            <button type="button" class="float-right font-bold px-4 pt-4 pb-4 -mr-4 -mt-4 -mb-4 bg-red-600">×</button>
        A Tailwind Alert
</div>
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_eliminates_duplicate_classes(): void
    {
        alert('A Tailwind Alert', 'success', 'success', 'foo', 'foo', 'foo', 'bar', 'alert-dismissible')->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 alert-dismissible bar foo bg-green-100 ring-green-500/20 text-green-900 transition-opacity opacity-100">
            <button type="button" class="float-right font-bold px-4 pt-4 pb-4 -mr-4 -mt-4 -mb-4 bg-red-600">×</button>
        A Tailwind Alert
</div>
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_renders_alert_with_link(): void
    {
        alert('A Tailwind Alert to {link}', 'success', 'success', 'foo', 'foo', 'foo', 'bar')
            ->away('link', 'https://www.something.com')
            ->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 bar foo bg-green-100 ring-green-500/20 text-green-900 transition-opacity opacity-100">
            <button type="button" class="float-right font-bold px-4 pt-4 pb-4 -mr-4 -mt-4 -mb-4 bg-red-600">×</button>
        A Tailwind Alert to <a href="https://www.something.com" target="_blank">link</a>
</div>
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_renders_alert_with_multiple_links(): void
    {
        alert('A Tailwind {Alert} to {link}', 'success', 'success', 'foo', 'foo', 'foo', 'bar', 'alert-dismissible')
            ->away('Alert', 'https://www.alert.com', false)
            ->away('link', 'https://www.something.com')
            ->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 alert-dismissible bar foo bg-green-100 ring-green-500/20 text-green-900 transition-opacity opacity-100">
            <button type="button" class="float-right font-bold px-4 pt-4 pb-4 -mr-4 -mt-4 -mb-4 bg-red-600">×</button>
        A Tailwind <a href="https://www.alert.com">Alert</a> to <a href="https://www.something.com" target="_blank">link</a>
</div>
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_renders_alert_with_same_link_multiple_times(): void
    {
        alert('A Tailwind {link} to {link}', 'success', 'success', 'foo', 'foo', 'foo', 'bar', 'alert-dismissible')
            ->away('link', 'https://www.something.com')
            ->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 alert-dismissible bar foo bg-green-100 ring-green-500/20 text-green-900 transition-opacity opacity-100">
            <button type="button" class="float-right font-bold px-4 pt-4 pb-4 -mr-4 -mt-4 -mb-4 bg-red-600">×</button>
        A Tailwind <a href="https://www.something.com" target="_blank">link</a> to <a href="https://www.something.com" target="_blank">link</a>
</div>
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_link_is_case_sensitive(): void
    {
        alert('A Tailwind {Link} to {link}', 'success', 'success', 'foo', 'foo', 'foo', 'bar', 'alert-dismissible')
            ->away('link', 'https://www.something.com')
            ->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 alert-dismissible bar foo bg-green-100 ring-green-500/20 text-green-900 transition-opacity opacity-100">
            <button type="button" class="float-right font-bold px-4 pt-4 pb-4 -mr-4 -mt-4 -mb-4 bg-red-600">×</button>
        A Tailwind {Link} to <a href="https://www.something.com" target="_blank">link</a>
</div>
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_shows_default_tags_by_default(): void
    {
        alert('foo')->tag('bar');
        alert('quz');

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 ">
        quz
</div>
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_filters_tags(): void
    {
        alert('foo')->tag('bar');
        alert('quz');

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 ">
        foo
</div>
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container :tags="[\'quz\', \'bar\']" /></div>')
        );
    }

    public function test_filters_single_tag(): void
    {
        alert('foo')->tag('bar');
        alert('quz');

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="relative max-w-lg rounded-lg px-4 py-4 mb-2 shadow-md ring-1 ">
        foo
</div>
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container :tags="\'bar\'" /></div>')
        );
    }
}
