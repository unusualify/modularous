<?php

namespace Unusualify\Modularity\Console\Coverage;

use Illuminate\Console\Command;
use Unusualify\Modularity\Facades\Coverage;

/**
 * Analyze coverage and display results
 */
class CoverageAnalyzeCommand extends Command
{
    protected $signature = 'coverage:analyze
                            {--cloverName= : Name of clover file}
                            {--cloverDir= : Path to clover directory}
                            {--files=* : Specific files to analyze}
                            {--threshold=0 : Coverage threshold percentage}
                            {--git= : Compare against git branch}
                            {--skip-magic : Skip magic methods}
                            {--format=table : Output format (table, json, list)}';

    protected $description = 'Analyze code coverage and identify uncovered methods';

    public function handle(): int
    {
        $this->info('🔍 Analyzing Coverage...');
        $this->newLine();

        $coverageService = Coverage::make($this->option('cloverDir'), $this->option('cloverName'));

        // Apply configurations
        if ($this->option('threshold')) {
            $coverageService->setCoverageThreshold((float) $this->option('threshold'));
        }

        if ($this->option('skip-magic')) {
            $coverageService->skipMagicMethods(true);
        }

        // Get results
        if ($git = $this->option('git')) {
            $this->line("Comparing against branch: <fg=cyan>{$git}</>");
            $results = Coverage::git($git);
        } elseif ($files = $this->option('files')) {
            $this->line('Analyzing files: <fg=cyan>' . implode(', ', $files) . '</>');
            $coverageService->filterByFiles($files);
            $results = $coverageService->analyze();
        } else {
            $results = $coverageService->analyze();
        }

        // Display results
        if (empty($results)) {
            $this->info('✅ All methods meet coverage requirements!');

            return self::SUCCESS;
        }

        $this->warn('⚠️  Found ' . count($results) . ' methods below threshold');
        $this->newLine();

        match ($this->option('format')) {
            'json' => $this->displayJson($results),
            'list' => $this->displayList($results),
            default => $this->displayTable($results),
        };

        return self::FAILURE;
        try {

        } catch (\Exception $e) {
            $this->error('❌ Analysis failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayTable(array $results): void
    {
        // dd($results[0]);
        $this->table(
            ['Class', 'Method', 'Coverage', 'Uncovered Lines'],
            array_map(fn ($m) => [
                $this->truncate($m['class'], 40),
                $m['method'],
                $this->formatCoverage($m['coverage']),
                "{$m['lines']['uncovered']}/{$m['lines']['total']}",
            ], $results)
        );
    }

    private function displayList(array $results): void
    {
        foreach ($results as $method) {
            $this->line("❌ <fg=red>{$method['class']}::{$method['method']}()</>");
            $this->line("   Coverage: {$method['coverage']}%");
            $this->line("   File: {$method['file']}");
            $this->newLine();
        }
    }

    private function displayJson(array $results): void
    {
        $this->line(json_encode($results, JSON_PRETTY_PRINT));
    }

    private function formatCoverage(float $coverage): string
    {
        $color = $coverage === 0 ? 'red' : ($coverage < 50 ? 'yellow' : 'green');

        return "<fg={$color}>{$coverage}%</>";
    }

    private function truncate(string $text, int $length): string
    {
        return mb_strlen($text) > $length
            ? mb_substr($text, 0, $length - 3) . '...'
            : $text;
    }
}
