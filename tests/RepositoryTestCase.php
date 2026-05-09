<?php

namespace Unusualify\Modularous\Tests;

abstract class RepositoryTestCase extends ModelTestCase
{
    public $path;

    public $modulesPath;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [

        ]);
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
    }
}
