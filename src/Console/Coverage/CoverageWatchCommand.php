<?php

namespace Unusualify\Modularous\Console\Coverage;

use Illuminate\Console\Command;
use Unusualify\Modularous\Facades\Coverage;

/**
 * Watch coverage changes in real-time
 */
class CoverageWatchCommand extends Command
{
    protected $signature = 'coverage:watch
                            {--cloverName= : Name of clover file}
                            {--cloverDir= : Path to clover directory}
                            {--interval=5 : Check interval in seconds}';

    protected $description = 'Watch coverage changes in real-time';

    public function handle(): int
    {
        $this->info('👀 Watching Coverage Changes...');
        $this->line('Press Ctrl+C to stop');
        $this->newLine();

        $interval = (int) $this->option('interval');
        $cloverName = $this->option('cloverName') ?? config('modularous-coverage.clover_name');
        $cloverDir = $this->option('cloverDir') ?? config('modularous-coverage.clover_dir');

        $cloverPath = concatenate_path($cloverDir, $cloverName);
        $lastStats = null;

        while (true) {
            clearstatcache();

            if (! file_exists($cloverPath)) {
                $this->warn('⏳ Waiting for coverage file...');
                sleep($interval);

                continue;
            }

            try {
                $stats = Coverage::stats();

                if ($lastStats === null) {
                    $this->displayStats($stats);
                } elseif ($stats !== $lastStats) {
                    $this->newLine();
                    $this->info('📊 Coverage Updated:');
                    $this->displayDiff($lastStats, $stats);
                    $this->newLine();
                }

                $lastStats = $stats;

            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
            }

            sleep($interval);
        }

        return self::SUCCESS;
    }

    private function displayStats(array $stats): void
    {
        $this->line("  Uncovered Methods: <fg=yellow>{$stats['total_methods']}</>");
        $this->line("  Affected Files: <fg=cyan>{$stats['total_files']}</>");
        $this->line("  Uncovered Lines: <fg=red>{$stats['total_uncovered_lines']}</>");
    }

    private function displayDiff(array $old, array $new): void
    {
        $methodDiff = $new['total_methods'] - $old['total_methods'];
        $lineDiff = $new['total_uncovered_lines'] - $old['total_uncovered_lines'];

        $this->line($this->formatDiff('Uncovered Methods', $old['total_methods'], $new['total_methods'], $methodDiff));
        $this->line($this->formatDiff('Uncovered Lines', $old['total_uncovered_lines'], $new['total_uncovered_lines'], $lineDiff));
    }

    private function formatDiff(string $label, int $old, int $new, int $diff): string
    {
        $symbol = $diff > 0 ? '↑' : ($diff < 0 ? '↓' : '→');
        $color = $diff > 0 ? 'red' : ($diff < 0 ? 'green' : 'yellow');

        return "  {$label}: {$old} <fg={$color}>{$symbol} {$new}</> (" . abs($diff) . ')';
    }
}
