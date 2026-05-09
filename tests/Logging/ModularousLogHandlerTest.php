<?php

namespace Unusualify\Modularous\Tests\Logging;

use Illuminate\Support\Facades\Notification;
use Mockery;
use Monolog\Level;
use Monolog\LogRecord;
use Unusualify\Modularous\Logging\ModularousLogHandler;
use Unusualify\Modularous\Tests\TestCase;

class ModularousLogHandlerTest extends TestCase
{
    protected $tempLogDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempLogDir = sys_get_temp_dir() . '/test_logs_' . uniqid();
        mkdir($this->tempLogDir);
    }

    protected function tearDown(): void
    {
        // Clean up temp log files
        if (is_dir($this->tempLogDir)) {
            // Recursively delete all files and subdirectories
            $this->deleteDirectory($this->tempLogDir);
        }

        Mockery::close();
        parent::tearDown();
    }

    private function deleteDirectory($dir)
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * @test
     */
    public function it_can_be_instantiated_with_default_parameters()
    {
        $handler = new ModularousLogHandler;

        $this->assertInstanceOf(ModularousLogHandler::class, $handler);
    }

    /**
     * @test
     */
    public function it_can_be_instantiated_with_custom_parameters()
    {
        $handler = new ModularousLogHandler(Level::Warning, 30);

        $this->assertInstanceOf(ModularousLogHandler::class, $handler);
    }

    /**
     * @test
     */
    public function it_generates_daily_log_path_with_current_date()
    {
        $handler = new ModularousLogHandler;

        $reflection = new \ReflectionClass($handler);
        $method = $reflection->getMethod('getDailyLogPath');
        $method->setAccessible(true);

        $path = $method->invoke($handler);
        $expectedDate = date('Y-m-d');

        $this->assertStringContainsString('modularous-' . $expectedDate . '.log', $path);
        $this->assertStringContainsString('storage/logs', $path);
    }

    /**
     * @test
     */
    public function it_writes_debug_level_logs_to_file()
    {
        // Override storage_path to use temp directory
        $originalStoragePath = storage_path();
        app()->useStoragePath($this->tempLogDir);

        // Create logs subdirectory
        $logsDir = $this->tempLogDir . '/logs';
        if (! is_dir($logsDir)) {
            mkdir($logsDir, 0777, true);
        }

        $handler = new ModularousLogHandler;

        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Debug,
            message: 'Test debug message',
            context: ['key' => 'value']
        );

        $reflection = new \ReflectionClass($handler);
        $method = $reflection->getMethod('write');
        $method->setAccessible(true);
        $method->invoke($handler, $record);

        $logFile = $logsDir . '/modularous-' . date('Y-m-d') . '.log';
        $this->assertFileExists($logFile);

        $contents = file_get_contents($logFile);
        $this->assertStringContainsString('Test debug message', $contents);
        $this->assertStringContainsString('Debug', $contents); // Monolog uses capitalized level names

        // Restore original storage path
        app()->useStoragePath($originalStoragePath);
    }

    /**
     * @test
     */
    public function it_sends_email_for_critical_level_logs()
    {
        // We'll test that the sendEmailNotification method gets called for critical logs
        // by checking that it attempts to send via the notification route

        // Skip this test as it requires actual notification classes to exist
        $this->markTestSkipped('Requires SystemNotification module to be present');
    }

    /**
     * @test
     */
    public function it_does_not_send_email_for_debug_level_logs()
    {
        Notification::shouldReceive('route')->never();
        Notification::shouldReceive('notify')->never();

        // Override storage_path to use temp directory
        app()->useStoragePath($this->tempLogDir);

        // Create logs subdirectory
        $logsDir = $this->tempLogDir . '/logs';
        if (! is_dir($logsDir)) {
            mkdir($logsDir, 0777, true);
        }

        $handler = new ModularousLogHandler;

        $record = new LogRecord(
            datetime: new \DateTimeImmutable,
            channel: 'test',
            level: Level::Debug,
            message: 'Debug message',
            context: []
        );

        $reflection = new \ReflectionClass($handler);
        $method = $reflection->getMethod('write');
        $method->setAccessible(true);
        $method->invoke($handler, $record);

        // Test passes if Notification mocks were never called
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_rotates_old_log_files_when_exceeding_max_files()
    {
        // Create mock log directory structure
        $logDir = $this->tempLogDir . '/logs';
        mkdir($logDir);

        // Create 5 old log files with proper date pattern
        for ($i = 0; $i < 5; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $file = $logDir . "/modularous-{$date}.log";
            file_put_contents($file, "Old log content $i");
            // Set modification time to make oldest files recognizable
            touch($file, time() - ($i * 86400));
        }

        // Override storage_path
        app()->useStoragePath($this->tempLogDir);

        // Create handler with maxFiles = 3
        $handler = new ModularousLogHandler(Level::Debug, 3);

        $reflection = new \ReflectionClass($handler);
        $method = $reflection->getMethod('rotateOldFiles');
        $method->setAccessible(true);
        $method->invoke($handler);

        // Should have only 3 files remaining
        $remainingFiles = glob($logDir . '/modularous-*.log');
        $this->assertCount(3, $remainingFiles);
    }

    /**
     * @test
     */
    public function it_formats_log_message_correctly()
    {
        // Override storage_path to use temp directory
        app()->useStoragePath($this->tempLogDir);

        // Create logs subdirectory
        $logsDir = $this->tempLogDir . '/logs';
        if (! is_dir($logsDir)) {
            mkdir($logsDir, 0777, true);
        }

        $handler = new ModularousLogHandler;

        $record = new LogRecord(
            datetime: new \DateTimeImmutable('2024-02-15 10:30:00'),
            channel: 'modularous',
            level: Level::Info,
            message: 'Custom log message',
            context: ['user_id' => 123, 'action' => 'login']
        );

        $reflection = new \ReflectionClass($handler);
        $writeMethod = $reflection->getMethod('writeToFile');
        $writeMethod->setAccessible(true);
        $writeMethod->invoke($handler, $record);

        $logFile = $logsDir . '/modularous-' . date('Y-m-d') . '.log';
        $contents = file_get_contents($logFile);

        $this->assertStringContainsString('Info', $contents); // Monolog uses capitalized level names
        $this->assertStringContainsString('modularous', $contents);
        $this->assertStringContainsString('Custom log message', $contents);
        $this->assertStringContainsString('Context:', $contents);
        $this->assertStringContainsString('"user_id": 123', $contents);
    }
}
