<?php

namespace Unusualify\Modularity\Entities\Enums;

enum RevisionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public static function defaultApproved(): self
    {
        return self::Approved;
    }
}
