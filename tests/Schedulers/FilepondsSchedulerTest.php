<?php

namespace Unusualify\Modularity\Tests\Schedulers;

use Illuminate\Support\Facades\Log;
use Mockery;
use Unusualify\Modularity\Facades\Filepond;
use Unusualify\Modularity\Schedulers\FilepondsScheduler;
use Unusualify\Modularity\Tests\TestCase;

class FilepondsSchedulerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_can_be_instantiated()
    {
        $scheduler = new FilepondsScheduler();

        $this->assertInstanceOf(FilepondsScheduler::class, $scheduler);
    }

    /**
     * @test
     */
    public function it_clears_temporary_files_with_default_days()
    {
        $temporaryFileponds = collect([]);

        // Mock Filepond facade
        Filepond::shouldReceive('clearTemporaryFiles')
            ->once()
            ->with(7)
            ->andReturn($temporaryFileponds);

        Filepond::shouldReceive('clearFolders')
            ->once()
            ->andReturn(null);

        // Mock Log facade
        Log::shouldReceive('channel')
            ->once()
            ->with('scheduler')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with('Modularity: Deleted 0 expired temporary fileponds in last 7 days');

        // Execute command via Artisan facade
        $this->artisan('modularity:fileponds:scheduler')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function it_clears_temporary_files_with_custom_days_option()
    {
        $temporaryFileponds = collect(['file1.tmp', 'file2.tmp', 'file3.tmp']);

        // Mock Filepond facade
        Filepond::shouldReceive('clearTemporaryFiles')
            ->once()
            ->with(14)
            ->andReturn($temporaryFileponds);

        Filepond::shouldReceive('clearFolders')
            ->once()
            ->andReturn(null);

        // Mock Log facade
        Log::shouldReceive('channel')
            ->once()
            ->with('scheduler')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with('Modularity: Deleted 3 expired temporary fileponds in last 14 days');

        // Execute command via Artisan with the --days option
        $this->artisan('modularity:fileponds:scheduler', ['--days' => 14])
            ->assertSuccessful();
    }

   /**
     * @test
     */
    public function it_logs_count_of_cleared_files()
    {
        $temporaryFileponds = collect(['file1.tmp', 'file2.tmp']);

        Filepond::shouldReceive('clearTemporaryFiles')
            ->once()
            ->with(7)
            ->andReturn($temporaryFileponds);

        Filepond::shouldReceive('clearFolders')
            ->once()
            ->andReturn(null);

        Log::shouldReceive('channel')
            ->once()
            ->with('scheduler')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with('Modularity: Deleted 2 expired temporary fileponds in last 7 days');

        $this->artisan('modularity:fileponds:scheduler')
            ->assertSuccessful();
    }
}
