<?php

namespace Laragear\Alerts\Blade\Components;

use Illuminate\View\Component;
use Laragear\Alerts\Alert;
use Laragear\Alerts\Bag;
use Laragear\Alerts\Contracts\Renderer as RendererContract;

class Container extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        protected Bag $bag,
        protected RendererContract $renderer,
        protected array|string|null $tags = null)
    {
        // If the developer doesn't set tags, we will use the default list.
        $this->tags = (array) $tags ?: $bag->getDefaultTags();
    }

    /**
     * Get the view / view contents that represent the component.
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
     */
    public function shouldRender(): bool
    {
        return $this->bag->collect()->isNotEmpty();
    }
}
