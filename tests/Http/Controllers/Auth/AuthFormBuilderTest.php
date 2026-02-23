<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Tests\Http\Controllers\Auth;

use Illuminate\Config\Repository as Config;
use Illuminate\Routing\Redirector;
use Illuminate\View\Factory as ViewFactory;
use Unusualify\Modularity\Http\Controllers\Auth\Controller;
use Unusualify\Modularity\Tests\TestCase;

/**
 * Test controller that exposes protected AuthFormBuilder methods for testing.
 */
class TestAuthController extends Controller
{
    public function buildAuthViewData(string $pageKey, array $overrides = []): array
    {
        return parent::buildAuthViewData($pageKey, $overrides);
    }

    public function authFormTitle(string $text, array $overrides = []): array
    {
        return parent::authFormTitle($text, $overrides);
    }

    public function restartOptionSlot(): array
    {
        return parent::restartOptionSlot();
    }

    public function resendOptionSlot(): array
    {
        return parent::resendOptionSlot();
    }

    public function haveAccountOptionSlot(): array
    {
        return parent::haveAccountOptionSlot();
    }
}

class AuthFormBuilderTest extends TestCase
{
    protected TestAuthController $controller;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('modularity.enabled.users-management', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new TestAuthController(
            app(Config::class),
            app(Redirector::class),
            app(ViewFactory::class)
        );

        $this->setupAuthConfig();
    }

    protected function setupAuthConfig(): void
    {
        config([
            'modularity.auth_pages' => array_merge(config('modularity.auth_pages', []), [
                'layout' => [
                    'logoSymbol' => 'main-logo-dark',
                    'logoLightSymbol' => 'main-logo-light',
                ],
                'layoutPresets' => [
                    'banner' => ['noSecondSection' => false],
                    'minimal' => ['noSecondSection' => true],
                ],
                'pages' => [
                    'login' => [
                        'pageTitle' => 'authentication.login',
                        'layoutPreset' => 'banner',
                        'formDraft' => 'login_form',
                        'actionRoute' => 'admin.login',
                        'formTitle' => 'authentication.login-title',
                        'buttonText' => 'authentication.sign-in',
                        'formSlotsPreset' => 'login_options',
                        'slotsPreset' => 'login_bottom',
                    ],
                    'forgot_password' => [
                        'pageTitle' => 'authentication.forgot-password',
                        'layoutPreset' => 'minimal',
                        'formDraft' => 'forgot_password_form',
                        'actionRoute' => 'admin.password.reset.email',
                        'formSlotsPreset' => 'forgot_password_form',
                        'slotsPreset' => 'forgot_password_bottom',
                    ],
                ],
            ]),
            'modularity.form_drafts.login_form' => [
                ['name' => 'email', 'type' => 'text', 'label' => 'Email'],
                ['name' => 'password', 'type' => 'password', 'label' => 'Password'],
            ],
            'modularity.form_drafts.forgot_password_form' => [
                ['name' => 'email', 'type' => 'text', 'label' => 'Email'],
            ],
        ]);
    }

    /** @test */
    public function it_builds_auth_view_data_for_login_page(): void
    {
        $data = $this->controller->buildAuthViewData('login');

        $this->assertArrayHasKey('attributes', $data);
        $this->assertArrayHasKey('formAttributes', $data);
        $this->assertArrayHasKey('formSlots', $data);
        $this->assertArrayHasKey('slots', $data);
        $this->assertArrayHasKey('pageTitle', $data);

        $this->assertArrayHasKey('noSecondSection', $data['attributes']);
        $this->assertArrayHasKey('logoLightSymbol', $data['attributes']);
        $this->assertArrayHasKey('logoSymbol', $data['attributes']);
    }

    /** @test */
    public function it_merges_layout_preset_into_attributes(): void
    {
        $data = $this->controller->buildAuthViewData('login');

        $this->assertFalse($data['attributes']['noSecondSection']);
    }

    /** @test */
    public function it_merges_minimal_preset_for_forgot_password(): void
    {
        $data = $this->controller->buildAuthViewData('forgot_password');

        $this->assertTrue($data['attributes']['noSecondSection']);
    }

    /** @test */
    public function it_applies_overrides_to_attributes(): void
    {
        $data = $this->controller->buildAuthViewData('login', [
            'attributes' => ['noSecondSection' => true],
        ]);

        $this->assertTrue($data['attributes']['noSecondSection']);
    }

    /** @test */
    public function it_resolves_form_slots_preset_login_options(): void
    {
        $data = $this->controller->buildAuthViewData('login');

        $this->assertArrayHasKey('options', $data['formSlots']);
        $this->assertIsArray($data['formSlots']['options']);
        $this->assertArrayHasKey('tag', $data['formSlots']['options']);
        $this->assertEquals('v-btn', $data['formSlots']['options']['tag']);
    }

    /** @test */
    public function it_resolves_restart_option_slot(): void
    {
        $slot = $this->controller->restartOptionSlot();

        $this->assertArrayHasKey('options', $slot);
        $this->assertArrayHasKey('tag', $slot['options']);
        $this->assertEquals('v-btn', $slot['options']['tag']);
        $this->assertArrayHasKey('attributes', $slot['options']);
        $this->assertArrayHasKey('href', $slot['options']['attributes']);
    }

    /** @test */
    public function it_resolves_resend_option_slot(): void
    {
        $slot = $this->controller->resendOptionSlot();

        $this->assertArrayHasKey('options', $slot);
        $this->assertEquals('v-btn', $slot['options']['tag']);
    }

    /** @test */
    public function it_resolves_have_account_option_slot(): void
    {
        $slot = $this->controller->haveAccountOptionSlot();

        $this->assertArrayHasKey('options', $slot);
        $this->assertArrayHasKey('attributes', $slot['options']);
    }

    /** @test */
    public function it_builds_auth_form_title(): void
    {
        $title = $this->controller->authFormTitle('Test Title');

        $this->assertEquals('Test Title', $title['text']);
        $this->assertEquals('h1', $title['tag']);
        $this->assertEquals('primary', $title['color']);
    }

    /** @test */
    public function it_builds_auth_form_title_with_overrides(): void
    {
        $title = $this->controller->authFormTitle('Test', ['tag' => 'h2']);

        $this->assertEquals('h2', $title['tag']);
    }
}
