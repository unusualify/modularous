<?php

namespace Unusualify\Modularous\Tests\Helpers;

use Unusualify\Modularous\Tests\TestCase;

class ComponentHelpersTest extends TestCase
{
    /** @test */
    public function test_modularous_response_modal_body_component_returns_array()
    {
        $result = modularous_response_modal_body_component(
            'success',
            'mdi-check-circle',
            'Success',
            'Operation completed'
        );

        // Component render() returns an array representation
        $this->assertIsArray($result);
    }

    /** @test */
    public function test_modularous_chip_leaves_variant_to_theme_defaults()
    {
        $this->assertSame(
            '<v-chip color="info" prepend-icon="mdi-clock-outline">Provision</v-chip>',
            modularous_chip('Provision', 'info', 'mdi-clock-outline')
        );
        $this->assertSame('<v-chip>No Status</v-chip>', modularous_chip('No Status'));
    }

    /** @test */
    public function test_modularous_chip_escapes_label_and_merges_attributes()
    {
        $html = modularous_chip('<b>A & B</b>', 'error', null, ['variant' => 'flat', 'class' => 'x']);

        $this->assertSame('<v-chip color="error" variant="flat" class="x">&lt;b&gt;A &amp; B&lt;/b&gt;</v-chip>', $html);
    }

    /** @test */
    public function test_modularous_modal_service_returns_array_structure()
    {
        $result = modularous_modal_service(
            'error',
            'mdi-alert',
            'Error',
            'Something went wrong'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('component', $result);
        $this->assertArrayHasKey('props', $result);
        $this->assertArrayHasKey('modalProps', $result);
        $this->assertEquals('ue-recursive-stuff', $result['component']);
    }

    /** @test */
    public function test_modularous_modal_service_form_returns_array_structure()
    {
        $schema = [['name' => 'email', 'type' => 'text']];
        $actionUrl = '/submit';
        $buttonText = 'Submit';

        $result = modularous_modal_service_form($schema, $actionUrl, $buttonText);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('component', $result);
        $this->assertArrayHasKey('props', $result);
        $this->assertArrayHasKey('modalProps', $result);
        $this->assertEquals('ue-recursive-stuff', $result['component']);
    }

    /** @test */
    public function test_modularous_modal_service_form_with_model()
    {
        $schema = [['name' => 'email', 'type' => 'text']];
        $actionUrl = '/submit';
        $buttonText = 'Submit';
        $model = ['email' => 'test@example.com'];

        $result = modularous_modal_service_form($schema, $actionUrl, $buttonText, $model);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('props', $result);
    }

    /** @test */
    public function test_modularous_new_modal_service_returns_array_structure()
    {
        $result = modularous_new_modal_service(
            'warning',
            'mdi-alert-circle',
            'Warning',
            'Please review'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('component', $result);
        $this->assertArrayHasKey('props', $result);
        $this->assertArrayHasKey('modalProps', $result);
        $this->assertEquals('ue-recursive-stuff', $result['component']);
    }

    /** @test */
    public function test_modularous_new_response_modal_body_component_returns_array()
    {
        $result = modularous_new_response_modal_body_component(
            'info',
            'mdi-information',
            'Information',
            'Here is some info'
        );

        // Component render() returns an array representation
        $this->assertIsArray($result);
    }
}
