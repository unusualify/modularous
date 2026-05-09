<?php

namespace Unusualify\Modularous\Console\Coverage;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Unusualify\Modularous\Facades\Coverage;

/**
 * Generate test files for uncovered methods using AI or templates
 */
class CoverageTestGeneratorCommand extends Command
{
    protected $signature = 'coverage:generate-tests
                            {--cloverName= : Name of clover file}
                            {--cloverDir= : Path to clover directory}
                            {--files=* : Specific files to analyze}
                            {--threshold=0 : Coverage threshold percentage}
                            {--git= : Compare against git branch}
                            {--output=tests/Generated : Output directory for test files}
                            {--ai : Use AI to generate tests (requires API setup)}
                            {--model= : AI model to use (will be prompted if not provided)}
                            {--api-key= : API key for the selected AI provider}
                            {--template=phpunit : Test template (phpunit, pest)}
                            {--interactive : Review each generated test}
                            {--delay=0 : Delay in seconds between API calls to avoid rate limits}
                            {--dry-run : Show what would be generated without writing files}';

    protected $description = 'Generate test files for uncovered methods';

    private array $generatedTests = [];

    private int $successCount = 0;

    private int $failureCount = 0;

    private const AI_PROVIDERS = [
        'anthropic' => [
            'name' => 'Anthropic Claude',
            'models' => [
                'claude-sonnet-4-5-20250929' => 'Claude Sonnet 4.5 (Recommended)',
                'claude-opus-4-5-20251101' => 'Claude Opus 4.5 (Most Capable)',
                'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5 (Fastest)',
            ],
            'endpoint' => 'https://api.anthropic.com/v1/messages',
            'free_tier' => false,
            'config_key' => 'anthropic_api_key',
            'env_key' => 'ANTHROPIC_API_KEY',
        ],
        'gemini' => [
            'name' => 'Google Gemini',
            'models' => [
                'gemini-3-flash-preview' => 'Gemini 3 Flash (Latest, Fast Frontier Intelligence)',
                'gemini-3-pro-preview' => 'Gemini 3 Pro (Most Capable, Best Reasoning)',
                'gemini-2.5-flash' => 'Gemini 2.5 Flash (Recommended, Stable, Free)',
                'gemini-2.5-pro' => 'Gemini 2.5 Pro (Advanced Reasoning)',
                'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite (Fast & Efficient)',
                'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash (Experimental)',
                'gemini-1.5-flash' => 'Gemini 1.5 Flash (Stable Legacy)',
                'gemini-1.5-flash-002' => 'Gemini 1.5 Flash 002 (Latest 1.5)',
                'gemini-1.5-flash-8b-001' => 'Gemini 1.5 Flash 8B (Lightweight)',
                'gemini-1.5-pro' => 'Gemini 1.5 Pro (Legacy Pro)',
                'gemini-1.5-pro-002' => 'Gemini 1.5 Pro 002 (Latest Legacy Pro)',
            ],
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
            'free_tier' => true,
            'config_key' => 'gemini_api_key',
            'env_key' => 'GEMINI_API_KEY',
        ],
        'openai' => [
            'name' => 'OpenAI',
            'models' => [
                'gpt-4o' => 'GPT-4o (Most Capable)',
                'gpt-4o-mini' => 'GPT-4o Mini (Cost Effective)',
                'gpt-4-turbo' => 'GPT-4 Turbo',
            ],
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'free_tier' => false,
            'config_key' => 'openai_api_key',
            'env_key' => 'OPENAI_API_KEY',
        ],
        'ollama' => [
            'name' => 'Ollama (Local)',
            'models' => [
                'llama3.1:8b' => 'Llama 3.1 8B (Local)',
                'codellama:13b' => 'Code Llama 13B (Local)',
                'qwen2.5-coder:7b' => 'Qwen 2.5 Coder 7B (Local)',
            ],
            'endpoint' => 'http://localhost:11434/api/generate',
            'free_tier' => true,
            'config_key' => 'ollama_endpoint',
            'env_key' => 'OLLAMA_ENDPOINT',
        ],
    ];

    private ?string $selectedProvider = null;

    private ?string $selectedModel = null;

    private ?string $apiKey = null;

    public function handle(): int
    {
        $this->info('🧪 Generating Tests for Uncovered Methods...');
        $this->newLine();

        try {
            // Get uncovered methods
            $uncovered = $this->getUncoveredMethods();

            if (empty($uncovered)) {
                $this->info('✅ All methods are covered! No tests to generate.');

                return self::SUCCESS;
            }

            $this->warn('Found ' . count($uncovered) . ' uncovered methods');
            $this->newLine();

            // Setup AI if requested
            if ($this->option('ai')) {
                $this->setupAI();
            }

            // Group by file for better organization
            $grouped = $this->groupByFile($uncovered);

            // Generate tests
            $bar = $this->output->createProgressBar(count($uncovered));
            $bar->start();

            $fileIndex = 0;
            foreach ($grouped as $file => $methods) {
                $this->generateTestsForFile($file, $methods, $bar);

                // Add delay between API calls if specified
                if ($this->option('ai') && $this->option('delay') > 0) {
                    $fileIndex++;
                    if ($fileIndex < count($grouped)) {
                        $delay = (int) $this->option('delay');
                        sleep($delay);
                    }
                }
            }

            $bar->finish();
            $this->newLine(2);

            // Display summary
            $this->displaySummary();

            return $this->failureCount > 0 ? self::FAILURE : self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Test generation failed: ' . $e->getMessage());
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    private function setupAI(): void
    {
        $this->info('🤖 Setting up AI for test generation...');
        $this->newLine();

        // Check if model is provided via option
        $modelOption = $this->option('model');

        if ($modelOption) {
            // Parse provider and model from option (format: provider/model or just model)
            $this->parseModelOption($modelOption);
        } else {
            // Interactive selection
            $this->selectAIProvider();
            $this->selectAIModel();
        }

        // Get API key
        $this->setupAPIKey();

        $this->newLine();
        $this->info("✓ Using {$this->selectedProvider} with model: {$this->selectedModel}");
        $this->newLine();
    }

    private function parseModelOption(string $modelOption): void
    {
        // Check if format is provider/model
        if (str_contains($modelOption, '/')) {
            [$provider, $model] = explode('/', $modelOption, 2);

            if (! isset(self::AI_PROVIDERS[$provider])) {
                throw new \InvalidArgumentException("Unknown AI provider: {$provider}");
            }

            $this->selectedProvider = $provider;
            $this->selectedModel = $model;

            // Validate model exists for provider
            if (! isset(self::AI_PROVIDERS[$provider]['models'][$model])) {
                $this->warn("Model {$model} not in predefined list, but will attempt to use it.");
            }
        } else {
            // Try to find model in all providers
            foreach (self::AI_PROVIDERS as $provider => $config) {
                if (isset($config['models'][$modelOption])) {
                    $this->selectedProvider = $provider;
                    $this->selectedModel = $modelOption;

                    return;
                }
            }

            throw new \InvalidArgumentException(
                "Model {$modelOption} not found. Use format 'provider/model' or select from available models."
            );
        }
    }

    private function selectAIProvider(): void
    {
        $this->info('📋 Available AI Providers:');
        $this->newLine();

        $choices = [];
        $index = 1;

        foreach (self::AI_PROVIDERS as $key => $provider) {
            $freeTier = $provider['free_tier'] ? ' <fg=green>(FREE)</>' : '';
            $choices[$index] = "{$provider['name']}{$freeTier}";
            $this->line("  [{$index}] {$choices[$index]}");
            $index++;
        }

        $this->newLine();

        $selection = $this->ask('Select AI provider', '2');
        $selection = (int) $selection;

        if (! isset($choices[$selection])) {
            throw new \InvalidArgumentException('Invalid provider selection');
        }

        $providerKeys = array_keys(self::AI_PROVIDERS);
        $this->selectedProvider = $providerKeys[$selection - 1];
    }

    private function selectAIModel(): void
    {
        $provider = self::AI_PROVIDERS[$this->selectedProvider];

        $this->newLine();
        $this->info("📋 Available Models for {$provider['name']}:");
        $this->newLine();

        $choices = [];
        $index = 1;

        foreach ($provider['models'] as $modelKey => $modelName) {
            $choices[$index] = $modelKey;
            $this->line("  [{$index}] {$modelName}");
            $index++;
        }

        $this->newLine();

        $selection = $this->ask('Select model', '1');
        $selection = (int) $selection;

        if (! isset($choices[$selection])) {
            throw new \InvalidArgumentException('Invalid model selection');
        }

        $this->selectedModel = $choices[$selection];
    }

    private function setupAPIKey(): void
    {
        $provider = self::AI_PROVIDERS[$this->selectedProvider];

        // Check if API key provided via option
        if ($apiKey = $this->option('api-key')) {
            $this->apiKey = $apiKey;

            return;
        }

        // Check config file
        $configKey = "modularous-coverage.{$provider['config_key']}";
        if ($apiKey = config($configKey)) {
            $this->apiKey = $apiKey;
            $this->line("Using API key from config: {$configKey}");

            return;
        }

        // Check environment variable
        if ($apiKey = env($provider['env_key'])) {
            $this->apiKey = $apiKey;
            $this->line("Using API key from environment: {$provider['env_key']}");

            return;
        }

        // Ollama doesn't require API key
        if ($this->selectedProvider === 'ollama') {
            $this->apiKey = 'local';

            return;
        }

        // Prompt user for API key
        $this->newLine();
        $this->warn('API key not found in config or environment.');

        if ($provider['free_tier']) {
            $this->info('💡 Get a free API key at:');
            $this->line($this->getAPIKeyURL($this->selectedProvider));
        }

        $this->newLine();
        $apiKey = $this->secret("Enter your {$provider['name']} API key");

        if (! $apiKey) {
            throw new \RuntimeException(
                "API key required for {$provider['name']}. " .
                "Set {$provider['env_key']} in .env or configure {$configKey}"
            );
        }

        $this->apiKey = $apiKey;
    }

    private function getAPIKeyURL(string $provider): string
    {
        return match ($provider) {
            'gemini' => 'https://makersuite.google.com/app/apikey',
            'anthropic' => 'https://console.anthropic.com/settings/keys',
            'openai' => 'https://platform.openai.com/api-keys',
            'ollama' => 'http://localhost:11434',
            default => '',
        };
    }

    private function getUncoveredMethods(): array
    {
        $coverageService = Coverage::make(
            $this->option('cloverDir') ?? config('modularous-coverage.clover_dir'),
            $this->option('cloverName') ?? config('modularous-coverage.clover_name')
        )->setCoverageThreshold((float) $this->option('threshold') ?? config('modularous-coverage.coverage_threshold'));

        if ($git = $this->option('git')) {
            $this->line("Analyzing changes vs <fg=cyan>{$git}</>");

            return $coverageService->git($git);
        }

        if ($files = $this->option('files')) {
            $this->line('Analyzing files: <fg=cyan>' . implode(', ', $files) . '</>');

            return $coverageService->filterByFiles($files)->analyze();
        }

        return $coverageService->analyze();
    }

    private function generateTestsForFile(string $file, array $methods, $bar): void
    {
        $testPath = $this->getTestPath($file);

        if ($this->option('dry-run')) {
            $this->generatedTests[] = [
                'file' => $file,
                'test_path' => $testPath,
                'methods' => count($methods),
                'status' => 'dry-run',
            ];
            $bar->advance(count($methods));

            return;
        }

        try {
            if ($this->option('ai')) {
                $content = $this->generateTestWithAI($file, $methods);
            } else {
                $content = $this->generateTestWithTemplate($file, $methods);
            }

            if ($this->option('interactive') && ! $this->confirmTest($file, $content)) {
                $this->failureCount += count($methods);
                $bar->advance(count($methods));

                return;
            }

            // Write test file
            $this->writeTestFile($testPath, $content);

            $this->generatedTests[] = [
                'file' => $file,
                'test_path' => $testPath,
                'methods' => count($methods),
                'status' => 'success',
            ];

            $this->successCount += count($methods);

        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();

            // Provide helpful error messages for common issues
            if (str_contains($errorMsg, 'quota') || str_contains($errorMsg, '429')) {
                $errorMsg = 'Rate limit exceeded. Try again later or switch to a different model.';
            } elseif (str_contains($errorMsg, 'timeout')) {
                $errorMsg = 'Request timeout. The file might be too large or API is slow.';
            }

            $this->generatedTests[] = [
                'file' => $file,
                'test_path' => $testPath,
                'methods' => count($methods),
                'status' => 'failed',
                'error' => $errorMsg,
            ];

            $this->failureCount += count($methods);

            // Show error in progress
            $this->newLine();
            $this->error("Failed to generate test for {$file}: {$errorMsg}");
        }

        $bar->advance(count($methods));
    }

    private function generateTestWithAI(string $file, array $methods): string
    {
        $provider = self::AI_PROVIDERS[$this->selectedProvider];

        $this->info("\n🤖 Using {$provider['name']} to generate tests for {$file}...");

        // Read source file
        $sourceCode = File::get($file);

        // Build prompt
        $methodList = collect($methods)->map(fn ($m) => "- {$m['class']}::{$m['method']}() (visibility: {$m['visibility']}, complexity: {$m['complexity']})"
        )->join("\n");

        $prompt = $this->buildAIPrompt($file, $sourceCode, $methodList, $methods);

        // Call appropriate AI API
        $response = match ($this->selectedProvider) {
            'anthropic' => $this->callAnthropicAPI($prompt),
            'gemini' => $this->callGeminiAPI($prompt),
            'openai' => $this->callOpenAIAPI($prompt),
            'ollama' => $this->callOllamaAPI($prompt),
            default => throw new \RuntimeException("Unsupported AI provider: {$this->selectedProvider}"),
        };

        return $this->extractTestCode($response);
    }

    private function callAnthropicAPI(string $prompt): string
    {
        $maxRetries = 3;
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01',
                    'x-api-key' => $this->apiKey,
                ])->timeout(120)->post('https://api.anthropic.com/v1/messages', [
                    'model' => $this->selectedModel,
                    'max_tokens' => 4000,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return collect($data['content'])
                        ->where('type', 'text')
                        ->pluck('text')
                        ->join("\n");
                }

                $statusCode = $response->status();

                if ($statusCode === 429) {
                    $retryAfter = $response->header('retry-after') ?? pow(2, $attempt) * 5;
                    $attempt++;

                    if ($attempt < $maxRetries) {
                        $this->warn("\n⏳ Rate limit hit. Waiting {$retryAfter} seconds before retry {$attempt}/{$maxRetries}...");
                        sleep((int) $retryAfter);

                        continue;
                    }
                }

                throw new \RuntimeException(
                    "Anthropic API request failed (Status {$statusCode}): " . $response->body()
                );

            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt < $maxRetries && str_contains($e->getMessage(), 'timeout')) {
                    $waitTime = min(pow(2, $attempt) * 5, 30);
                    $this->warn("\n⏳ Request timeout. Retrying in {$waitTime} seconds... ({$attempt}/{$maxRetries})");
                    sleep($waitTime);

                    continue;
                }

                throw $e;
            }
        }

        throw $lastException ?? new \RuntimeException("Failed after {$maxRetries} attempts");
    }

    private function callGeminiAPI(string $prompt): string
    {
        $endpoint = str_replace(
            '{model}',
            $this->selectedModel,
            self::AI_PROVIDERS['gemini']['endpoint']
        );

        $maxRetries = 3;
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            try {
                $response = Http::timeout(120)->post($endpoint . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 4000,
                    ],
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (! isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        throw new \RuntimeException('Unexpected Gemini API response format');
                    }

                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }

                // Handle rate limiting
                $statusCode = $response->status();
                $body = $response->json();

                if ($statusCode === 429) {
                    $retryAfter = $this->extractRetryDelay($body);
                    $attempt++;

                    if ($attempt < $maxRetries) {
                        $this->warn("\n⏳ Rate limit hit. Waiting {$retryAfter} seconds before retry {$attempt}/{$maxRetries}...");
                        sleep($retryAfter);

                        continue;
                    }
                }

                throw new \RuntimeException(
                    "Gemini API request failed (Status {$statusCode}): " . $response->body()
                );

            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt < $maxRetries && str_contains($e->getMessage(), 'timeout')) {
                    $waitTime = min(pow(2, $attempt) * 5, 30);
                    $this->warn("\n⏳ Request timeout. Retrying in {$waitTime} seconds... ({$attempt}/{$maxRetries})");
                    sleep($waitTime);

                    continue;
                }

                throw $e;
            }
        }

        throw $lastException ?? new \RuntimeException("Failed after {$maxRetries} attempts");
    }

    private function extractRetryDelay(array $responseBody): int
    {
        // Try to extract retry delay from response
        if (isset($responseBody['error']['details'])) {
            foreach ($responseBody['error']['details'] as $detail) {
                if (isset($detail['@type']) &&
                    $detail['@type'] === 'type.googleapis.com/google.rpc.RetryInfo' &&
                    isset($detail['retryDelay'])) {

                    $retryDelay = $detail['retryDelay'];
                    // Parse "33s" format
                    if (preg_match('/(\d+)s/', $retryDelay, $matches)) {
                        return (int) $matches[1];
                    }
                }
            }
        }

        // Default exponential backoff
        return 30;
    }

    private function callOpenAIAPI(string $prompt): string
    {
        $maxRetries = 3;
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(120)->post(self::AI_PROVIDERS['openai']['endpoint'], [
                    'model' => $this->selectedModel,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert PHP test writer. Generate comprehensive, working test code.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => 4000,
                    'temperature' => 0.7,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return $data['choices'][0]['message']['content'];
                }

                $statusCode = $response->status();

                if ($statusCode === 429) {
                    $retryAfter = $response->header('retry-after') ?? pow(2, $attempt) * 5;
                    $attempt++;

                    if ($attempt < $maxRetries) {
                        $this->warn("\n⏳ Rate limit hit. Waiting {$retryAfter} seconds before retry {$attempt}/{$maxRetries}...");
                        sleep((int) $retryAfter);

                        continue;
                    }
                }

                throw new \RuntimeException(
                    "OpenAI API request failed (Status {$statusCode}): " . $response->body()
                );

            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt < $maxRetries && str_contains($e->getMessage(), 'timeout')) {
                    $waitTime = min(pow(2, $attempt) * 5, 30);
                    $this->warn("\n⏳ Request timeout. Retrying in {$waitTime} seconds... ({$attempt}/{$maxRetries})");
                    sleep($waitTime);

                    continue;
                }

                throw $e;
            }
        }

        throw $lastException ?? new \RuntimeException("Failed after {$maxRetries} attempts");
    }

    private function callOllamaAPI(string $prompt): string
    {
        $endpoint = config('modularous-coverage.ollama_endpoint') ??
                    env('OLLAMA_ENDPOINT', 'http://localhost:11434');

        $response = Http::timeout(180)->post("{$endpoint}/api/generate", [
            'model' => $this->selectedModel,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0.7,
                'num_predict' => 4000,
            ],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Ollama API request failed. Make sure Ollama is running locally. ' . $response->body()
            );
        }

        $data = $response->json();

        return $data['response'] ?? '';
    }

    private function extractTestCode(string $response): string
    {
        // Remove markdown code fences if present
        $response = preg_replace('/^```php\s*/m', '', $response);
        $response = preg_replace('/^```\s*/m', '', $response);
        $response = preg_replace('/\s*```$/m', '', $response);

        return trim($response);
    }

    private function generateTestWithTemplate(string $file, array $methods): string
    {
        $template = $this->option('template');

        return match ($template) {
            'pest' => $this->generatePestTest($file, $methods),
            default => $this->generatePHPUnitTest($file, $methods),
        };
    }

    private function getTestPath(string $sourceFile): string
    {
        $outputDir = $this->option('output');
        $relativePath = $this->getRelativePath($sourceFile);
        $testFileName = $this->getTestFileName($sourceFile);

        return $outputDir . '/' . dirname($relativePath) . '/' . $testFileName;
    }

    private function getRelativePath(string $file): string
    {
        $coverageService = Coverage::make(
            $this->option('cloverDir') ?? config('modularous-coverage.clover_dir'),
            $this->option('cloverName') ?? config('modularous-coverage.clover_name')
        )->setCoverageThreshold((float) $this->option('threshold') ?? config('modularous-coverage.coverage_threshold'));

        return $coverageService->getRelativePath($file);
    }

    private function getTestFileName(string $sourceFile): string
    {
        $basename = basename($sourceFile, '.php');

        return $basename . 'Test.php';
    }

    private function extractClassName(string $file): string
    {
        return basename($file, '.php');
    }

    private function extractNamespace(string $file): string
    {
        $content = File::get($file);

        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            return trim($matches[1]);
        }

        return 'App';
    }

    private function getTestNamespace(string $sourceNamespace): string
    {
        $parts = explode('\\', $sourceNamespace);

        if ($parts[0] === 'App') {
            $parts[0] = 'Tests\Unit';
        } else {
            array_unshift($parts, 'Tests', 'Unit');
        }

        return implode('\\', $parts);
    }

    private function writeTestFile(string $path, string $content): void
    {
        $coverageService = Coverage::make(
            $this->option('cloverDir') ?? config('modularous-coverage.clover_dir'),
            $this->option('cloverName') ?? config('modularous-coverage.clover_name')
        )->setCoverageThreshold((float) $this->option('threshold') ?? config('modularous-coverage.coverage_threshold'));

        $path = concatenate_path($coverageService->getBaseDirectory(), $path);
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        File::put($path, $content);
    }

    private function confirmTest(string $file, string $content): bool
    {
        $this->newLine();
        $this->info("Generated test for: {$file}");
        $this->line($content);
        $this->newLine();

        return $this->confirm('Accept this test?', true);
    }

    private function groupByFile(array $methods): array
    {
        $grouped = [];

        foreach ($methods as $method) {
            $file = $method['file'];
            if (! isset($grouped[$file])) {
                $grouped[$file] = [];
            }
            $grouped[$file][] = $method;
        }

        return $grouped;
    }

    private function displaySummary(): void
    {
        $this->info('📊 Generation Summary:');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN - No files were written');
            $this->newLine();
        }

        $this->table(
            ['Source File', 'Test File', 'Methods', 'Status'],
            collect($this->generatedTests)->map(fn ($t) => [
                $this->truncate($t['file'], 40),
                $this->truncate($t['test_path'], 40),
                $t['methods'],
                $this->formatStatus($t['status']),
            ])->toArray()
        );

        $this->newLine();
        $this->info("✅ Success: {$this->successCount} methods");

        if ($this->failureCount > 0) {
            $this->error("❌ Failed: {$this->failureCount} methods");

            // Show error details
            $errors = collect($this->generatedTests)
                ->filter(fn ($t) => $t['status'] === 'failed' && isset($t['error']))
                ->values();

            if ($errors->isNotEmpty()) {
                $this->newLine();
                $this->warn('Error Details:');
                foreach ($errors as $error) {
                    $this->line('  • ' . basename($error['file']) . ': ' . $error['error']);
                }
            }
        }

        if (! $this->option('dry-run')) {
            $this->newLine();
            $this->comment('💡 Next steps:');
            $this->line('  1. Review generated tests in: ' . $this->option('output'));
            $this->line('  2. Implement TODOs in test methods');
            $this->line('  3. Run tests: vendor/bin/phpunit ' . $this->option('output'));

            if ($this->failureCount > 0 && $this->option('ai')) {
                $this->newLine();
                $this->comment('💡 Rate limit tips:');
                $this->line('  • Use --delay=5 to add delay between requests');
                $this->line('  • Try a different model with higher limits');
                $this->line('  • Process fewer files at once with --files option');
                $this->line('  • Use Ollama for unlimited local generation');
            }
        }
    }

    private function formatStatus(string $status): string
    {
        return match ($status) {
            'success' => '<fg=green>✓ Generated</>',
            'failed' => '<fg=red>✗ Failed</>',
            'dry-run' => '<fg=yellow>⊘ Dry Run</>',
            default => $status
        };
    }

    private function truncate(string $text, int $length): string
    {
        return mb_strlen($text) > $length
            ? '...' . mb_substr($text, -($length - 3))
            : $text;
    }

    private function camelToSnake(string $input): string
    {
        return mb_strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    private function buildAIPrompt(string $file, string $sourceCode, string $methodList, array $methods): string
    {
        $template = $this->option('template');

        return <<<PROMPT
Generate comprehensive {$template} tests for the following PHP class.

**Source File:** `{$file}`

**Uncovered Methods:**
{$methodList}

**Source Code:**
```php
{$sourceCode}
```

**Requirements:**
1. Generate complete, working test cases for all uncovered methods
2. Use {$template} testing framework
3. Include edge cases, error conditions, and happy paths
4. Mock dependencies appropriately
5. Follow PSR-12 coding standards
6. Add meaningful assertions
7. Include docblocks explaining what each test validates

**Important:**
- Focus on the uncovered methods listed above
- Generate realistic test data
- Test both success and failure scenarios
- Consider boundary conditions

Return ONLY the complete test class code, no explanations.
PROMPT;
    }

    private function generatePHPUnitTest(string $file, array $methods): string
    {
        $className = $this->extractClassName($file);
        $namespace = $this->extractNamespace($file);
        $testClassName = $className . 'Test';
        $testNamespace = $this->getTestNamespace($namespace);

        $methodTests = collect($methods)->map(function ($method) use ($className) {
            $methodName = $method['method'];
            $testName = 'test_' . $this->camelToSnake($methodName);

            return <<<TEST
    /**
     * Test {$methodName} method
     *
     * @test
     * @covers \\{$method['class']}::{$methodName}
     */
    public function {$testName}(): void
    {
        // Arrange
        \$instance = new {$className}();

        // Act
        // TODO: Call \$instance->{$methodName}() with appropriate parameters

        // Assert
        // TODO: Add assertions
        \$this->markTestIncomplete('This test needs to be implemented');
    }
TEST;
        })->join("\n\n");

        return <<<PHP
<?php

namespace {$testNamespace};

use PHPUnit\Framework\TestCase;
use {$namespace}\\{$className};

/**
 * Generated test for {$className}
 *
 * @coversDefaultClass \\{$namespace}\\{$className}
 */
class {$testClassName} extends TestCase
{
{$methodTests}
}
PHP;
    }

    private function generatePestTest(string $file, array $methods): string
    {
        $className = $this->extractClassName($file);
        $namespace = $this->extractNamespace($file);

        $methodTests = collect($methods)->map(function ($method) use ($className, $namespace) {
            $methodName = $method['method'];
            $description = str_replace('_', ' ', $this->camelToSnake($methodName));

            return <<<TEST
test('{$description}', function () {
    // Arrange
    \$instance = new \\{$namespace}\\{$className}();

    // Act
    // TODO: Call \$instance->{$methodName}() with appropriate parameters

    // Assert
    // TODO: Add expectations
})->todo();
TEST;
        })->join("\n\n");

        return <<<PHP
<?php

use {$namespace}\\{$className};

/**
 * Generated tests for {$className}
 */

{$methodTests}
PHP;
    }
}
