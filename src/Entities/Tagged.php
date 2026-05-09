<?php

namespace Unusualify\Modularous\Entities;

use Cartalyst\Tags\IlluminateTagged;

class Tagged extends IlluminateTagged
{
    public function getTable()
    {
        return modularousConfig('tables.tagged', parent::getTable());
    }
}
