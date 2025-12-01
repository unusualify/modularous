<?php

namespace Unusualify\Modularity\Events\Traits;

use Unusualify\Modularity\Entities\Traits\HasStateable;

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
        if($this->model instanceof \Unusualify\Modularity\Entities\Model && classHasTrait($this->model, HasStateable::class)) {
            $this->hasStateable = true;
            $this->stateableChanged = $this->model->stateableChanged();
            $this->previousStateableState = $this->model->previousStateableState();
            $this->currentStateableState = $this->model->currentStateableState();
        }
    }

}
