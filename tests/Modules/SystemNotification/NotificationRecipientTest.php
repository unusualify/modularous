<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Modules\SystemNotification;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\SystemNotification\Entities\Notification;
use Modules\SystemUser\Entities\Company;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Tests\ModelTestCase;

class NotificationRecipientTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->uuidMorphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    private function createNotificationFor(User $user): Notification
    {
        return Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'Tests\\FakeNotification',
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Hello'],
        ]);
    }

    private function createUser(?Company $company = null): User
    {
        return User::create([
            'name' => 'Jane',
            'surname' => 'Doe',
            'email' => 'jane@example.com',
            'company_id' => $company?->id,
        ]);
    }

    public function test_recipient_relation_selects_company_name_without_loading_company(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $user = $this->createUser($company);
        $this->createNotificationFor($user);

        $notification = Notification::with('recipient')->firstOrFail();

        $this->assertTrue($notification->relationLoaded('recipient'));
        $this->assertTrue($notification->recipient->relationLoaded('fileponds'));
        $this->assertFalse($notification->recipient->relationLoaded('company'));
        $this->assertSame('Acme', $notification->recipient->getAttributes()['recipient_company_name']);
    }

    public function test_recipient_summary_queries_do_not_grow_with_rows(): void
    {
        $createRow = function (int $i): void {
            $company = Company::create(['name' => "Company{$i}"]);
            $this->createNotificationFor(
                User::create(['name' => "User{$i}", 'email' => "user{$i}@example.com", 'company_id' => $company->id])
            );
        };

        foreach (range(1, 5) as $i) {
            $createRow($i);
        }

        DB::enableQueryLog();
        Notification::with('recipient')->get()->each(fn (Notification $n) => $n->recipient_summary);
        $fiveRows = count(DB::getQueryLog());

        $createRow(6);

        DB::flushQueryLog();
        Notification::with('recipient')->get()->each(fn (Notification $n) => $n->recipient_summary);

        $this->assertSame($fiveRows, count(DB::getQueryLog()));
    }

    public function test_recipient_summary_returns_compact_payload(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $user = $this->createUser($company);
        $this->createNotificationFor($user);

        $summary = Notification::with('recipient')->firstOrFail()->recipient_summary;

        $this->assertSame([
            'id' => $user->id,
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'company_name' => 'Acme',
            'avatar' => $user->avatar,
        ], $summary);
        $this->assertArrayNotHasKey('name', $summary);
        $this->assertArrayNotHasKey('title', $summary);
    }

    public function test_recipient_summary_without_company(): void
    {
        $user = $this->createUser();
        $this->createNotificationFor($user);

        $summary = Notification::with('recipient')->firstOrFail()->recipient_summary;

        $this->assertNull($summary['company_name']);
    }

    public function test_recipient_summary_ignores_soft_deleted_company(): void
    {
        $company = Company::create(['name' => 'Acme']);
        $this->createNotificationFor($this->createUser($company));
        $company->delete();

        $summary = Notification::with('recipient')->firstOrFail()->recipient_summary;

        $this->assertNull($summary['company_name']);
    }

    public function test_recipient_summary_is_null_when_relation_not_loaded(): void
    {
        $this->createNotificationFor($this->createUser());

        $notification = Notification::query()->firstOrFail();

        $this->assertNull($notification->recipient_summary);
        $this->assertFalse($notification->relationLoaded('recipient'));
    }

    public function test_recipient_summary_is_null_when_notifiable_missing(): void
    {
        $user = $this->createUser();
        $this->createNotificationFor($user);
        $user->delete();

        $this->assertNull(Notification::with('recipient')->firstOrFail()->recipient_summary);
    }
}
