<?php

namespace Unusualify\Modularous\Services\FileLibrary;

interface FileServiceInterface
{
    /**
     * @param string $id
     * @return string
     */
    public function getUrl($id);
}
