<?php

namespace Unusualify\Modularous\Tests\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\Revision;
use Unusualify\Modularous\Tests\ModelTestCase;

class RevisionTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_concrete_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('article_id')->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_concrete_revisions');
        parent::tearDown();
    }

    public function test_timestamps_are_enabled(): void
    {
        $revision = new TestConcreteRevision;

        $this->assertTrue($revision->timestamps);
    }

    public function test_fillable_contains_payload_user_id_and_source_id(): void
    {
        $revision = new TestConcreteRevision;

        $this->assertContains('payload', $revision->getFillable());
        $this->assertContains('user_id', $revision->getFillable());
        $this->assertContains('source_id', $revision->getFillable());
    }

    public function test_constructor_does_not_auto_append_foreign_key_when_four_fillable_items(): void
    {
        // TestConcreteRevision explicitly declares 4 fillable items, so no auto-append
        $revision = new TestConcreteRevision;

        $this->assertCount(4, $revision->getFillable());
        $this->assertContains('article_id', $revision->getFillable());
    }

    public function test_constructor_auto_appends_foreign_key_from_class_name_when_three_fillable_items(): void
    {
        // When $fillable has exactly 3 items, the constructor appends a derived foreign key
        $revision = new TestThreeItemFillableRevision;

        $fillable = $revision->getFillable();
        $this->assertCount(4, $fillable);
    }

    public function test_get_by_user_attribute_returns_system_when_no_user_associated(): void
    {
        $revision = TestConcreteRevision::create([
            'payload' => json_encode(['title' => 'Test']),
            'user_id' => null,
        ]);

        // Accessor is defined as getByUserAttribute → accessed as $model->by_user
        $this->assertEquals('System', $revision->by_user);
    }

    public function test_get_by_user_attribute_returns_user_name(): void
    {
        $userId = DB::table('um_users')->insertGetId([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $revision = TestConcreteRevision::create([
            'payload' => json_encode(['title' => 'Test']),
            'user_id' => $userId,
        ]);

        // Reload to trigger eager loading of user
        $revision = TestConcreteRevision::find($revision->id);

        $this->assertEquals('Jane Doe', $revision->by_user);
    }

    public function test_is_draft_returns_true_when_cms_save_type_starts_with_draft_revision(): void
    {
        $revision = new TestConcreteRevision;
        $revision->payload = json_encode(['cmsSaveType' => 'draft-revision-auto']);

        $this->assertTrue($revision->isDraft());
    }

    public function test_is_draft_returns_true_for_plain_draft_revision_value(): void
    {
        $revision = new TestConcreteRevision;
        $revision->payload = json_encode(['cmsSaveType' => 'draft-revision']);

        $this->assertTrue($revision->isDraft());
    }

    public function test_is_draft_returns_false_when_cms_save_type_is_published(): void
    {
        $revision = new TestConcreteRevision;
        $revision->payload = json_encode(['cmsSaveType' => 'published']);

        $this->assertFalse($revision->isDraft());
    }

    public function test_is_draft_returns_false_when_cms_save_type_is_absent(): void
    {
        $revision = new TestConcreteRevision;
        $revision->payload = json_encode(['title' => 'No save type here']);

        $this->assertFalse($revision->isDraft());
    }

    public function test_user_relationship_is_eager_loaded_via_with(): void
    {
        $reflection = new \ReflectionClass(TestConcreteRevision::class);
        $property = $reflection->getProperty('with');
        $property->setAccessible(true);

        $with = $property->getValue(new TestConcreteRevision);

        $this->assertContains('user', $with);
    }
}

class TestConcreteRevision extends Revision
{
    protected $table = 'test_concrete_revisions';

    // 4 items: constructor skips auto-append
    protected $fillable = ['payload', 'user_id', 'source_id', 'article_id'];
}

class TestThreeItemFillableRevision extends Revision
{
    protected $table = 'test_concrete_revisions';

    // Exactly 3 items: constructor will auto-append a foreign key
    protected $fillable = ['payload', 'user_id', 'source_id'];
}
