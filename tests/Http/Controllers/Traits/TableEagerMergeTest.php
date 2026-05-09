<?php

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Unusualify\Modularous\Http\Controllers\Traits\Table\TableEager;
use Unusualify\Modularous\Tests\TestCase;

class TableEagerMergeTest extends TestCase
{
    public function test_merge_index_withs_merges_dotted_plain_into_existing_assoc_root(): void
    {
        $stub = new class
        {
            use TableEager;

            public function merge(array $base, array $incoming): array
            {
                return $this->mergeIndexWiths($base, $incoming);
            }
        };

        $result = $stub->merge(
            [
                'creator' => [
                    'roles',
                ],
            ],
            [
                'creator.company',
            ]
        );

        $this->assertSame(
            [
                'creator' => [
                    'roles',
                    'company',
                ],
            ],
            $result
        );
    }

    public function test_merge_index_withs_preserves_deeper_dotted_tail_under_assoc_root(): void
    {
        $stub = new class
        {
            use TableEager;

            public function merge(array $base, array $incoming): array
            {
                return $this->mergeIndexWiths($base, $incoming);
            }
        };

        $result = $stub->merge(
            [
                'creator' => [
                    'roles',
                ],
            ],
            [
                'creator.company.logo',
            ]
        );

        $this->assertSame(
            [
                'creator' => [
                    'roles',
                    'company.logo',
                ],
            ],
            $result
        );
    }

    public function test_merge_index_withs_collapses_plain_root_with_dotted_plain_paths(): void
    {
        $stub = new class
        {
            use TableEager;

            public function merge(array $base, array $incoming): array
            {
                return $this->mergeIndexWiths($base, $incoming);
            }
        };

        $result = $stub->merge(
            [
                'creator',
            ],
            [
                'creator.company',
                'creator.roles',
            ]
        );

        $this->assertSame(
            [
                'creator' => [
                    'roles',
                    'company',
                ],
            ],
            $result
        );
    }

    public function test_merge_index_withs_promotes_single_dotted_path_when_no_plain_siblings(): void
    {
        $stub = new class
        {
            use TableEager;

            public function merge(array $base, array $incoming): array
            {
                return $this->mergeIndexWiths($base, $incoming);
            }
        };

        $result = $stub->merge(
            [],
            [
                'creator.company',
            ]
        );

        $this->assertSame(
            [
                'creator' => [
                    'company',
                ],
            ],
            $result
        );
    }
}
