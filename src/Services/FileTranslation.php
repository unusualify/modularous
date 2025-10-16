<?php

namespace Unusualify\Modularity\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JoeDixon\Translation\Drivers\File;

class FileTranslation extends File
{
    public $disk;

    public $languageFilesPath;

    public $sourceLanguage;

    public $scanner;

    public function __construct(Filesystem $disk, $languageFilesPath, $sourceLanguage, $scanner)
    {
        parent::__construct($disk, $languageFilesPath, $sourceLanguage, $scanner);
        $this->disk = $disk;
        $this->languageFilesPath = $languageFilesPath;
        $this->sourceLanguage = $sourceLanguage;
        $this->scanner = $scanner;

    }

    /**
     * Save namespaced group type language translations.
     *
     * @param  string  $language
     * @param  string  $group
     * @param  array  $translations
     * @return void
     */
    private function saveNamespacedGroupTranslations($language, $group, $translations)
    {
        [$namespace, $group] = explode('::', $group);
        $directory = "{$this->languageFilesPath}".DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR."{$namespace}".DIRECTORY_SEPARATOR."{$language}";

        if (! $this->disk->exists($directory)) {
            $this->disk->makeDirectory($directory, 0755, true);
        }

        // $this->disk->put("$directory".DIRECTORY_SEPARATOR."{$group}.php", "<?php\n\nreturn ".var_export($translations, true).';'.\PHP_EOL);
        $this->disk->put("$directory".DIRECTORY_SEPARATOR."{$group}.php", php_array_file_content($translations));
    }
    /**
     * Save group type language translations.
     *
     * @param  string  $language
     * @param  string  $group
     * @param  array  $translations
     * @return void
     */
    public function saveGroupTranslations($language, $group, $translations)
    {
        // here we check if it's a namespaced translation which need saving to a
        // different path
        $translations = $translations instanceof Collection ? $translations->toArray() : $translations;
        ksort($translations);
        $translations = array_undot($translations);
        if (Str::contains($group, '::')) {
            return $this->saveNamespacedGroupTranslations($language, $group, $translations);
        }

        // $this->disk->put("{$this->languageFilesPath}".DIRECTORY_SEPARATOR."{$language}".DIRECTORY_SEPARATOR."{$group}.php", "<?php\n\nreturn ".var_export($translations, true).';'.\PHP_EOL);
        $this->disk->put("{$this->languageFilesPath}".DIRECTORY_SEPARATOR."{$language}".DIRECTORY_SEPARATOR."{$group}.php", php_array_file_content($translations));
    }
}
