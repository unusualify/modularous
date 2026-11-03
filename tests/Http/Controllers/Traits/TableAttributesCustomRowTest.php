<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Unusualify\Modularous\Http\Controllers\Traits\Table\TableAttributes;
use Unusualify\Modularous\Tests\TestCase;

class TableAttributesCustomRowTest extends TestCase
{
    private function makeStub(array $attributes, array $userRoles): object
    {
        return new class($attributes, $userRoles)
        {
            use TableAttributes;

            public function __construct(array $attributes, private array $userRoles)
            {
                $this->tableAttributes = $attributes;
            }

            public function isAllowedItem($item, $searchKey = null, $orClosure = null, $andClosure = null, $disallowIfUnauthenticated = true)
            {
                $allowedRoles = $item[$searchKey ?? 'allowedRoles'] ?? null;

                return $allowedRoles === null || array_intersect($allowedRoles, $this->userRoles) !== [];
            }

            public function hydrate(): array
            {
                return $this->hydrateTableAttributes();
            }
        };
    }

    private function clientCustomRowAttributes(): array
    {
        return [
            'striped' => true,
            'transparentBody' => false,
            'customRow' => [
                [
                    'name' => 'custom-card',
                    'allowedRoles' => ['client-manager'],
                    'tableAttributes' => [
                        'striped' => false,
                        'transparentBody' => true,
                    ],
                ],
            ],
        ];
    }

    public function test_matching_custom_row_applies_its_table_attributes(): void
    {
        $result = $this->makeStub($this->clientCustomRowAttributes(), ['client-manager'])->hydrate();

        $this->assertFalse($result['striped']);
        $this->assertTrue($result['transparentBody']);
        $this->assertSame('custom-card', $result['customRow']['name']);
        $this->assertArrayNotHasKey('tableAttributes', $result['customRow']);
        $this->assertArrayNotHasKey('allowedRoles', $result['customRow']);
    }

    public function test_non_matching_role_keeps_table_attributes_untouched(): void
    {
        $result = $this->makeStub($this->clientCustomRowAttributes(), ['admin'])->hydrate();

        $this->assertTrue($result['striped']);
        $this->assertFalse($result['transparentBody']);
        $this->assertSame([], $result['customRow']);
    }

    public function test_associative_custom_row_applies_its_table_attributes(): void
    {
        $result = $this->makeStub([
            'customRow' => [
                'name' => 'custom-card',
                'tableAttributes' => ['transparentBody' => true],
            ],
        ], [])->hydrate();

        $this->assertTrue($result['transparentBody']);
        $this->assertSame(['cols' => 12], $result['customRow']['col']);
    }

    public function test_custom_row_table_attributes_cannot_override_custom_row(): void
    {
        $result = $this->makeStub([
            'customRow' => [
                'name' => 'custom-card',
                'tableAttributes' => ['customRow' => ['name' => 'other']],
            ],
        ], [])->hydrate();

        $this->assertSame('custom-card', $result['customRow']['name']);
    }
}
