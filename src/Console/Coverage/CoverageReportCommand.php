<?php

namespace Unusualify\Modularity\Console\Coverage;

use Illuminate\Console\Command;
use Unusualify\Modularity\Facades\Coverage;

/**
 * Generate coverage reports in various formats
 */
class CoverageReportCommand extends Command
{
    protected $signature = 'coverage:report
                            {--cloverName= : Name of clover file}
                            {--cloverDir= : Path to clover directory}
                            {--output=storage/app/coverage : Output directory}
                            {--format=* : Report formats (json, markdown, html, all)}
                            {--git= : Compare against git branch}
                            {--files=* : Specific files to analyze}
                            {--threshold=0 : Minimum coverage threshold}
                            {--open : Open HTML report in browser}';

    protected $description = 'Generate coverage reports in various formats';

    public function handle(): int
    {
        $this->info('📊 Generating Coverage Reports...');
        $this->newLine();

        $outputDir = $this->option('output');

        // Create output directory
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $formats = $this->option('format');
        if (empty($formats) || in_array('all', $formats)) {
            $formats = ['json', 'markdown', 'html'];
        }

        $files = $this->option('files') ?: null;
        $git = $this->option('git');

        // Determine analysis method
        if ($git) {
            $this->line("Analyzing changes vs <fg=cyan>{$git}</>");
        } elseif ($files) {
            $this->line("Analyzing files: <fg=cyan>" . implode(', ', $files) . "</>");
        }

        $bar = $this->output->createProgressBar(count($formats));
        $bar->start();

        $generated = [];

        $coverageService = Coverage::make($this->option('cloverDir'), $this->option('cloverName'))
            ->setCoverageThreshold($this->option('threshold'));

        foreach ($formats as $format) {
            $filename = "coverage-report.{$format}";
            $path = "{$outputDir}/{$filename}";

            $coverageService->save($path, $files, $format);
            $generated[] = $path;
            $bar->advance();
            try {
            } catch (\Exception $e) {
                $this->error("\nFailed to generate {$format}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Display results
        $this->info('✅ Reports generated successfully:');
        foreach ($generated as $path) {
            $this->line("  📄 {$path}");
        }

        // Open HTML report if requested
        if ($this->option('open') && in_array('html', $formats)) {
            $htmlPath = "{$outputDir}/coverage-report.html";
            if (file_exists($htmlPath)) {
                $this->openInBrowser($htmlPath);
            }
        }

        // Display statistics
        $this->newLine();
        $this->displayStatistics($files);

        return self::SUCCESS;
        try {

        } catch (\Exception $e) {
            $this->error('❌ Report generation failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function displayStatistics(?array $files): void
    {
        $stats = Coverage::stats($files);

        $this->info('📈 Coverage Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Uncovered Methods', $stats['total_methods']],
                ['Affected Files', $stats['total_files']],
                ['Total Lines', $stats['total_lines']],
                ['Uncovered Lines', $stats['total_uncovered_lines']],
            ]
        );
    }

    private function openInBrowser(string $path): void
    {
        $command = match(PHP_OS_FAMILY) {
            'Darwin' => 'open',
            'Windows' => 'start',
            'Linux' => 'xdg-open',
            default => null,
        };

        if ($command) {
            shell_exec("{$command} " . escapeshellarg($path));
            $this->info("🌐 Opened in browser");
        }
    }
}
