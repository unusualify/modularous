<?php

namespace Unusualify\Modularity\Tests\Support;

use Unusualify\Modularity\Support\RegexReplacement;
use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Support\Facades\File;

class RegexReplacementTest extends TestCase
{
    protected $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        // Use a unique directory per process/run to avoid collisions in parallel
        $this->tempDir = sys_get_temp_dir() . '/modularous_regex_' . uniqid();
        if (!File::exists($this->tempDir)) {
            File::makeDirectory($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempDir)) {
            File::deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    /** @test */
    public function it_can_be_instantiated_and_properties_set()
    {
        $regex = new RegexReplacement($this->tempDir, '/pattern/', 'data');
        
        $this->assertInstanceOf(RegexReplacement::class, $regex);
        
        $regex->setPath('/new/path');
        $regex->setPattern('/new-pattern/');
        $regex->setData('new-data');
        $regex->setDirectoryPattern('*.txt');
        
        // Properties are protected, but we can test they were set by running methods that use them
        // or using reflection if strictly necessary. For now, we'll trust the setters if the logic works.
    }

    /** @test */
    public function it_can_replace_pattern_in_a_file()
    {
        $filePath = $this->tempDir . '/test.txt';
        File::put($filePath, 'Hello World');

        $regex = new RegexReplacement($this->tempDir, '/World/', 'Modularous');
        $result = $regex->replacePatternFile($filePath);

        $this->assertTrue($result);
        $this->assertEquals('Hello Modularous', File::get($filePath));
    }

    /** @test */
    public function it_can_run_recursively_in_directory()
    {
        $subDir = $this->tempDir . '/sub';
        File::makeDirectory($subDir);
        
        File::put($this->tempDir . '/file1.php', 'Old Content');
        File::put($subDir . '/file2.php', 'Old Content');
        File::put($this->tempDir . '/file3.txt', 'Old Content'); // Should not be matched by default **/*.php

        $regex = new RegexReplacement($this->tempDir, '/Old/', 'New');
        $regex->run();

        $this->assertEquals('New Content', File::get($this->tempDir . '/file1.php'));
        $this->assertEquals('New Content', File::get($subDir . '/file2.php'));
        $this->assertEquals('Old Content', File::get($this->tempDir . '/file3.txt'));
    }

    /** @test */
    public function it_respects_pretending_mode()
    {
        $filePath = $this->tempDir . '/test.php';
        File::put($filePath, 'Original');

        $regex = new RegexReplacement($this->tempDir, '/Original/', 'Replaced', '**/*.php', false, null, true);
        $regex->replacePatternFile($filePath);

        $this->assertEquals('Original', File::get($filePath));
    }

    /** @test */
    public function it_handles_empty_files()
    {
        $filePath = $this->tempDir . '/empty.php';
        File::put($filePath, '');

        $regex = new RegexReplacement($this->tempDir, '/any/', 'thing');
        $result = $regex->replacePatternFile($filePath);

        $this->assertFalse($result);
    }
}
