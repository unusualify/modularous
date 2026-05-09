<?php

namespace Modules\Cms\Entities\Revisions;

use Modules\Cms\Entities\Page;
use Unusualify\Modularous\Entities\Revision;

class PageRevision extends Revision
{
    protected $fillable = [
        'page_id',
        'payload',
        'user_id',
        'source_id',
        'status',
        'approved_at',
        'approved_by',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function getTable(): string
    {
        return modularousConfig('tables.cms_pages_revisions', 'um_cms_pages_revisions');
    }
}
