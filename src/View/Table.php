<?php

namespace Unusualify\Modularous\View;

use Illuminate\View\Component;
use Illuminate\View\View;

class Table extends Component
{
    /**
     * The headers.
     *
     * @var array
     */
    public $headers;

    /**
     * The inputs.
     *
     * @var array
     */
    public $inputs;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($headers, $inputs, $name)
    {
        $this->headers = $headers;
        $this->inputs = $inputs;
        $this->name = $name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        $baseKey = $this->baseKey ?? modularousBaseKey();

        return view("{$baseKey}::components.table");
    }
}
