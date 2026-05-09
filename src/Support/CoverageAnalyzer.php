<?php

namespace Unusualify\Modularous\Support;

use InvalidArgumentException;
use RuntimeException;
use SimpleXMLElement;

/**
 * Standalone Coverage Analyzer
 *
 * Parses PHPUnit clover.xml coverage reports and identifies methods with low or zero coverage.
 * Can filter by specific files or analyze all files in the report.
 *
 * @example
 * ```php
 * $analyzer = new CoverageAnalyzer('/path/to/coverage', 'clover.xml');
 * $uncovered = $analyzer->analyze();
 * ```
 */
class CoverageAnalyzer
{
    private string $cloverDir;

    private string $cloverPath;

    private ?SimpleXMLElement $xml = null;

    private array $fileFilters = [];

    private array $errors = [];

    // Configuration
    private bool $skipMagicMethods = true;

    private bool $skipPrivateMethods = false;

    private bool $skipProtectedMethods = false;

    private float $coverageThreshold = 0.0;

    /**
     * @param string $cloverDir Path to clover directory
     * @param string $cloverName Name of clover file
     *
     * @throws InvalidArgumentException if file doesn't exist
     */
    public function __construct(string $cloverDir, string $cloverName)
    {
        $cloverPath = concatenate_path($cloverDir, $cloverName);

        if (! file_exists($cloverPath)) {
            throw new InvalidArgumentException("Coverage file not found: {$cloverPath}");
        }

        if (! is_readable($cloverPath)) {
            throw new InvalidArgumentException("Coverage file is not readable: {$cloverPath}");
        }

        $this->cloverPath = $cloverPath;
        $this->cloverDir = $cloverDir;

    }

    /**
     * Set files to analyze (optional filter)
     *
     * @param array $files List of file paths to analyze
     */
    public function filterByFiles(array $files): self
    {
        $this->fileFilters = array_map(function ($file) {
            return str_replace('\\', '/', $file);
        }, $files);

        return $this;
    }

    /**
     * Set coverage threshold (only report methods below this percentage)
     *
     * @param float $threshold Coverage percentage (0.0 - 100.0)
     */
    public function setCoverageThreshold(float $threshold): self
    {
        if ($threshold < 0.0 || $threshold > 100.0) {
            throw new InvalidArgumentException('Threshold must be between 0.0 and 100.0');
        }

        $this->coverageThreshold = $threshold;

        return $this;
    }

    /**
     * Configure whether to skip magic methods (__construct, __toString, etc.)
     */
    public function skipMagicMethods(bool $skip = true): self
    {
        $this->skipMagicMethods = $skip;

        return $this;
    }

    /**
     * Configure whether to skip private methods
     */
    public function skipPrivateMethods(bool $skip = true): self
    {
        $this->skipPrivateMethods = $skip;

        return $this;
    }

    /**
     * Configure whether to skip protected methods
     */
    public function skipProtectedMethods(bool $skip = true): self
    {
        $this->skipProtectedMethods = $skip;

        return $this;
    }

    /**
     * Analyze coverage and return methods below threshold
     *
     * @return array Array of methods with details
     *
     * @throws RuntimeException if XML parsing fails
     */
    public function analyze(): array
    {
        $this->loadXML();

        $results = [];
        $files = $this->getFilesToAnalyze();

        foreach ($files as $file) {
            $filePath = $this->normalizeFilePath((string) $file['name']);

            // Skip if file filter is set and this file isn't included
            if (! $this->shouldAnalyzeFile($filePath)) {
                continue;
            }

            // Get class information
            $classes = $file->xpath('.//class');
            $classMap = $this->buildClassMap($classes);

            // Get all method lines (direct children of file element)
            $methodLines = $file->xpath('./line[@type="method"]');

            foreach ($methodLines as $methodLine) {
                $methodData = $this->analyzeMethodLine($methodLine, $file, $classMap, $filePath);

                if ($methodData !== null) {
                    $results[] = $methodData;
                }
            }
        }

        // dd(array_map(function($file) {
        //     return $file['class'] . '->' . $file['method'];
        // }, $results));

        return $results;
    }

    /**
     * Analyze a single file and return methods below threshold
     *
     * @param string $filePath Specific file to analyze
     * @return array Methods in this file below threshold
     */
    public function analyzeFile(string $filePath): array
    {
        $this->loadXML();

        $normalizedPath = $this->normalizeFilePath($filePath);
        $results = [];

        $files = $this->xml->xpath('//file');

        foreach ($files as $file) {
            $currentPath = $this->normalizeFilePath((string) $file['name']);

            if (! $this->pathsMatch($currentPath, $normalizedPath)) {
                continue;
            }

            // Get class information
            $classes = $file->xpath('.//class');
            $classMap = $this->buildClassMap($classes);

            // Get all method lines
            $methodLines = $file->xpath('./line[@type="method"]');

            foreach ($methodLines as $methodLine) {
                $methodData = $this->analyzeMethodLine($methodLine, $file, $classMap, $currentPath);

                if ($methodData !== null) {
                    $results[] = $methodData;
                }
            }
        }

        return $results;
    }

    /**
     * Get coverage statistics for a specific method
     *
     * @param string $filePath File containing the method
     * @param string $methodName Method to analyze
     * @return array|null Coverage details or null if not found
     */
    public function getMethodCoverage(string $filePath, string $methodName): ?array
    {
        $this->loadXML();

        $normalizedPath = $this->normalizeFilePath($filePath);
        $files = $this->xml->xpath('//file');

        foreach ($files as $file) {
            $currentPath = $this->normalizeFilePath((string) $file['name']);

            if (! $this->pathsMatch($currentPath, $normalizedPath)) {
                continue;
            }

            $methodLines = $file->xpath("./line[@type='method' and @name='{$methodName}']");

            if (! empty($methodLines)) {
                $methodLine = $methodLines[0];
                $lineInfo = $this->getMethodLineDetails($methodLine, $file);

                return [
                    'name' => $methodName,
                    'coverage' => $lineInfo['coverage'],
                    'execution_count' => (int) $methodLine['count'],
                    'visibility' => (string) $methodLine['visibility'],
                    'complexity' => (int) $methodLine['complexity'],
                    'crap' => (float) $methodLine['crap'],
                    'line' => (int) $methodLine['num'],
                    'lines' => $lineInfo['lines'],
                ];
            }
        }

        return null;
    }

    /**
     * Get overall coverage statistics
     *
     * @return array Overall statistics
     */
    public function getOverallStatistics(): array
    {
        $this->loadXML();

        $projectMetrics = $this->xml->xpath('//project/metrics');

        if (empty($projectMetrics)) {
            return [];
        }

        $metrics = $projectMetrics[0];

        return [
            'files' => (int) $metrics['files'],
            'classes' => (int) $metrics['classes'],
            'methods' => (int) $metrics['methods'],
            'covered_methods' => (int) $metrics['coveredmethods'],
            'statements' => (int) $metrics['statements'],
            'covered_statements' => (int) $metrics['coveredstatements'],
            'elements' => (int) $metrics['elements'],
            'covered_elements' => (int) $metrics['coveredelements'],
            'method_coverage_percent' => $this->calculatePercentage(
                (int) $metrics['coveredmethods'],
                (int) $metrics['methods']
            ),
            'statement_coverage_percent' => $this->calculatePercentage(
                (int) $metrics['coveredstatements'],
                (int) $metrics['statements']
            ),
            'element_coverage_percent' => $this->calculatePercentage(
                (int) $metrics['coveredelements'],
                (int) $metrics['elements']
            ),
        ];
    }

    /**
     * Get all errors encountered during analysis
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if analyzer has any errors
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    // ==================== PRIVATE METHODS ====================

    /**
     * Load and parse the XML file
     */
    private function loadXML(): void
    {
        if ($this->xml !== null) {
            return; // Already loaded
        }

        libxml_use_internal_errors(true);

        try {
            // 1. Load into a temporary variable first
            $xml = simplexml_load_file($this->cloverPath);

            if ($xml === false) {
                $errors = libxml_get_errors();
                libxml_clear_errors();

                $errorMessages = array_map(fn ($error) => trim($error->message), $errors);

                throw new RuntimeException(
                    'Failed to parse coverage XML: ' . implode(', ', $errorMessages)
                );
            }
            $this->xml = $xml;
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to parse coverage XML: ' . $e->getMessage());
        }
    }

    /**
     * Get files to analyze from XML
     */
    private function getFilesToAnalyze(): array
    {
        $files = $this->xml->xpath('//file');

        if ($files === false || empty($files)) {
            $this->errors[] = 'No files found in coverage report';

            return [];
        }

        return $files;
    }

    /**
     * Build a map of classes by line number range
     */
    private function buildClassMap(array $classes): array
    {
        $classMap = [];

        foreach ($classes as $class) {
            $className = (string) $class['name'];
            $namespace = (string) $class['namespace'];
            $fullClassName = $namespace && $namespace !== 'global'
                ? "{$namespace}\\{$className}"
                : $className;

            $classMap[] = [
                'name' => $fullClassName,
                'namespace' => $namespace,
            ];
        }

        return $classMap;
    }

    /**
     * Determine which class a method belongs to
     */
    private function getClassForMethod(array $classMap): ?string
    {
        // In most cases, there's only one class per file
        return count($classMap) > 1 ? null : $classMap[0]['name'];
    }

    /**
     * Check if a file should be analyzed based on filters
     */
    private function shouldAnalyzeFile(string $filePath): bool
    {
        if (empty($this->fileFilters)) {
            return true; // No filter, analyze all
        }

        foreach ($this->fileFilters as $filter) {
            if ($this->pathsMatch($filePath, $filter)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Analyze a single method line
     */
    private function analyzeMethodLine(
        SimpleXMLElement $methodLine,
        SimpleXMLElement $file,
        array $classMap,
        string $filePath
    ): ?array {
        $methodName = (string) $methodLine['name'];
        $visibility = (string) $methodLine['visibility'];

        // Apply filters
        if ($this->shouldSkipMethod($methodName, $visibility)) {
            return null;
        }

        // Calculate coverage and get line details
        $lineInfo = $this->getMethodLineDetails($methodLine, $file);
        $coverage = $lineInfo['coverage'];

        // // Check against threshold
        // if($filePath === '/var/www/b2press-app/packages/modularous/src/Entities/Observers/PriceableObserver.php') {
        //     dump($coverage, $this->coverageThreshold);
        // }
        if ($coverage > $this->coverageThreshold) {
            return null;
        }

        $className = $this->getClassForMethod($classMap);

        return [
            'class' => $className ?? 'Unknown',
            'method' => $methodName,
            'file' => $filePath,
            'line' => (int) $methodLine['num'],
            'visibility' => $visibility,
            'complexity' => (int) $methodLine['complexity'],
            'crap' => (float) $methodLine['crap'],
            'coverage' => $coverage,
            'execution_count' => (int) $methodLine['count'],
            'lines' => $lineInfo['lines'],
        ];
    }

    /**
     * Get detailed line information and coverage for a method
     */
    private function getMethodLineDetails(
        SimpleXMLElement $methodLine,
        SimpleXMLElement $file
    ): array {
        $executionCount = (int) $methodLine['count'];

        // Find statement lines that belong to this method
        $methodLineNum = (int) $methodLine['num'];
        $allLines = $file->xpath('./line');

        $methodStatements = [];
        $collectStatements = false;

        foreach ($allLines as $line) {
            $lineNum = (int) $line['num'];
            $lineType = (string) $line['type'];

            // Start collecting when we hit our method
            if ($lineType === 'method' && $lineNum === $methodLineNum) {
                $collectStatements = true;

                continue;
            }

            // Stop collecting when we hit the next method
            if ($collectStatements && $lineType === 'method' && $lineNum !== $methodLineNum) {
                break;
            }

            // Collect statement lines
            if ($collectStatements && $lineType === 'stmt') {
                $methodStatements[] = $line;
            }
        }

        // Build line details
        $lineInfo = [
            'total' => count($methodStatements),
            'covered' => 0,
            'uncovered' => 0,
            'details' => [],
        ];

        foreach ($methodStatements as $stmt) {
            $lineNum = (int) $stmt['num'];
            $count = (int) $stmt['count'];
            $isCovered = $count > 0;

            if ($isCovered) {
                $lineInfo['covered']++;
            } else {
                $lineInfo['uncovered']++;
            }

            $lineInfo['details'][] = [
                'number' => $lineNum,
                'executed' => $count,
                'covered' => $isCovered,
            ];
        }

        // Calculate coverage percentage
        $coverage = 0.0;
        if ($executionCount === 0) {
            $coverage = 0.0;
        } elseif (empty($methodStatements)) {
            $coverage = 100.0; // Method executed with no statements
        } else {
            $coverage = round(($lineInfo['covered'] / $lineInfo['total']) * 100, 2);
        }

        return [
            'coverage' => $coverage,
            'lines' => $lineInfo,
        ];
    }

    /**
     * Check if method should be skipped based on configuration
     */
    private function shouldSkipMethod(string $methodName, string $visibility): bool
    {
        // Skip magic methods
        if ($this->skipMagicMethods && str_starts_with($methodName, '__')) {
            return true;
        }

        // Skip based on visibility
        if ($this->skipPrivateMethods && $visibility === 'private') {
            return true;
        }

        if ($this->skipProtectedMethods && $visibility === 'protected') {
            return true;
        }

        return false;
    }

    /**
     * Calculate percentage
     */
    private function calculatePercentage(int $covered, int $total): float
    {
        return $total === 0 ? 0.0 : round(($covered / $total) * 100, 2);
    }

    /**
     * Normalize file paths for comparison
     */
    private function normalizeFilePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Check if two paths match (handles partial paths)
     */
    private function pathsMatch(string $path1, string $path2): bool
    {
        $path1 = $this->normalizeFilePath($path1);
        $path2 = $this->normalizeFilePath($path2);

        // Exact match
        if ($path1 === $path2) {
            return true;
        }

        // Partial match (path contains filter)
        if (str_contains($path1, $path2) || str_contains($path2, $path1)) {
            return true;
        }

        return false;
    }
}
