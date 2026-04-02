<?php

namespace Unusualify\Modularity\Tests\Services\Security;

use Illuminate\Contracts\Auth\Authenticatable;
use Unusualify\Modularity\Services\Security\SecurityService;
use Unusualify\Modularity\Tests\TestCase;

class SecurityServiceTest extends TestCase
{
    public function test_user_requires_mfa_for_configured_roles(): void
    {
        config()->set('modularity.security.mfa.enabled', true);
        config()->set('modularity.security.mfa.provider', 'google_totp');
        config()->set('modularity.security.mfa.required_roles', ['admin']);

        $user = \Mockery::mock(Authenticatable::class);
        $user->shouldReceive('hasRole')->with('admin')->andReturn(true);
        $user->google_2fa_enabled = false;
        $user->google_2fa_secret = null;

        $service = new SecurityService;

        $this->assertTrue($service->userRequiresMfa($user));
        $this->assertFalse($service->userHasEnabledMfa($user));
    }

    public function test_email_otp_provider_does_not_require_google_2fa_columns(): void
    {
        config()->set('modularity.security.mfa.enabled', true);
        config()->set('modularity.security.mfa.provider', 'email_otp');

        $user = \Mockery::mock(Authenticatable::class);

        $service = new SecurityService;

        $this->assertTrue($service->userHasEnabledMfa($user));
    }

    public function test_field_permission_checks_are_applied(): void
    {
        config()->set('modularity.security.critical_field_permissions.canonical_url', 'cms-seo-override_edit');

        $user = \Mockery::mock(Authenticatable::class);
        $user->shouldReceive('can')->with('cms-seo-override_edit')->andReturn(true);

        $service = new SecurityService;

        $this->assertTrue($service->canWriteField($user, 'canonical_url'));
        $this->assertTrue($service->canWriteField($user, 'non_critical_field'));
    }
}
