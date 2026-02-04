<?php

namespace Unusualify\Modularity\Tests\Repositories;

use Unusualify\Modularity\Repositories\Repository;

class NoteRepository extends Repository
{
    public function __construct(Note $model)
    {
        $this->model = $model;
    }
}
