<?php

namespace Tests\Renderers;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Laragear\Alerts\Bag;
use Tests\TestCase;
use function alert;

class BootstrapRendererTest extends TestCase
{
    use InteractsWithViews;

    protected Bag $bag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bag = $this->app[Bag::class];
    }

    public function test_renders_empty_if_no_alerts_done(): void
    {
        static::assertSame(
            '<div class="container"></div>',
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_renders_bootstrap_alert(): void
    {
        alert(
            'A Bootstrap alert',
            'primary',
            'secondary',
            'success',
            'danger',
            'warning',
            'info',
            'light',
            'dark',
            'foo',
            'bar',
            'dismiss'
        );

        static::assertSame(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="alert bar alert-danger alert-dark dismiss foo alert-info alert-light alert-primary alert-secondary alert-success alert-warning" role="alert">
    A Bootstrap alert
    </div>
    </div>
</div>
EOT,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_renders_dismissible_alert(): void
    {
        alert('A Bootstrap Alert', 'success', 'dark')->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="alert alert-dark alert-success fade show alert-dismissible" role="alert">
    A Bootstrap Alert
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
        alert('A Bootstrap Alert', 'success', 'success', 'foo', 'foo', 'foo', 'bar', 'alert-dismissible')->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="alert alert-dismissible bar foo alert-success fade show" role="alert">
    A Bootstrap Alert
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
        alert('A Bootstrap Alert to {link}', 'success', 'success', 'foo', 'foo', 'foo', 'bar', 'alert-dismissible')
            ->away('link', 'https://www.something.com')
            ->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="alert alert-dismissible bar foo alert-success fade show" role="alert">
    A Bootstrap Alert to <a href="https://www.something.com" target="_blank">link</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
        alert('A Bootstrap {Alert} to {link}', 'success', 'success', 'foo', 'foo', 'foo', 'bar', 'alert-dismissible')
            ->away('Alert', 'https://www.alert.com', false)
            ->away('link', 'https://www.something.com')
            ->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="alert alert-dismissible bar foo alert-success fade show" role="alert">
    A Bootstrap <a href="https://www.alert.com">Alert</a> to <a href="https://www.something.com" target="_blank">link</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
        alert('A Bootstrap {link} to {link}', 'success', 'success', 'foo', 'foo', 'foo', 'bar', 'alert-dismissible')
            ->away('link', 'https://www.something.com')
            ->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="alert alert-dismissible bar foo alert-success fade show" role="alert">
    A Bootstrap <a href="https://www.something.com" target="_blank">link</a> to <a href="https://www.something.com" target="_blank">link</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
        alert('A Bootstrap {Link} to {link}', 'success', 'success', 'foo', 'foo', 'foo', 'bar', 'alert-dismissible')
            ->away('link', 'https://www.something.com')
            ->dismiss();

        static::assertEquals(
            <<<'EOT'
<div class="container"><div class="alerts">
        <div class="alert alert-dismissible bar foo alert-success fade show" role="alert">
    A Bootstrap {Link} to <a href="https://www.something.com" target="_blank">link</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
        <div class="alert" role="alert">
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
        <div class="alert" role="alert">
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
        <div class="alert" role="alert">
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
