<?php

namespace Unusualify\Modularity\Tests\Services;

use Unusualify\Modularity\Services\SlugInputValidationService;
use Unusualify\Modularity\Tests\Repositories\TestModel;
use Unusualify\Modularity\Tests\TestCase;

class SlugInputValidationServiceTest extends TestCase
{
    public function test_rejects_model_without_has_slug_trait(): void
    {
        $service = app(SlugInputValidationService::class);

        $result = $service->validateModelSlug(TestModel::class, 'any-slug', 'en', true, null);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['message']);
    }
}
