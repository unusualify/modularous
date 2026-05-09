<?php

namespace Unusualify\Modularous\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader as LaravelFileLoader;

class FileLoader extends LaravelFileLoader
{
    /**
     * Create a new file loader instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files, array|string $path)
    {
        parent::__construct($files, $path);
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getGroups(): array
    {
        $groups = [];

        foreach ($this->getPaths() as $dir) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $group = basename($file->getFilename(), '.php');
                    if (! in_array($group, $groups)) {
                        $groups[] = $group;
                    }
                }
            }
        }

        return $groups;
    }

    /**
     * Add a path to the file loader.
     *
     * @param string $path
     * @return void
     */
    public function addPath($path)
    {
        $this->paths = array_merge($this->paths, is_string($path) ? [$path] : $path);
    }
}
