<?php

namespace Unusualify\Modularity\Console\Coverage;

use Illuminate\Console\Command;
use Unusualify\Modularity\Facades\Coverage;

/**
 * Check PR coverage requirements
 */
class CoveragePRCheckCommand extends Command
{
    protected $signature = 'coverage:pr:check
                            {--cloverName= : Name of clover file}
                            {--cloverDir= : Path to clover directory}
                            {--branch=main : Base branch to compare}
                            {--threshold=0 : Minimum coverage threshold}
                            {--fail : Exit with error code if uncovered methods found}';

    protected $description = 'Check if PR meets coverage requirements';

    public function handle(): int
    {
        $this->info('🔍 Checking PR Coverage...');
        $this->newLine();

        try {
            $branch = $this->option('branch');
            $threshold = (float) $this->option('threshold');

            $cloverName = $this->option('cloverName') ?? config('modularity-coverage.clover_name');
            $cloverDir = $this->option('cloverDir') ?? config('modularity-coverage.clover_dir');
            $cloverPath = concatenate_path($cloverDir, $cloverName);

            $results = Coverage::make($cloverDir, $cloverName)
                ->setCoverageThreshold($threshold)
                ->git($branch);

            $this->error('❌ Coverage check failed');
            $this->newLine();

            $this->warn('Found ' . count($results) . " methods below {$threshold}% coverage:");

            foreach (array_slice($results, 0, 10) as $method) {
                $this->line("  • {$method['class']}::{$method['method']}() - {$method['coverage']}%");
            }

            if (count($results) > 10) {
                $remaining = count($results) - 10;
                $this->line("  ... and {$remaining} more");
            }

            $this->newLine();
            $this->comment('💡 Run `php artisan coverage:report --git='
                . $branch
                . ($threshold > 0 ? ' --threshold=' . $threshold : '')
                . ($this->option('cloverName') ? ' --cloverName=' . $this->option('cloverName') : '')
                . ($this->option('cloverDir') ? ' --cloverDir=' . $this->option('cloverDir') : '')
                . '` for detailed analysis');

            return $this->option('fail') ? self::FAILURE : self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Check failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
