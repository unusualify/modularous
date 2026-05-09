<?php

namespace Unusualify\Modularous\Tests\Models\Enums;

use Unusualify\Modularous\Entities\Enums\RoleTeam;
use Unusualify\Modularous\Tests\TestCase;

class RoleTeamTest extends TestCase
{
    public function test_enum_cases()
    {
        $expectedCases = [
            'CORPORATE' => 1,
            'CLIENT' => 2,
        ];

        foreach ($expectedCases as $caseName => $caseValue) {
            $this->assertEquals($caseValue, RoleTeam::from($caseValue)->value);
            $this->assertEquals($caseValue, constant(RoleTeam::class . '::' . $caseName)->value);
        }
    }

    public function test_all_cases_exist()
    {
        $cases = RoleTeam::cases();
        $this->assertCount(2, $cases);

        $caseValues = array_map(fn ($case) => $case->value, $cases);
        $this->assertContains(1, $caseValues);
        $this->assertContains(2, $caseValues);
    }

    public function test_from_method_with_valid_values()
    {
        $this->assertInstanceOf(RoleTeam::class, RoleTeam::from(1));
        $this->assertInstanceOf(RoleTeam::class, RoleTeam::from(2));
        $this->assertEquals(RoleTeam::CORPORATE, RoleTeam::from(1));
        $this->assertEquals(RoleTeam::CLIENT, RoleTeam::from(2));
    }

    public function test_from_method_with_invalid_value()
    {
        $this->expectException(\ValueError::class);
        RoleTeam::from(99);
    }

    public function test_try_from_method_with_valid_values()
    {
        $this->assertInstanceOf(RoleTeam::class, RoleTeam::tryFrom(1));
        $this->assertInstanceOf(RoleTeam::class, RoleTeam::tryFrom(2));
        $this->assertEquals(RoleTeam::CORPORATE, RoleTeam::tryFrom(1));
        $this->assertEquals(RoleTeam::CLIENT, RoleTeam::tryFrom(2));
    }

    public function test_try_from_method_with_invalid_value()
    {
        $this->assertNull(RoleTeam::tryFrom(99));
        $this->assertNull(RoleTeam::tryFrom(0));
    }

    public function test_enum_name_property()
    {
        $this->assertEquals('CORPORATE', RoleTeam::CORPORATE->name);
        $this->assertEquals('CLIENT', RoleTeam::CLIENT->name);
    }

    public function test_enum_value_property()
    {
        $this->assertEquals(1, RoleTeam::CORPORATE->value);
        $this->assertEquals(2, RoleTeam::CLIENT->value);
    }

    public function test_enum_comparison()
    {
        $corporate1 = RoleTeam::CORPORATE;
        $corporate2 = RoleTeam::from(1);
        $client = RoleTeam::CLIENT;

        $this->assertTrue($corporate1 === $corporate2);
        $this->assertFalse($corporate1 === $client);
    }

    public function test_enum_in_match_expression()
    {
        $team = RoleTeam::CORPORATE;

        $result = match ($team) {
            RoleTeam::CORPORATE => 'corporate',
            RoleTeam::CLIENT => 'client',
        };

        $this->assertEquals('corporate', $result);
    }

    public function test_enum_json_serialization()
    {
        $team = RoleTeam::CLIENT;
        $json = json_encode($team);

        $this->assertEquals('2', $json);
    }
}
