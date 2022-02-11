<?php

namespace Tests\Blade\Components;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Collection;
use Laragear\Alerts\Alert;
use Laragear\Alerts\Bag;
use Laragear\Alerts\Contracts\Renderer;
use Tests\TestCase;
use function alert;

class ContainerTest extends TestCase
{
    use InteractsWithViews;

    protected Bag $bag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bag = $this->app[Bag::class];
    }

    public function test_doesnt_renders_without_alerts(): void
    {
        static::assertEmpty($this->bag->collect());

        static::assertEquals(<<<'EOT'
<div class="container"></div>
EOT
        , (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_renders_alerts(): void
    {
        $render = $this->mock(Renderer::class);

        $render->shouldReceive('render')
            ->once()
            ->withArgs(function (Collection $alerts) {
                static::assertCount(1, $alerts);
                static::assertInstanceOf(Alert::class, $alerts->get(0));

                return true;
            })
            ->andReturn('<foo>bar</foo>');

        alert('foo', 'bar');

        static::assertEquals(<<<'EOT'
<div class="container"><foo>bar</foo></div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }
}
