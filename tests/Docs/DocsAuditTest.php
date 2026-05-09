<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Docs;

use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Unusualify\Modularous\Console\Docs\DocsAuditCommand;
use Unusualify\Modularous\Tests\TestCase;

/**
 * Ensures every tracked source file in the Modularous package has a
 * corresponding documentation page. Run with:
 *
 *   vendor/bin/phpunit --filter DocsAuditTest
 *
 * When this test fails it means new PHP files were added without documentation.
 * Either create the missing .md pages or, for intentionally undocumented files,
 * add an exclude rule in DocsAuditCommand::sections().
 */
class DocsAuditTest extends TestCase
{
    private string $packageRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->packageRoot = realpath(__DIR__ . '/../..');
    }

    /** @test */
    public function all_tracked_source_files_have_documentation(): void
    {
        $missing = $this->collectMissingDocs();

        if (empty($missing)) {
            $this->assertTrue(true);

            return;
        }

        $message = "The following source files are missing documentation:\n\n";

        foreach ($missing as $section => $files) {
            $message .= "  [{$section}]\n";
            foreach ($files as $file) {
                $message .= "    - {$file['class']}  ({$file['source']})  → expected: {$file['expected']}\n";
            }
            $message .= "\n";
        }

        $message .= 'Create the missing .md files or add exclude rules in DocsAuditCommand::sections().';

        $this->fail($message);
    }

    /** @test */
    public function docs_audit_command_exits_zero_when_all_documented(): void
    {
        $missing = $this->collectMissingDocs();

        if (! empty($missing)) {
            $this->markTestSkipped('Skipped because there are currently undocumented files — fix those first.');
        }

        $this->artisan('modularous:docs:audit', ['--fail-on-missing' => true])
            ->assertExitCode(0);
    }

    /** @test */
    public function docs_audit_command_runs_without_errors(): void
    {
        $this->artisan('modularous:docs:audit')
            ->assertExitCode(0);
    }

    /**
     * Collect all missing docs across every tracked section.
     *
     * @return array<string, list<array{class: string, source: string, expected: string}>>
     */
    private function collectMissingDocs(): array
    {
        $allMissing = [];

        foreach (DocsAuditCommand::sections() as $section) {
            $sourceDir = $this->packageRoot . '/' . $section['source'];
            $docsDir = $this->packageRoot . '/' . $section['docs'];

            if (! is_dir($sourceDir)) {
                continue;
            }

            $sourceFiles = $this->scanSourceFiles($sourceDir, $section);
            $docSlugs = is_dir($docsDir) ? $this->scanDocSlugs($docsDir) : [];

            foreach ($sourceFiles as $relPath => $className) {
                $expectedSlug = Str::kebab($className);

                if (! isset($docSlugs[$expectedSlug])) {
                    $allMissing[$section['label']][] = [
                        'class' => $className,
                        'source' => $section['source'] . '/' . $relPath,
                        'expected' => $expectedSlug . '.md',
                    ];
                }
            }
        }

        return $allMissing;
    }

    private function scanSourceFiles(string $dir, array $section): array
    {
        $recursive = $section['recursive'] ?? false;
        $excludeDirs = $section['exclude_dirs'] ?? [];

        $finder = (new Finder)
            ->files()
            ->name('*.php')
            ->in($dir);

        if (! $recursive) {
            $finder->depth('== 0');
        }

        foreach ($excludeDirs as $exclude) {
            $finder->notPath($exclude);
        }

        $files = [];

        foreach ($finder as $file) {
            $files[$file->getRelativePathname()] = $file->getBasename('.php');
        }

        return $files;
    }

    private function scanDocSlugs(string $dir): array
    {
        $finder = (new Finder)
            ->files()
            ->name('*.md')
            ->notName('index.md')
            ->in($dir);

        $slugs = [];

        foreach ($finder as $file) {
            $slugs[$file->getBasename('.md')] = true;
        }

        return $slugs;
    }
}
