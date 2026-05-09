<?php

namespace Unusualify\Modularous\Tests\Repositories;

use Unusualify\Modularous\Repositories\Repository;

class NoteRepository extends Repository
{
    public function __construct(Note $model)
    {
        $this->model = $model;
    }
}
