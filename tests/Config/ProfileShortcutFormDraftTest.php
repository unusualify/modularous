<?php

namespace Unusualify\Modularous\Tests\Config;

use Unusualify\Modularous\Tests\TestCase;

/**
 * The "Upload Profile Image" dialog (layouts/Main.vue) submits the
 * `profile_shortcut` draft. Its avatar must accept an empty list so the
 * user can remove the profile picture there.
 */
class ProfileShortcutFormDraftTest extends TestCase
{
    public function test_profile_shortcut_avatar_is_not_required(): void
    {
        // Deferred config is not loaded in the test app; read the package default directly.
        $drafts = require dirname(__DIR__, 2) . '/config/defers/form_drafts.php';
        $avatar = $drafts['profile_shortcut']['avatar'] ?? null;

        $this->assertIsArray($avatar);

        $rules = is_array($avatar['rules'] ?? null)
            ? $avatar['rules']
            : explode('|', (string) ($avatar['rules'] ?? ''));

        foreach ($rules as $rule) {
            $this->assertStringStartsNotWith('required', (string) $rule);
        }
    }
}
