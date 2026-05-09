<?php

namespace Unusualify\Modularous\Events\Traits;

use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasStateable;

trait EventStateable
{
    /**
     * The model has stateable trait.
     *
     * @var bool
     */
    public $hasStateable = false;

    /**
     * The stateable changed.
     *
     * @var string|null
     */
    public $stateableChanged = false;

    /**
     * The previous state name.
     *
     * @var string|null
     */
    public $previousStateableState = null;

    /**
     * The current state name.
     *
     * @var string|null
     */
    public $currentStateableState = null;

    public function setupEventStateable()
    {
        if ($this->model instanceof Model && classHasTrait($this->model, HasStateable::class)) {
            $this->hasStateable = true;
            $this->stateableChanged = $this->model->stateableChanged();
            $this->previousStateableState = $this->model->previousStateableState();
            $this->currentStateableState = $this->model->currentStateableState();
        }
    }
}
