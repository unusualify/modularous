<?php

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Entities\UserOauth;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\OauthTrait;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class OauthTraitTest extends RepositoryTestCase
{
    use RefreshDatabase;

    protected OauthTestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // $this->createTables();
        $this->repository = new OauthTestRepository(new User);
    }

    private function createTables(): void
    {
        // users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->boolean('published')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        // user oauths table (default fallback name)
        Schema::create('um_user_oauths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('provider');
            $table->string('oauth_id');
            $table->string('token')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });
    }

    public function test_oauth_user_finds_by_email(): void
    {
        $user = User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'published' => true]);
        $oauthUser = (object) [
            'email' => 'alice@example.com',
        ];

        $found = $this->repository->oauthUser($oauthUser);
        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->id);
    }

    public function test_oauth_is_user_linked_true_and_false(): void
    {
        $user = User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'published' => true]);

        // Initially not linked
        $oauthUser = (object) [
            'id' => 'oauth-123',
            'email' => 'bob@example.com',
        ];
        $this->assertFalse($this->repository->oauthIsUserLinked($oauthUser, 'google'));

        // Link provider
        UserOauth::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'oauth_id' => 'oauth-123',
            'token' => 't0',
            'avatar' => 'http://a/b.jpg',
        ]);

        $this->assertTrue($this->repository->oauthIsUserLinked($oauthUser, 'google'));
        $this->assertFalse($this->repository->oauthIsUserLinked((object) ['id' => 'x', 'email' => 'bob@example.com'], 'google'));
    }

    public function test_oauth_update_provider_updates_token_and_avatar(): void
    {
        $user = User::create(['name' => 'Carol', 'email' => 'carol@example.com', 'published' => true]);
        UserOauth::create([
            'user_id' => $user->id,
            'provider' => 'github',
            'oauth_id' => 'gid-1',
            'token' => 'old-token',
            'avatar' => 'http://old.png',
        ]);

        $oauthUser = (object) [
            'id' => 'gid-1',
            'email' => 'carol@example.com',
            'token' => 'new-token',
            'avatar' => 'http://new.png',
        ];

        $returned = $this->repository->oauthUpdateProvider($oauthUser, 'github');
        $this->assertSame($user->id, $returned->id);

        $provider = UserOauth::where('user_id', $user->id)->where('provider', 'github')->first();
        $this->assertSame('new-token', $provider->token);
        $this->assertSame('http://new.png', $provider->avatar);
    }

    public function test_oauth_create_user_creates_with_published_true(): void
    {
        $oauthUser = (object) [
            'name' => 'Dave',
            'email' => 'dave@example.com',
        ];

        $created = $this->repository->oauthCreateUser($oauthUser);

        $this->assertNotNull($created);
        $this->assertSame('Dave', $created->name);
        $this->assertSame('dave@example.com', $created->email);
        $this->assertTrue((bool) $created->published);

        // idempotent on same user (should not create another)
        $again = $this->repository->oauthCreateUser($oauthUser);
        $this->assertSame($created->id, $again->id);
    }
}

class OauthTestRepository extends Repository
{
    use OauthTrait;

    public function __construct(User $model)
    {
        $this->model = $model;
    }
}
