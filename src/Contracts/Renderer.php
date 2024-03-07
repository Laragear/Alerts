<?php

namespace Laragear\Alerts\Contracts;

use Illuminate\Support\Collection;

interface Renderer
{
    /**
     * Returns the rendered alerts as a single HTML string.
     *
     * @param  \Illuminate\Support\Collection<int, \Laragear\Alerts\Alert>  $alerts
     */
    public function render(Collection $alerts): string;
}
