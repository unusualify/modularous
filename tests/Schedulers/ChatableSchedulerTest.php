<?php

namespace Unusualify\Modularity\Tests\Schedulers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Unusualify\Modularity\Facades\ModularityFinder;
use Unusualify\Modularity\Schedulers\ChatableScheduler;
use Unusualify\Modularity\Tests\TestCase;

class ChatableSchedulerTest extends TestCase
{
    /** @test */
    public function test_constructor_initializes_correctly()
    {
        $scheduler = new ChatableScheduler();

        $this->assertInstanceOf(ChatableScheduler::class, $scheduler);
    }

    /** @test */
    public function test_command_signature_is_correct()
    {
        $scheduler = new ChatableScheduler();

        $this->assertEquals('modularity:scheduler:chatable', $scheduler->getName());
    }

    /** @test */
    public function test_handle_processes_models_with_chatable_trait()
    {
        // Create mock model
        $mockModel = \Mockery::mock('alias:TestChatableModel');
        $mockQueryBuilder = \Mockery::mock('Illuminate\Database\Eloquent\Builder');
        
        // Mock the chunk callback
        $mockQueryBuilder->shouldReceive('chunk')
            ->with(100, \Mockery::type('Closure'))
            ->once()
            ->andReturnUsing(function ($size, $callback) {
                // Simulate empty result
                return true;
            });

        $mockModel->shouldReceive('hasNotifiableMessage')
            ->once()
            ->andReturn($mockQueryBuilder);

        ModularityFinder::shouldReceive('getModelsWithTrait')
            ->with(\Unusualify\Modularity\Entities\Traits\Chatable::class)
            ->once()
            ->andReturn([$mockModel]);

        $scheduler = new ChatableScheduler();
        $scheduler->handle();

        $this->assertTrue(true); // Assertion to confirm no exceptions
    }

    /** @test */
    public function test_handle_chunks_items_and_calls_notification_handler()
    {
        // Create mock items
        $mockItem1 = \Mockery::mock();
        $mockItem1->shouldReceive('handleChatableNotification')
            ->once();

        $mockItem2 = \Mockery::mock();
        $mockItem2->shouldReceive('handleChatableNotification')
            ->once();

        $mockModel = \Mockery::mock('alias:TestChatableModel');
        $mockQueryBuilder = \Mockery::mock('Illuminate\Database\Eloquent\Builder');
        
        $mockQueryBuilder->shouldReceive('chunk')
            ->with(100, \Mockery::type('Closure'))
            ->once()
            ->andReturnUsing(function ($size, $callback) use ($mockItem1, $mockItem2) {
                // Simulate chunk with 2 items
                $callback(collect([$mockItem1, $mockItem2]));
                return true;
            });

        $mockModel->shouldReceive('hasNotifiableMessage')
            ->once()
            ->andReturn($mockQueryBuilder);

        ModularityFinder::shouldReceive('getModelsWithTrait')
            ->with(\Unusualify\Modularity\Entities\Traits\Chatable::class)
            ->once()
            ->andReturn([$mockModel]);

        $scheduler = new ChatableScheduler();
        $scheduler->handle();

        // Mockery will verify all expectations automatically
        $this->assertTrue(true);
    }

    /** @test */
    public function test_handle_processes_multiple_models()
    {
        // Create mock for first model
        $mockModel1 = \Mockery::mock('alias:TestChatableModel1');
        $mockQueryBuilder1 = \Mockery::mock('Illuminate\Database\Eloquent\Builder');
        $mockQueryBuilder1->shouldReceive('chunk')
            ->with(100, \Mockery::type('Closure'))
            ->once()
            ->andReturn(true);
        $mockModel1->shouldReceive('hasNotifiableMessage')
            ->once()
            ->andReturn($mockQueryBuilder1);

        // Create mock for second model
        $mockModel2 = \Mockery::mock('alias:TestChatableModel2');
        $mockQueryBuilder2 = \Mockery::mock('Illuminate\Database\Eloquent\Builder');
        $mockQueryBuilder2->shouldReceive('chunk')
            ->with(100, \Mockery::type('Closure'))
            ->once()
            ->andReturn(true);
        $mockModel2->shouldReceive('hasNotifiableMessage')
            ->once()
            ->andReturn($mockQueryBuilder2);

        ModularityFinder::shouldReceive('getModelsWithTrait')
            ->with(\Unusualify\Modularity\Entities\Traits\Chatable::class)
            ->once()
            ->andReturn([$mockModel1, $mockModel2]);

        $scheduler = new ChatableScheduler();
        $scheduler->handle();

        $this->assertTrue(true);
    }

    /** @test */
    public function test_handle_logs_error_on_exception()
    {
        $exception = new \Exception('Test error message');

        ModularityFinder::shouldReceive('getModelsWithTrait')
            ->with(\Unusualify\Modularity\Entities\Traits\Chatable::class)
            ->once()
            ->andThrow($exception);

        Log::shouldReceive('channel')
            ->with('scheduler')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('error')
            ->with('Modularity: Chatable scheduler error', \Mockery::on(function ($context) {
                return isset($context['error']) 
                    && $context['error'] === 'Test error message'
                    && isset($context['trace'])
                    && is_string($context['trace']);
            }))
            ->once();

        $scheduler = new ChatableScheduler();
        $scheduler->handle();

        // Mockery will verify the log was called
        $this->assertTrue(true);
    }

    /** @test */
    public function test_handle_catches_throwable_not_just_exceptions()
    {
        $error = new \Error('Test error');

        ModularityFinder::shouldReceive('getModelsWithTrait')
            ->with(\Unusualify\Modularity\Entities\Traits\Chatable::class)
            ->once()
            ->andThrow($error);

        Log::shouldReceive('channel')
            ->with('scheduler')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('error')
            ->with('Modularity: Chatable scheduler error', \Mockery::type('array'))
            ->once();

        $scheduler = new ChatableScheduler();
        $scheduler->handle();

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
