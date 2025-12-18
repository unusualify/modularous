<?php

namespace Unusualify\Modularity\Repositories\Logic;

use Unusualify\Modularity\Traits\ManageTraits;

trait Schema
{
    use ManageTraits;

    /**
     * @var array
     */
    protected $schema;

    /**
     * Get the schema
     *
     * @return array
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Set the schema
     *
     * @param array $schema
     */
    public function setSchema($schema = null)
    {
        $this->schema = $schema;
    }

    /**
     * Get the schema
     *
     * @return array
     */
    public function getInputs()
    {
        if(isset($this->schema)){
            return $this->schema;
        }

        return $this->inputs();
    }

    /**
     * Get the raw inputs
     *
     * @return array
     */
    public function getRawInputs()
    {
        return $this->inputs();
    }

    /**
     * Get the raw chunked inputs
     *
     * @param bool $all
     * @param bool $noGroupChunk
     * @return array
     */
    public function getRawChunkedInputs($all = false, $noGroupChunk = false)
    {
        return $this->chunkInputs($this->getRawInputs(), $all, $noGroupChunk);
    }
    /**
     * Set the inputs
     *
     * @param bool $all
     * @param bool $noGroupChunk
     */
    public function getChunkedInputs($all = false, $noGroupChunk = false)
    {
        return $this->chunkInputs($this->getInputs(), $all, $noGroupChunk);
    }
}
