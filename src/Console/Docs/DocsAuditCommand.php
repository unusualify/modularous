<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Console\Docs;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class DocsAuditCommand extends Command
{
    protected $signature = 'modularity:docs:audit
        {--section= : Filter to a specific section label (e.g. "Entities")}
        {--fail-on-missing : Exit with code 1 when undocumented files exist}';

    protected $description = 'Audit source files against documentation pages and report gaps.';

    /**
     * Registry of source-to-docs mappings.
     *
     * Each entry maps a source directory to its expected documentation directory.
     * The audit scans each source dir for .php files, converts the class name to
     * kebab-case, and checks whether a matching .md file exists in the docs dir.
     *
     * To track a new section, add an entry here.
     */
    public static function sections(): array
    {
        return [
            [
                'label' => 'Entities',
                'source' => 'src/Entities',
                'docs' => 'docs/src/pages/system-reference/backend/entities',
                'recursive' => false,
                'exclude_dirs' => ['Enums', 'Scopes', 'Traits', 'Translations', 'Casts', 'Observers', 'Mutators'],
            ],
            [
                'label' => 'Entity Enums',
                'source' => 'src/Entities/Enums',
                'docs' => 'docs/src/pages/system-reference/backend/entity-enums',
            ],
            [
                'label' => 'Entity Scopes',
                'source' => 'src/Entities/Scopes',
                'docs' => 'docs/src/pages/system-reference/backend/entity-scopes',
            ],
            [
                'label' => 'Entity Traits',
                'source' => 'src/Entities/Traits',
                'docs' => 'docs/src/pages/system-reference/backend/entity-traits',
                'recursive' => true,
            ],
            [
                'label' => 'Controllers',
                'source' => 'src/Http/Controllers',
                'docs' => 'docs/src/pages/system-reference/backend/controllers',
                'recursive' => true,
                'exclude_dirs' => ['Traits'],
            ],
            [
                'label' => 'Middleware',
                'source' => 'src/Http/Middleware',
                'docs' => 'docs/src/pages/system-reference/backend/middleware',
            ],
            [
                'label' => 'HTTP Requests',
                'source' => 'src/Http/Requests',
                'docs' => 'docs/src/pages/system-reference/backend/http-requests',
            ],
            [
                'label' => 'View Composers',
                'source' => 'src/Http/ViewComposers',
                'docs' => 'docs/src/pages/system-reference/backend/view-composers',
            ],
            [
                'label' => 'Facades',
                'source' => 'src/Facades',
                'docs' => 'docs/src/pages/system-reference/backend/facades',
            ],
            [
                'label' => 'Helpers',
                'source' => 'src/Helpers',
                'docs' => 'docs/src/pages/system-reference/backend/helpers',
            ],
            [
                'label' => 'Providers',
                'source' => 'src/Providers',
                'docs' => 'docs/src/pages/system-reference/backend/providers',
            ],
            [
                'label' => 'Events',
                'source' => 'src/Events',
                'docs' => 'docs/src/pages/system-reference/backend/events',
            ],
            [
                'label' => 'Notifications',
                'source' => 'src/Notifications',
                'docs' => 'docs/src/pages/system-reference/backend/notifications',
            ],
            [
                'label' => 'Generators',
                'source' => 'src/Generators',
                'docs' => 'docs/src/pages/system-reference/backend/generators',
            ],
            [
                'label' => 'Hydrates',
                'source' => 'src/Hydrates',
                'docs' => 'docs/src/pages/system-reference/backend/hydrates',
                'recursive' => true,
            ],
            [
                'label' => 'Core Services',
                'source' => 'src/Services',
                'docs' => 'docs/src/pages/system-reference/backend/core-services',
                'recursive' => true,
            ],
            [
                'label' => 'Package Traits',
                'source' => 'src/Traits',
                'docs' => 'docs/src/pages/system-reference/backend/package-traits',
                'recursive' => true,
            ],
            [
                'label' => 'Contracts',
                'source' => 'src/Contracts',
                'docs' => 'docs/src/pages/system-reference/backend/contracts',
                'recursive' => true,
            ],
            [
                'label' => 'Exceptions',
                'source' => 'src/Exceptions',
                'docs' => 'docs/src/pages/system-reference/backend/exceptions',
            ],
            [
                'label' => 'Transformers',
                'source' => 'src/Transformers',
                'docs' => 'docs/src/pages/system-reference/backend/transformers',
            ],
            [
                'label' => 'Activators',
                'source' => 'src/Activators',
                'docs' => 'docs/src/pages/system-reference/backend/activators',
            ],
            [
                'label' => 'Brokers',
                'source' => 'src/Brokers',
                'docs' => 'docs/src/pages/system-reference/backend/brokers',
            ],
            [
                'label' => 'Repository Traits',
                'source' => 'src/Repositories/Traits',
                'docs' => 'docs/src/pages/system-reference/backend/repository-traits',
                'recursive' => true,
            ],
        ];
    }

    public function handle(): int
    {
        $packageRoot = $this->resolvePackageRoot();
        $filterSection = $this->option('section');
        $failOnMissing = $this->option('fail-on-missing');

        $this->components->info('Modularous Documentation Audit');
        $this->line('  Package root: ' . $packageRoot);
        $this->newLine();

        $totalSource = 0;
        $totalDocumented = 0;
        $allMissing = [];
        $summaryRows = [];

        foreach (static::sections() as $section) {
            if ($filterSection && ! Str::contains(Str::lower($section['label']), Str::lower($filterSection))) {
                continue;
            }

            $sourceDir = $packageRoot . '/' . $section['source'];
            $docsDir = $packageRoot . '/' . $section['docs'];

            if (! is_dir($sourceDir)) {
                continue;
            }

            $sourceFiles = $this->scanSourceFiles($sourceDir, $section);
            $docFiles = is_dir($docsDir) ? $this->scanDocFiles($docsDir) : [];

            $missing = [];

            foreach ($sourceFiles as $relPath => $className) {
                $expectedSlug = Str::kebab($className);

                if (! isset($docFiles[$expectedSlug])) {
                    $missing[] = [
                        'class' => $className,
                        'source' => $section['source'] . '/' . $relPath,
                        'expected' => $expectedSlug . '.md',
                    ];
                }
            }

            $srcCount = count($sourceFiles);
            $docCount = $srcCount - count($missing);
            $totalSource += $srcCount;
            $totalDocumented += $docCount;

            $status = count($missing) === 0 ? '<fg=green>✓ Complete</>' : '<fg=red>✗ ' . count($missing) . ' missing</>';

            $summaryRows[] = [
                $section['label'],
                (string) $srcCount,
                (string) $docCount,
                $status,
            ];

            if (count($missing) > 0) {
                $allMissing[$section['label']] = $missing;
            }
        }

        $this->table(
            ['Section', 'Source Files', 'Documented', 'Status'],
            $summaryRows,
        );

        $this->newLine();
        $coverage = $totalSource > 0 ? round(($totalDocumented / $totalSource) * 100) : 100;
        $this->components->info(sprintf(
            'Coverage: %d/%d files (%d%%)',
            $totalDocumented,
            $totalSource,
            $coverage,
        ));

        if (! empty($allMissing)) {
            $this->newLine();
            $this->components->warn('Missing documentation:');

            foreach ($allMissing as $sectionLabel => $files) {
                $this->newLine();
                $this->components->twoColumnDetail("<fg=yellow>{$sectionLabel}</>", count($files) . ' file(s)');

                foreach ($files as $file) {
                    $this->components->bulletList([
                        "<fg=white>{$file['class']}</> — <fg=gray>{$file['source']}</>",
                    ]);
                }
            }

            if ($failOnMissing) {
                $this->newLine();
                $this->components->error(sprintf(
                    '%d source file(s) have no documentation.',
                    $totalSource - $totalDocumented,
                ));

                return self::FAILURE;
            }
        } else {
            $this->newLine();
            $this->components->info('All tracked source files are documented.');
        }

        return self::SUCCESS;
    }

    /**
     * Scan a source directory for PHP files and return [relativePath => className].
     */
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
            $className = $file->getBasename('.php');
            $relPath = $file->getRelativePathname();
            $files[$relPath] = $className;
        }

        return $files;
    }

    /**
     * Scan a docs directory recursively for .md files and return [slug => true].
     * Excludes index.md files since those are section overviews.
     */
    private function scanDocFiles(string $dir): array
    {
        $finder = (new Finder)
            ->files()
            ->name('*.md')
            ->notName('index.md')
            ->in($dir);

        $slugs = [];

        foreach ($finder as $file) {
            $slug = $file->getBasename('.md');
            $slugs[$slug] = true;
        }

        return $slugs;
    }

    private function resolvePackageRoot(): string
    {
        $default = base_path('packages/modularous');

        if (is_dir($default)) {
            return $default;
        }

        return realpath(__DIR__ . '/../../..') ?: $default;
    }
}
