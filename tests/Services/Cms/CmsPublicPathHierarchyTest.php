<?php

namespace Unusualify\Modularity\Tests\Services\Cms;

use Modules\Cms\Support\CmsPublicPathHierarchy;
use Unusualify\Modularity\Tests\TestCase;

class CmsPublicPathHierarchyTest extends TestCase
{
    public function test_detects_parent_child_segments(): void
    {
        $this->assertTrue(CmsPublicPathHierarchy::segmentsOverlapAsPrefix('/blog', '/blog/post'));
        $this->assertTrue(CmsPublicPathHierarchy::segmentsOverlapAsPrefix('/blog/post', '/blog'));
    }

    public function test_no_false_positive_on_shared_word_prefix(): void
    {
        $this->assertFalse(CmsPublicPathHierarchy::segmentsOverlapAsPrefix('/blog', '/blogging'));
    }

    public function test_equal_paths_not_overlap(): void
    {
        $this->assertFalse(CmsPublicPathHierarchy::segmentsOverlapAsPrefix('/same', '/same'));
    }

    public function test_root_does_not_flag_everything(): void
    {
        $this->assertFalse(CmsPublicPathHierarchy::segmentsOverlapAsPrefix('/', '/foo'));
    }
}
