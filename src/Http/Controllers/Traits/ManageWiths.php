<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

trait ManageWiths
{
    /**
     * Relations to eager load for the index view.
     *
     * @var array
     */
    protected $indexWith = [];

    /**
     * Relations to eager load for the form view.
     *
     * @var array
     */
    protected $formWith = [];

    protected function addIndexWiths()
    {
        $methods = array_filter(get_class_methods(static::class), function ($method) {
            return preg_match('/addIndexWiths[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $this->indexWith = array_merge($this->indexWith, $this->{$method}());
        }
    }

    protected function addFormWiths()
    {
        $editOnModal = $this->tableAttributes['editOnModal'] ?? true;
        $methods = array_filter(get_class_methods(static::class), function ($method) {
            return preg_match('/addFormWiths[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $formWiths = $this->{$method}();
            $this->formWith += array_merge($this->formWith, $formWiths);
            if ($editOnModal) {
                $this->indexWith = array_merge($this->indexWith, $formWiths);
            }
        }
    }

    protected function addWiths()
    {
        $methods = array_filter(get_class_methods(static::class), function ($method) {
            return preg_match('/addWiths[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $withs = $this->{$method}();
            $this->indexWith = array_merge($this->indexWith, $withs);
            $this->formWith = array_merge($this->formWith, $withs);
        }
    }
}
