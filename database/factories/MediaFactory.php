<?php

namespace Unusualify\Modularous\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Unusualify\Modularous\Entities\Media;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'filename' => fake()->word() . '.jpg',
            'alt_text' => fake()->sentence(),
            'caption' => fake()->sentence(),
            'width' => fake()->numberBetween(800, 1920),
            'height' => fake()->numberBetween(600, 1080),
        ];
    }
}
