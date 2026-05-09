<?php

namespace Unusualify\Modularous\Http\Controllers\Traits;

trait ManageAppends
{
    /**
     * Appends to add to the index view.
     *
     * @var array
     */
    protected $indexAppends = [];

    /**
     * Appends to add to the form view.
     *
     * @var array
     */
    protected $formAppends = [];

    public function addIndexAppends()
    {
        $methods = array_filter(get_class_methods(static::class), function ($method) {
            return preg_match('/addIndexAppends[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $this->indexAppends = array_unique(array_merge($this->indexAppends, $this->{$method}()));
        }
    }

    public function addFormAppends()
    {
        $editOnModal = $this->tableAttributes['editOnModal'] ?? true;

        $methods = array_filter(get_class_methods(static::class), function ($method) {
            return preg_match('/addFormAppends[A-Z]{1}[A-Za-z]+/', $method);
        });

        foreach ($methods as $key => $method) {
            $formAppends = $this->{$method}();
            $this->formAppends = array_merge($this->formAppends, $formAppends);
            if ($editOnModal) {
                $this->indexAppends = array_unique(array_merge($this->indexAppends, $formAppends));
            }
        }
    }

    public function getIndexAppends()
    {
        $editOnModal = $this->tableAttributes['editOnModal'] ?? true;
        if ($editOnModal) {
            return array_unique(array_merge($this->indexAppends, $this->formAppends));
        }

        return $this->indexAppends;
    }

    public function getFormAppends()
    {
        return $this->formAppends;
    }
}
