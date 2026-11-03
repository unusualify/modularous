<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Helpers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Permission;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Tests\TestCase;

class GateAbilitiesHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
            });
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_permission_abilities_follow_the_users_permission_set(): void
    {
        $user = $this->mockUser(isSuperAdmin: false);
        $guardName = Guard::getDefaultName($user);

        $this->definePermissionAbilities($guardName, ['articles_view', 'articles_edit']);
        Permission::create(['name' => 'articles_view', 'guard_name' => 'other-guard']);

        $user->shouldReceive('getAllPermissions')->andReturn(collect([
            new Permission(['name' => 'articles_view', 'guard_name' => $guardName]),
            new Permission(['name' => 'articles_edit', 'guard_name' => 'other-guard']),
        ]));

        Gate::define('custom_open', static fn (?User $user = null) => true);
        Gate::define('custom_closed', static fn (?User $user = null) => false);

        $abilities = get_modularous_gate_abilities($user);

        $this->assertTrue($abilities['articles_view']);
        $this->assertFalse($abilities['articles_edit']);
        $this->assertTrue($abilities['custom_open']);
        $this->assertFalse($abilities['custom_closed']);
        $this->assertSame(array_keys(Gate::abilities()), array_keys($abilities));
    }

    public function test_superadmin_is_granted_every_permission_ability(): void
    {
        $user = $this->mockUser(isSuperAdmin: true);
        $guardName = Guard::getDefaultName($user);

        $this->definePermissionAbilities($guardName, ['articles_view', 'articles_edit']);

        $user->shouldReceive('getAllPermissions')->andReturn(collect());

        $abilities = get_modularous_gate_abilities($user);

        $this->assertTrue($abilities['articles_view']);
        $this->assertTrue($abilities['articles_edit']);
    }

    public function test_guest_falls_back_to_gate_checks(): void
    {
        Gate::define('custom_open', static fn (?User $user = null) => true);
        Gate::define('custom_closed', static fn (?User $user = null) => false);

        $abilities = get_modularous_gate_abilities(null);

        $this->assertTrue($abilities['custom_open']);
        $this->assertFalse($abilities['custom_closed']);
    }

    /**
     * @param list<string> $names
     */
    private function definePermissionAbilities(string $guardName, array $names): void
    {
        foreach ($names as $name) {
            Permission::create(['name' => $name, 'guard_name' => $guardName]);
            Gate::define($name, static fn () => false);
        }
    }

    private function mockUser(bool $isSuperAdmin): User|Mockery\MockInterface
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('getAttribute')->with('is_superadmin')->andReturn($isSuperAdmin);

        return $user;
    }
}
