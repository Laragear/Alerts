<?php

namespace Laragear\Alerts\Blade\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Laragear\Alerts\Alert;
use Laragear\Alerts\Bag;
use function array_map;
use function explode;
use function get_class;
use function in_array;
use function is_string;
use function view;

class Container extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  class-string<\Laragear\Alerts\Alert>[]  $filter
     */
    public function __construct(protected Bag $bag, public string|array $filter = [])
    {
        // Normalize the filters if the developer passed a string with comma-separated class names.
        if (is_string($this->filter)) {
            $this->filter = array_map('trim', explode(',', $this->filter));
        }
    }

    /**
     * @inheritDoc
     */
    public function render(): mixed
    {
        return view('alerts::container', [
            'alerts' => $this->alerts(),
        ]);
    }

    /**
     * Returns a list of filtered alerts.
     *
     * @return  \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert>
     */
    protected function alerts(): Collection
    {
        return $this->bag
            ->collect()
            ->when($this->filter, static function (Collection $alerts, array $classes): Collection {
                return $alerts->filter(static function (Alert $alert) use ($classes): bool {
                    return in_array(get_class($alert), $classes, true);
                });
            });
    }

    /**
     * Determine if the component should be rendered.
     */
    public function shouldRender(): bool
    {
        return $this->alerts()->isNotEmpty();
    }
}
