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
