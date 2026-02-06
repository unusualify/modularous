<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Coverage Analysis Facade
 *
 * @method static \Unusualify\Modularity\Services\CoverageService setCloverPath(string $path)
 * @method static \Unusualify\Modularity\Services\CoverageService filterByFiles(array $files)
 * @method static \Unusualify\Modularity\Services\CoverageService setCoverageThreshold(float $threshold)
 * @method static \Unusualify\Modularity\Services\CoverageService skipMagicMethods(bool $skip = true)
 * @method static \Unusualify\Modularity\Services\CoverageService skipPrivateMethods(bool $skip = true)
 * @method static \Unusualify\Modularity\Services\CoverageService skipProtectedMethods(bool $skip = true)
 * @method static array analyze()
 * @method static array analyzeFile(string $filePath)
 * @method static array|null getMethodCoverage(string $filePath, string $methodName)
 * @method static array getErrors()
 * @method static bool hasErrors()
 * @method static array git(string $baseBranch = 'main')
 * @method static array uncovered(array $files = [])
 * @method static array partial(float $threshold = 50.0, array $files = [])
 * @method static string json(?array $files = null, bool $prettyPrint = true)
 * @method static bool save(string $outputPath, ?array $files = null, string $format = 'json')
 * @method static string markdown(?array $files = null)
 * @method static string html(?array $files = null)
 * @method static bool checkPR(string $baseBranch = 'main', bool $throwOnFailure = false)
 * @method static array stats(?array $files = null)
 *
 * @see \Unusualify\Modularity\Services\CoverageService
 */
class Coverage extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'coverage.service';
    }
}
