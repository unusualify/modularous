<?php

namespace Unusualify\Modularous\Console\Sync;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use JoeDixon\Translation\Scanner;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\FileTranslation;

class SyncTranslationsCommand extends BaseCommand
{
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:sync:translations
                            {--dry-run : Show missing keys without syncing}
                            {--only-languages= : Sync only specific languages}
                            {--exclude-languages= : Exclude specific languages}
                            {--language= : Sync only specific language}';

    protected $aliases = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync missing translation keys from Laravel lang path to Modularous lang path';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /*
     * Executes the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        $laravelLangPath = base_path('lang');
        $modularousLangPath = base_path('modularous/lang');

        $translationFileClass = new FileTranslation(
            new Filesystem,
            $laravelLangPath,
            'en',
            App::make(Scanner::class)
        );

        $allLanguages = $translationFileClass->allLanguages();

        $isDryRun = $this->option('dry-run');
        $specificLanguage = $this->option('language');
        $onlyLanguages = $this->option('only-languages');
        $excludeLanguages = $this->option('exclude-languages');

        // Ensure modularous lang path and language folders exist
        $this->ensureLanguageFoldersExist($translationFileClass, $laravelLangPath, $modularousLangPath, $specificLanguage);

        $this->info('🔍 Analyzing translation files...');
        $this->newLine();

        if ($specificLanguage) {
            // Handle specific language
            $this->handleLanguageSync(
                $translationFileClass,
                $laravelLangPath,
                $modularousLangPath,
                $specificLanguage,
                $isDryRun
            );
        } elseif ($onlyLanguages || $excludeLanguages) {
            $languages = [];
            if ($onlyLanguages) {
                $onlyLanguages = explode(',', $onlyLanguages);
                $languages = $translationFileClass->getLanguagesOnly($onlyLanguages);
            } elseif ($excludeLanguages) {
                $excludeLanguages = explode(',', $excludeLanguages);
                $languages = $translationFileClass->getLanguagesExcept($excludeLanguages);
            }

            foreach ($languages as $language) {
                $this->handleLanguageSync(
                    $translationFileClass,
                    $laravelLangPath,
                    $modularousLangPath,
                    $language,
                    $isDryRun
                );
            }
        } else {
            // Handle all languages
            $this->handleAllLanguagesSync(
                $translationFileClass,
                $laravelLangPath,
                $modularousLangPath,
                $isDryRun
            );
        }

        return 0;
    }

    /**
     * Handle sync for a specific language.
     *
     * @param FileTranslation $translationFileClass
     * @param string $laravelLangPath
     * @param string $modularousLangPath
     * @param string $language
     * @param bool $isDryRun
     * @return void
     */
    protected function handleLanguageSync($translationFileClass, $laravelLangPath, $modularousLangPath, $language, $isDryRun)
    {
        $this->info("Language: {$language}");

        $missingKeys = $translationFileClass->findMissingKeysFromPath(
            $laravelLangPath,
            $modularousLangPath,
            $language
        );

        if (empty($missingKeys)) {
            $this->info("✅ No missing keys for language '{$language}'");

            return;
        }

        $this->displayMissingKeys($missingKeys, $language);

        if ($isDryRun) {
            $this->warn('🔎 Dry run mode - no files were modified');
        } else {
            $this->info('📝 Syncing missing keys...');
            $translationFileClass->syncMissingKeysToPath(
                $laravelLangPath,
                $modularousLangPath,
                $language,
                $missingKeys
            );
            $this->info("✅ Successfully synced missing keys for '{$language}'");
        }
    }

    /**
     * Handle sync for all languages.
     *
     * @param FileTranslation $translationFileClass
     * @param string $laravelLangPath
     * @param string $modularousLangPath
     * @param bool $isDryRun
     * @return void
     */
    protected function handleAllLanguagesSync($translationFileClass, $laravelLangPath, $modularousLangPath, $isDryRun)
    {
        $allMissingKeys = $translationFileClass->findAllMissingKeys(
            $laravelLangPath,
            $modularousLangPath
        );

        if (empty($allMissingKeys)) {
            $this->info('✅ No missing keys found in any language');

            return;
        }

        // Display summary
        foreach ($allMissingKeys as $language => $missingKeys) {
            $this->displayMissingKeys($missingKeys, $language);
            $this->newLine();
        }

        if ($isDryRun) {
            $this->warn('🔎 Dry run mode - no files were modified');
        } else {
            $this->info('📝 Syncing all missing keys...');
            $stats = $translationFileClass->syncAllMissingKeys(
                $laravelLangPath,
                $modularousLangPath
            );

            $this->newLine();
            $this->info('✅ Sync completed successfully!');
            $this->info("Total keys synced: {$stats['total_keys']}");

            foreach ($stats['languages'] as $language => $count) {
                $this->line("  - {$language}: {$count} keys");
            }
        }
    }

    /**
     * Handle sync for specific languages.
     *
     * @param FileTranslation $translationFileClass
     * @param string $laravelLangPath
     * @param string $modularousLangPath
     * @param array $languages
     * @param bool $isDryRun
     * @return void
     */
    protected function handleSpecificLanguagesSync($translationFileClass, $laravelLangPath, $modularousLangPath, $languages, $isDryRun)
    {
        $this->info('Languages: ' . implode(', ', $languages));

        foreach ($languages as $language) {
            $this->handleLanguageSync(
                $translationFileClass,
                $laravelLangPath,
                $modularousLangPath,
                $language,
                $isDryRun
            );
        }

        $this->info('✅ Successfully synced missing keys for all languages');
    }

    /**
     * Handle sync for exclude specific languages.
     *
     * @param FileTranslation $translationFileClass
     * @param string $laravelLangPath
     * @param string $modularousLangPath
    /**
     * Display missing keys for a language.
     * @param array $missingKeys
     * @param string $language
     * @return void
     */
    protected function displayMissingKeys($missingKeys, $language)
    {
        $totalKeys = 0;

        $this->warn("Missing keys for language '{$language}':");

        foreach ($missingKeys as $type => $groups) {
            $this->line("  Type: {$type}");

            foreach ($groups as $group => $translations) {
                $count = count($translations);
                $totalKeys += $count;
                $this->line("    - {$group}: {$count} keys");

                // Show first few keys as examples
                $keys = array_keys($translations);
                $exampleKeys = array_slice($keys, 0, 3);

                foreach ($exampleKeys as $key) {
                    $this->line("      • {$key}");
                }

                if (count($keys) > 3) {
                    $remaining = count($keys) - 3;
                    $this->line("      ... and {$remaining} more");
                }
            }
        }

        $this->info("Total missing keys: {$totalKeys}");
    }

    /**
     * Ensure language folders exist in the target path.
     *
     * @param FileTranslation $translationFileClass
     * @param string $laravelLangPath
     * @param string $modularousLangPath
     * @param string|null $specificLanguage
     * @return void
     */
    protected function ensureLanguageFoldersExist($translationFileClass, $laravelLangPath, $modularousLangPath, $specificLanguage = null)
    {
        $disk = new Filesystem;

        // Ensure the main modularous lang path exists
        if (! $disk->exists($modularousLangPath)) {
            $this->warn("📁 Creating modularous lang directory: {$modularousLangPath}");
            $disk->makeDirectory($modularousLangPath, 0755, true);
        }

        // Get languages to check/create
        if ($specificLanguage) {
            $languages = [$specificLanguage];
        } else {
            // Get all languages from Laravel lang path
            $sourceInstance = new FileTranslation(
                $disk,
                $laravelLangPath,
                'en',
                App::make(Scanner::class)
            );
            $languages = $sourceInstance->allLanguages()->keys()->toArray();
        }

        // Create language folders if they don't exist
        foreach ($languages as $language) {
            $languageFolder = $modularousLangPath . DIRECTORY_SEPARATOR . $language;

            if (! $disk->exists($languageFolder)) {
                $this->warn("📁 Creating language folder: {$language}");
                $disk->makeDirectory($languageFolder, 0755, true);
            }
        }
    }
}
