<?php

namespace Unusualify\Modularity\Tests\Hydrates;

use Unusualify\Modularity\Hydrates\Inputs\FilepondAvatarHydrate;
use Unusualify\Modularity\Tests\TestCase;

class FilepondAvatarHydrateTest extends TestCase
{
    public function test_filepond_avatar_hydrate_sets_type_and_labels()
    {
        $input = [
            'type' => 'filepond-avatar',
            'name' => 'avatar'
        ];

        $h = new FilepondAvatarHydrate($input, null, null, true);

        $result = $h->render();

        $this->assertEquals('input-filepond-avatar', $result['type']);
        $this->assertFalse($result['credits']);
        $this->assertEquals(2, $result['max-files']);
        $this->assertEquals(2, $result['max']);
    }
}
