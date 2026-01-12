<?php

namespace Unusualify\Modularity\Repositories\Logic;

trait CountBuilders
{
    use MethodTransformers;

    /**
     * @return int
     */
    public function getCountForAll()
    {
        return $this->cacheableCount('all', function () {
            $query = $this->model->newQuery();

            return $this->filter($query, $this->countScope)->count();
        });
    }

    /**
     * @return int
     */
    public function getCountForPublished()
    {
        return $this->cacheableCount('published', function () {
            $query = $this->model->newQuery();

            return $this->filter($query, $this->countScope)->published()->count();
        });
    }

    /**
     * @return int
     */
    public function getCountForDraft()
    {
        return $this->cacheableCount('draft', function () {
            $query = $this->model->newQuery();

            return $this->filter($query, $this->countScope)->draft()->count();
        });
    }

    /**
     * @return int
     */
    public function getCountForTrash()
    {
        return $this->cacheableCount('trash', function () {
            $query = $this->model->newQuery();

            return $this->filter($query, $this->countScope)->onlyTrashed()->count();
        });
    }

    /**
     * @return int
     */
    public function getCountFor($method, $args = [])
    {
        return $this->cacheableCount($method, function () use ($method, $args) {
            $query = $this->model->newQuery();

            if (method_exists($this->getModel(), 'scope' . ucfirst($method))) {
                return $this->filter($query, $this->countScope)->$method(...$args)->count();
            }

            throw new \Exception('Method scope' . ucfirst($method) . ' does not exist');
        }, $args);
    }
}
