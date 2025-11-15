<?php

namespace Tests\Blade\Components;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Laragear\Alerts\Bag;
use Tests\Fixtures\TestAlert;
use Tests\Fixtures\TestAlertWithView;
use Tests\TestCase;

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
        $this->bag->add(new TestAlertWithView(['foo' => 'bar']));

        static::assertEquals(<<<'EOT'
<div class="container"><div class="alerts">
            itrenders
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container /></div>')
        );
    }

    public function test_renders_only_some_alerts_based_on_alert_class(): void
    {
        $this->bag->add([
            new TestAlertWithView(),
            new TestAlert()
        ]);

        static::assertEquals(<<<'EOT'
<div class="container"><div class="alerts">
            itrenders
    </div>
</div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container filter="Tests\Fixtures\TestAlertWithView"/></div>')
        );

        static::assertEquals(<<<'EOT'
<div class="container"></div>
EOT
            ,
            (string) $this->blade('<div class="container"><x-alerts-container filter="Invalid"/></div>')
        );
    }
}
