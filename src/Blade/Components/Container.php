<?php

namespace Laragear\Alerts\Blade\Components;

use Illuminate\View\Component;
use JetBrains\PhpStorm\Pure;
use Laragear\Alerts\Alert;
use Laragear\Alerts\Bag;
use Laragear\Alerts\Contracts\Renderer;

class Container extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  \Laragear\Alerts\Bag  $bag
     * @param  \Laragear\Alerts\Contracts\Renderer  $renderer
     * @param  array|string|null  $tags
     */
    #[Pure]
    public function __construct(
        protected Bag $bag,
        protected Renderer $renderer,
        protected array|string|null $tags = null)
    {
        // If the developer doesn't set tags, we will use the default list.
        $this->tags = (array) $tags ?: $bag->getDefaultTags();
    }

    /**
     * Get the view / view contents that represent the component.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->renderer->render(
            $this->bag->collect()->filter(function (Alert $alert): bool {
                return $alert->hasAnyTag(...$this->tags);
            })
        );
    }

    /**
     * Determine if the component should be rendered.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->bag->collect()->isNotEmpty();
    }
}
