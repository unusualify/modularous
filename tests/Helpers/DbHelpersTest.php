<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Illuminate\Support\Facades\DB;
use Unusualify\Modularous\Tests\TestCase;

class DbHelpersTest extends TestCase
{
    /** @test */
    public function test_database_exists_returns_true_when_connection_succeeds()
    {
        // Mock the DB facade to return a PDO instance
        DB::shouldReceive('connection')
            ->once()
            ->andReturnSelf();

        DB::shouldReceive('getPDO')
            ->once()
            ->andReturn(new \PDO('sqlite::memory:'));

        $result = database_exists();

        $this->assertTrue($result);
    }

    /** @test */
    public function test_database_exists_returns_false_when_connection_fails()
    {
        // Mock the DB facade to throw an exception
        DB::shouldReceive('connection')
            ->once()
            ->andReturnSelf();

        DB::shouldReceive('getPDO')
            ->once()
            ->andThrow(new \Exception('Connection failed'));

        $result = database_exists();

        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
