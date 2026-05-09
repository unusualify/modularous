<?php

namespace Unusualify\Modularous\Tests\Models\Enums;

use Unusualify\Modularous\Entities\Enums\UserRole;
use Unusualify\Modularous\Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_enum_cases()
    {
        $expectedCases = [
            'SUPERADMIN' => 'Superadmin',
            'ADMIN' => 'Admin',
            'PUBLISHER' => 'Publisher',
            'VIEWONLY' => 'View Only',
        ];

        foreach ($expectedCases as $caseName => $caseValue) {
            $this->assertEquals($caseValue, UserRole::from($caseValue)->value);
            $this->assertEquals($caseValue, constant(UserRole::class . '::' . $caseName)->value);
        }
    }

    public function test_all_cases_exist()
    {
        $cases = UserRole::cases();
        $this->assertCount(4, $cases);

        $caseValues = array_map(fn ($case) => $case->value, $cases);
        $this->assertContains('Superadmin', $caseValues);
        $this->assertContains('Admin', $caseValues);
        $this->assertContains('Publisher', $caseValues);
        $this->assertContains('View Only', $caseValues);
    }

    public function test_from_method_with_valid_values()
    {
        $this->assertInstanceOf(UserRole::class, UserRole::from('Superadmin'));
        $this->assertInstanceOf(UserRole::class, UserRole::from('Admin'));
        $this->assertInstanceOf(UserRole::class, UserRole::from('Publisher'));
        $this->assertInstanceOf(UserRole::class, UserRole::from('View Only'));
        $this->assertEquals(UserRole::SUPERADMIN, UserRole::from('Superadmin'));
        $this->assertEquals(UserRole::ADMIN, UserRole::from('Admin'));
    }

    public function test_from_method_with_invalid_value()
    {
        $this->expectException(\ValueError::class);
        UserRole::from('InvalidRole');
    }

    public function test_try_from_method_with_valid_values()
    {
        $this->assertInstanceOf(UserRole::class, UserRole::tryFrom('Superadmin'));
        $this->assertInstanceOf(UserRole::class, UserRole::tryFrom('Admin'));
        $this->assertEquals(UserRole::PUBLISHER, UserRole::tryFrom('Publisher'));
        $this->assertEquals(UserRole::VIEWONLY, UserRole::tryFrom('View Only'));
    }

    public function test_try_from_method_with_invalid_value()
    {
        $this->assertNull(UserRole::tryFrom('InvalidRole'));
        $this->assertNull(UserRole::tryFrom(''));
    }

    public function test_enum_name_property()
    {
        $this->assertEquals('SUPERADMIN', UserRole::SUPERADMIN->name);
        $this->assertEquals('ADMIN', UserRole::ADMIN->name);
        $this->assertEquals('PUBLISHER', UserRole::PUBLISHER->name);
        $this->assertEquals('VIEWONLY', UserRole::VIEWONLY->name);
    }

    public function test_enum_value_property()
    {
        $this->assertEquals('Superadmin', UserRole::SUPERADMIN->value);
        $this->assertEquals('Admin', UserRole::ADMIN->value);
        $this->assertEquals('Publisher', UserRole::PUBLISHER->value);
        $this->assertEquals('View Only', UserRole::VIEWONLY->value);
    }

    public function test_enum_comparison()
    {
        $admin1 = UserRole::ADMIN;
        $admin2 = UserRole::from('Admin');
        $publisher = UserRole::PUBLISHER;

        $this->assertTrue($admin1 === $admin2);
        $this->assertFalse($admin1 === $publisher);
    }

    public function test_enum_in_match_expression()
    {
        $role = UserRole::PUBLISHER;

        $result = match ($role) {
            UserRole::SUPERADMIN => 'superadmin',
            UserRole::ADMIN => 'admin',
            UserRole::PUBLISHER => 'publisher',
            UserRole::VIEWONLY => 'viewonly',
        };

        $this->assertEquals('publisher', $result);
    }

    public function test_enum_json_serialization()
    {
        $role = UserRole::ADMIN;
        $json = json_encode($role);

        $this->assertEquals('"Admin"', $json);
    }

    public function test_enum_serialization()
    {
        $role = UserRole::ADMIN;
        $serialized = serialize($role);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(UserRole::class, $unserialized);
        $this->assertTrue($role === $unserialized);
        $this->assertEquals($role->value, $unserialized->value);
    }
}
