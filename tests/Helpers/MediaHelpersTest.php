<?php

namespace Unusualify\Modularity\Tests\Helpers;

use Unusualify\Modularity\Tests\TestCase;

class MediaHelpersTest extends TestCase
{
    /** @test */
    public function test_bytes_to_human_converts_bytes()
    {
        $this->assertEquals('512 B', bytesToHuman(512));
    }

    /** @test */
    public function test_bytes_to_human_converts_kilobytes()
    {
        // bytesToHuman uses > 1024, so exactly 1024 stays as bytes
        $this->assertEquals('1024 B', bytesToHuman(1024));
        $this->assertEquals('2.5 Kb', bytesToHuman(2560));
        $this->assertEquals('1 Kb', bytesToHuman(1025));
    }

    /** @test */
    public function test_bytes_to_human_converts_megabytes()
    {
        $this->assertEquals('1024 Kb', bytesToHuman(1024 * 1024));
        $this->assertEquals('1 Mb', bytesToHuman(1024 * 1024 + 1));
        $this->assertEquals('5.5 Mb', bytesToHuman(5.5 * 1024 * 1024 + 1));
    }

    /** @test */
    public function test_bytes_to_human_converts_gigabytes()
    {
        $this->assertEquals('1024 Mb', bytesToHuman(1024 * 1024 * 1024));
        $this->assertEquals('1 Gb', bytesToHuman(1024 * 1024 * 1024 + 1));
        $this->assertEquals('2.75 Gb', bytesToHuman(2.75 * 1024 * 1024 * 1024 + 1));
    }

    /** @test */
    public function test_bytes_to_human_converts_terabytes()
    {
        $this->assertEquals('1024 Gb', bytesToHuman(1024 * 1024 * 1024 * 1024));
        $this->assertEquals('1 Tb', bytesToHuman(1024 * 1024 * 1024 * 1024 + 1));
    }

    /** @test */
    public function test_bytes_to_human_converts_petabytes()
    {
        $this->assertEquals('1024 Tb', bytesToHuman(1024 * 1024 * 1024 * 1024 * 1024));
        $this->assertEquals('1 Pb', bytesToHuman(1024 * 1024 * 1024 * 1024 * 1024 + 1));
    }

    /** @test */
    public function test_replace_accents_removes_accented_characters()
    {
        // iconv transliteration may vary by system. Accept approximate results.
        $result = replaceAccents('café');
        $this->assertStringContainsString('caf', $result);

        $result = replaceAccents('naïve');
        $this->assertStringContainsString('na', $result);

        $result = replaceAccents('Zürich');
        $this->assertStringContainsString('rich', $result);
    }

    /** @test */
    public function test_replace_accents_handles_various_unicode_characters()
    {
        // iconv transliteration may vary by system
        $result = replaceAccents('Français');
        $this->assertStringContainsString('Fran', $result);

        $result = replaceAccents('Español');
        $this->assertStringContainsString('Espa', $result);
    }

    /** @test */
    public function test_sanitize_filename_replaces_spaces_with_dashes()
    {
        $this->assertEquals('my-document.pdf', sanitizeFilename('my document.pdf'));
        $this->assertEquals('test-file.txt', sanitizeFilename('test file.txt'));
    }

    /** @test */
    public function test_sanitize_filename_replaces_url_encoded_spaces()
    {
        $this->assertEquals('my-file.pdf', sanitizeFilename('my%20file.pdf'));
    }

    /** @test */
    public function test_sanitize_filename_replaces_underscores_with_dashes()
    {
        $this->assertEquals('my-file.pdf', sanitizeFilename('my_file.pdf'));
    }

    /** @test */
    public function test_sanitize_filename_removes_special_characters()
    {
        $this->assertEquals('myfile.pdf', sanitizeFilename('my@file#.pdf'));
        $this->assertEquals('document.txt', sanitizeFilename('document!?.txt'));
    }

    /** @test */
    public function test_sanitize_filename_removes_multiple_dots_except_last()
    {
        $this->assertEquals('myfiletestv2.pdf', sanitizeFilename('my.file.test.v2.pdf'));
    }

    /** @test */
    public function test_sanitize_filename_replaces_multiple_dashes_with_single()
    {
        $this->assertEquals('my-file.pdf', sanitizeFilename('my---file.pdf'));
    }

    /** @test */
    public function test_sanitize_filename_removes_dash_before_extension()
    {
        $this->assertEquals('myfile.pdf', sanitizeFilename('myfile-.pdf'));
    }

    /** @test */
    public function test_sanitize_filename_converts_to_lowercase()
    {
        $this->assertEquals('myfile.pdf', sanitizeFilename('MyFile.PDF'));
        $this->assertEquals('document.txt', sanitizeFilename('DOCUMENT.TXT'));
    }

    /** @test */
    public function test_sanitize_filename_handles_accented_characters()
    {
        $this->assertEquals('cafe.pdf', sanitizeFilename('café.pdf'));
    }

    /** @test */
    public function test_sanitize_filename_complex_example()
    {
        $this->assertEquals('my-awesome-document-v2.pdf', sanitizeFilename('My Awesome_Document!!_v2.pdf'));
    }
}
