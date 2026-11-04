<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Console;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Unusualify\Modularous\Console\Update\UpgradePermissionNamespacesCommand;
use Unusualify\Modularous\Tests\TestCase;

class UpgradePermissionNamespacesCommandTest extends TestCase
{
    private const GUARD = 'modularous';

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permissions');

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });
    }

    public function test_renames_single_module_permissions_in_place(): void
    {
        $id = $this->permission('press-release_view', roles: [1]);

        $this->runCommand(['--owner' => ['user=SystemUser']]);

        $this->assertSame('press_release::press-release_view', $this->nameOf($id));
        $this->assertSame([1], $this->rolesOf('press_release::press-release_view'));
    }

    public function test_shared_route_goes_to_owner_and_is_mirrored_with_assignments(): void
    {
        $id = $this->permission('user_view', roles: [1, 2], users: [7]);

        $this->runCommand(['--owner' => ['user=SystemUser']]);

        $this->assertSame('system_user::user_view', $this->nameOf($id));
        $this->assertSame([1, 2], $this->rolesOf('system_user::user_view'));
        $this->assertSame([1, 2], $this->rolesOf('user::user_view'));
        $this->assertSame([7], $this->usersOf('user::user_view'));
    }

    public function test_shared_route_without_owner_fails_without_writing(): void
    {
        $this->permission('user_view');
        $this->permission('press-release_view');

        $exitCode = $this->runCommand([]);

        $this->assertSame(1, $exitCode);
        $this->assertEqualsCanonicalizing(['user_view', 'press-release_view'], $this->allNames());
    }

    public function test_skips_global_unmapped_and_namespaced_names(): void
    {
        $this->permission('dashboard');
        $this->permission('payments_view');
        $this->permission('faq::faq_view');

        $this->runCommand(['--owner' => ['user=SystemUser']]);

        $this->assertEqualsCanonicalizing(['dashboard', 'payments_view', 'faq::faq_view'], $this->allNames());
    }

    public function test_merges_into_an_already_existing_namespaced_permission(): void
    {
        $this->permission('faq::faq_view', roles: [1]);
        $this->permission('faq_view', roles: [2]);

        $this->runCommand(['--owner' => ['user=SystemUser']]);

        $this->assertSame(['faq::faq_view'], $this->allNames());
        $this->assertSame([1, 2], $this->rolesOf('faq::faq_view'));
    }

    public function test_running_twice_changes_nothing(): void
    {
        $this->permission('user_view', roles: [1]);
        $this->permission('faq_view', roles: [1]);

        $this->runCommand(['--owner' => ['user=SystemUser']]);
        $snapshot = [$this->allNames(), DB::table('role_has_permissions')->count()];

        $this->runCommand(['--owner' => ['user=SystemUser']]);

        $this->assertSame($snapshot, [$this->allNames(), DB::table('role_has_permissions')->count()]);
    }

    public function test_dry_run_does_not_write(): void
    {
        $this->permission('user_view', roles: [1]);

        $output = new BufferedOutput;
        $this->runCommand(['--owner' => ['user=SystemUser'], '--dry-run' => true], $output);

        $this->assertSame(['user_view'], $this->allNames());
        $this->assertStringContainsString('system_user::user_view', $output->fetch());
    }

    public function test_rejects_owner_that_does_not_define_the_route(): void
    {
        $this->permission('user_view');

        $this->assertSame(1, $this->runCommand(['--owner' => ['user=Faq']]));
        $this->assertSame(['user_view'], $this->allNames());
    }

    private function runCommand(array $options, ?BufferedOutput $output = null): int
    {
        $command = $this->app->make(FakeRoutesUpgradePermissionNamespacesCommand::class);
        $command->setLaravel($this->app);

        return $command->run(new ArrayInput($options), $output ?? new BufferedOutput);
    }

    private function permission(string $name, array $roles = [], array $users = []): int
    {
        $id = (int) DB::table('permissions')->insertGetId(['name' => $name, 'guard_name' => self::GUARD]);

        foreach ($roles as $roleId) {
            DB::table('role_has_permissions')->insert(['permission_id' => $id, 'role_id' => $roleId]);
        }

        foreach ($users as $userId) {
            DB::table('model_has_permissions')->insert(['permission_id' => $id, 'model_type' => 'User', 'model_id' => $userId]);
        }

        return $id;
    }

    private function nameOf(int $id): ?string
    {
        return DB::table('permissions')->where('id', $id)->value('name');
    }

    private function allNames(): array
    {
        return DB::table('permissions')->orderBy('id')->pluck('name')->all();
    }

    private function rolesOf(string $name): array
    {
        return DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('permissions.name', $name)
            ->orderBy('role_id')
            ->pluck('role_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function usersOf(string $name): array
    {
        return DB::table('model_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
            ->where('permissions.name', $name)
            ->orderBy('model_id')
            ->pluck('model_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}

class FakeRoutesUpgradePermissionNamespacesCommand extends UpgradePermissionNamespacesCommand
{
    protected function routeModuleMap(): array
    {
        return [
            'user' => ['SystemUser', 'User'],
            'press-release' => ['PressRelease'],
            'faq' => ['Faq'],
        ];
    }
}
