<?php

namespace Modules\Cms\Support;

/**
 * Detects parent/child relationships between normalized public paths (multi-segment slugs).
 *
 * Policy: overlapping segment prefixes are **warnings**, not hard errors — both URLs may be valid,
 * but routing/order and UX should be reviewed.
 */
final class CmsPublicPathHierarchy
{
    /**
     * True when {@see $a} and {@see $b} are distinct and one path extends the other with an extra `/...` segment.
     * Root-only paths are ignored to avoid flagging every page as under "/".
     */
    public static function segmentsOverlapAsPrefix(string $normalizedA, string $normalizedB): bool
    {
        $a = trim($normalizedA, '/');
        $b = trim($normalizedB, '/');

        if ($a === '' || $b === '' || $a === $b) {
            return false;
        }

        return str_starts_with($b, $a . '/') || str_starts_with($a, $b . '/');
    }
}
