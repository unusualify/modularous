<?php

namespace Unusualify\Modularity\Support;

use Illuminate\Database\Schema\Blueprint;

/**
 * Publishable metadata columns (SEO title/description, canonical, robots, sitemap inclusion) for
 * {@see \Unusualify\Modularity\Entities\Traits\Publishable}.
 *
 * Repository form inputs: {@see \Unusualify\Modularity\Repositories\Traits\PublishableTrait}.
 */
final class PublishableMetadata
{
    /**
     * Add standard metadata columns to a translations table blueprint.
     */
    public static function addColumns(Blueprint $table, bool $publishable = true, $publishDates = false): void
    {
        if ($publishable) {
            $table->boolean('published')->default(true);
        }

        if ($publishDates) {
            $table->timestamp('publish_start_date')->nullable();
            $table->timestamp('publish_end_date')->nullable();
        }
    }

    /**
     * Default Modularity form input definitions (usually appended via {@see PublishableTrait}).
     *
     * @return list<array<string, mixed>>
     */
    public static function defaultFormInputs(): array
    {
        return [
            ['type' => 'switch', 'name' => 'published', 'label' => 'Published', 'trueValue' => true, 'falseValue' => false, 'isEvent' => true],
            ['name' => 'publish_start_date', 'label' => 'Publish from', 'type' => 'date', 'isSecondary' => true],
            ['name' => 'publish_end_date', 'label' => 'Publish until', 'type' => 'date', 'isSecondary' => true],
        ];
    }
}
