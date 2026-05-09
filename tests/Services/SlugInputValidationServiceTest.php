<?php

namespace Unusualify\Modularous\Tests\Services;

use Unusualify\Modularous\Services\SlugInputValidationService;
use Unusualify\Modularous\Tests\Repositories\TestModel;
use Unusualify\Modularous\Tests\TestCase;

class SlugInputValidationServiceTest extends TestCase
{
    public function test_rejects_model_without_has_slug_trait(): void
    {
        $service = app(SlugInputValidationService::class);

        $result = $service->validateModelSlug(TestModel::class, 'any-slug', 'en', true, null);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['message']);
    }

    public function test_propose_rejects_model_without_has_slug_trait(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $service = app(SlugInputValidationService::class);
        $service->proposeUniqueSlugForModel(TestModel::class, 'hello-world', 'en', true, null);
    }
}
