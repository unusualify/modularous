<?php

namespace Unusualify\Modularous\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Unusualify\Modularous\Entities\File;

/**
 * @extends Factory<File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = File::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'filename' => fake()->name(),
            'size' => fake()->numberBetween(100, 1000000),
        ];
    }
}
