<?php

namespace Unusualify\Modularity\Tests\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Unusualify\Modularity\Http\Controllers\API\LanguageController;
use Unusualify\Modularity\Tests\TestCase;
use Unusualify\Modularity\Translation\Translator;

class LanguageControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_index_returns_json_response()
    {
        $translator = $this->createMock(Translator::class);
        $translator->method('getTranslations')->willReturn(['en' => ['key' => 'value']]);
        $this->app->instance('translator', $translator);

        $controller = $this->app->make(LanguageController::class);
        $request = Request::create('/');

        $response = $controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
    }

    public function test_index_returns_array_structure()
    {
        $translator = $this->createMock(Translator::class);
        $translator->method('getTranslations')->willReturn(['en' => ['key' => 'value']]);
        $this->app->instance('translator', $translator);

        $controller = $this->app->make(LanguageController::class);
        $request = Request::create('/');

        $response = $controller->index($request);
        $content = json_decode($response->getContent(), true);

        $this->assertIsArray($content);
    }
}
