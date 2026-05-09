<?php

namespace Unusualify\Modularous\Tests\Services;

use Unusualify\Modularous\Services\MessageStage;
use Unusualify\Modularous\Tests\TestCase;

class MessageStageTest extends TestCase
{
    /** @test */
    public function test_enum_has_success_case()
    {
        $this->assertEquals('success', MessageStage::SUCCESS->value);
    }

    /** @test */
    public function test_enum_has_error_case()
    {
        $this->assertEquals('error', MessageStage::ERROR->value);
    }

    /** @test */
    public function test_enum_has_warning_case()
    {
        $this->assertEquals('warning', MessageStage::WARNING->value);
    }

    /** @test */
    public function test_enum_has_info_case()
    {
        $this->assertEquals('info', MessageStage::INFO->value);
    }

    /** @test */
    public function test_can_get_all_cases()
    {
        $cases = MessageStage::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(MessageStage::SUCCESS, $cases);
        $this->assertContains(MessageStage::ERROR, $cases);
        $this->assertContains(MessageStage::WARNING, $cases);
        $this->assertContains(MessageStage::INFO, $cases);
    }

    /** @test */
    public function test_can_construct_from_value()
    {
        $this->assertEquals(MessageStage::SUCCESS, MessageStage::from('success'));
        $this->assertEquals(MessageStage::ERROR, MessageStage::from('error'));
        $this->assertEquals(MessageStage::WARNING, MessageStage::from('warning'));
        $this->assertEquals(MessageStage::INFO, MessageStage::from('info'));
    }

    /** @test */
    public function test_from_throws_exception_for_invalid_value()
    {
        $this->expectException(\ValueError::class);
        MessageStage::from('invalid');
    }

    /** @test */
    public function test_try_from_returns_null_for_invalid_value()
    {
        $result = MessageStage::tryFrom('invalid');

        $this->assertNull($result);
    }
}
