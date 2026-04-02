<?php

namespace Unusualify\Modularity\Tests\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Revision;
use Unusualify\Modularity\Entities\Traits\HasRevisions;
use Unusualify\Modularity\Tests\ModelTestCase;

class HasRevisionsTest extends ModelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_hr_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('test_hr_article_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_hr_article_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('source_revision_id')->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_hr_article_revisions');
        Schema::dropIfExists('test_hr_articles');
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Relationship
    // -------------------------------------------------------------------------

    public function test_model_uses_has_revisions_trait(): void
    {
        $traits = class_uses_recursive(TestHrArticle::class);

        $this->assertContains(HasRevisions::class, $traits);
    }

    public function test_revisions_method_returns_has_many_relation(): void
    {
        $article = TestHrArticle::create(['title' => 'Draft']);

        $this->assertInstanceOf(HasMany::class, $article->revisions());
    }

    public function test_revisions_are_ordered_descending_by_created_at(): void
    {
        $article = TestHrArticle::create(['title' => 'Post']);

        $older = TestHrArticleRevision::create([
            'test_hr_article_id' => $article->id,
            'payload' => json_encode(['title' => 'V1']),
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);

        $newer = TestHrArticleRevision::create([
            'test_hr_article_id' => $article->id,
            'payload' => json_encode(['title' => 'V2']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $revisions = $article->revisions()->get();

        $this->assertEquals($newer->id, $revisions->first()->id);
        $this->assertEquals($older->id, $revisions->last()->id);
    }

    // -------------------------------------------------------------------------
    // revisionsArray()
    // -------------------------------------------------------------------------

    public function test_revisions_array_returns_correct_keys(): void
    {
        $article = TestHrArticle::create(['title' => 'Post']);
        $userId = DB::table('um_users')->insertGetId([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        TestHrArticleRevision::create([
            'test_hr_article_id' => $article->id,
            'user_id' => $userId,
            'payload' => json_encode(['title' => 'Hello']),
        ]);

        $array = $article->revisionsArray();

        $this->assertIsArray($array);
        $this->assertCount(1, $array);
        $this->assertArrayHasKey('id', $array[0]);
        $this->assertArrayHasKey('author', $array[0]);
        $this->assertArrayHasKey('datetime', $array[0]);
        $this->assertArrayHasKey('label', $array[0]);
        $this->assertArrayHasKey('source_label', $array[0]);
    }

    public function test_revisions_array_assigns_version_labels_newest_first(): void
    {
        $article = TestHrArticle::create(['title' => 'Post']);

        TestHrArticleRevision::create([
            'test_hr_article_id' => $article->id,
            'payload' => json_encode(['title' => 'V1']),
            'created_at' => now()->subMinutes(20),
            'updated_at' => now()->subMinutes(20),
        ]);

        TestHrArticleRevision::create([
            'test_hr_article_id' => $article->id,
            'payload' => json_encode(['title' => 'V2']),
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);

        TestHrArticleRevision::create([
            'test_hr_article_id' => $article->id,
            'payload' => json_encode(['title' => 'V3']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $array = $article->revisionsArray();

        // revisionsArray() returns ordered DESC (newest first), with label V3, V2, V1
        $this->assertEquals('V3', $array[0]['label']);
        $this->assertEquals('V2', $array[1]['label']);
        $this->assertEquals('V1', $array[2]['label']);
    }

    public function test_revisions_array_source_label_is_null_when_no_source_revision(): void
    {
        $article = TestHrArticle::create(['title' => 'Post']);

        TestHrArticleRevision::create([
            'test_hr_article_id' => $article->id,
            'payload' => json_encode(['title' => 'Original']),
            'source_revision_id' => null,
        ]);

        $array = $article->revisionsArray();

        $this->assertNull($array[0]['source_label']);
    }

    public function test_revisions_array_source_label_reflects_source_version(): void
    {
        $article = TestHrArticle::create(['title' => 'Post']);

        $v1 = TestHrArticleRevision::create([
            'test_hr_article_id' => $article->id,
            'payload' => json_encode(['title' => 'Original']),
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);

        // V2 is a restore of V1, so source_revision_id = V1's id
        TestHrArticleRevision::create([
            'test_hr_article_id' => $article->id,
            'payload' => json_encode(['title' => 'Restored from V1']),
            'source_revision_id' => $v1->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $array = $article->revisionsArray();

        // V2 (index 0, newest) should have source_label 'V1'
        $this->assertEquals('V1', $array[0]['source_label']);
        // V1 (index 1, oldest) should have no source_label
        $this->assertNull($array[1]['source_label']);
    }

    // -------------------------------------------------------------------------
    // deleteSpecificRevisions()
    // -------------------------------------------------------------------------

    public function test_delete_specific_revisions_removes_oldest_beyond_limit(): void
    {
        $article = TestHrArticle::create(['title' => 'Post']);

        foreach (range(1, 5) as $i) {
            TestHrArticleRevision::create([
                'test_hr_article_id' => $article->id,
                'payload' => json_encode(['title' => "V{$i}"]),
                'created_at' => now()->subMinutes(10 - $i),
                'updated_at' => now()->subMinutes(10 - $i),
            ]);
        }

        $this->assertCount(5, $article->revisions);

        $article->deleteSpecificRevisions(3);

        $this->assertCount(3, $article->fresh()->revisions);
    }

    public function test_delete_specific_revisions_respects_model_limit_revisions_property(): void
    {
        $article = TestHrArticle::create(['title' => 'Limited']);
        // Set limitRevisions on the instance — deleteSpecificRevisions reads $this->limitRevisions
        $article->limitRevisions = 2;

        foreach (range(1, 4) as $i) {
            TestHrArticleRevision::create([
                'test_hr_article_id' => $article->id,
                'payload' => json_encode(['title' => "V{$i}"]),
                'created_at' => now()->subMinutes(10 - $i),
                'updated_at' => now()->subMinutes(10 - $i),
            ]);
        }

        // Model has limitRevisions = 2, so passing 10 should still trim to 2
        $article->deleteSpecificRevisions(10);

        $this->assertCount(2, $article->fresh()->revisions);
    }

    // -------------------------------------------------------------------------
    // scopeMine()
    // -------------------------------------------------------------------------

    public function test_scope_mine_returns_only_models_with_current_user_revisions(): void
    {
        $userId = DB::table('um_users')->insertGetId([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherUserId = DB::table('um_users')->insertGetId([
            'name' => 'Carol',
            'email' => 'carol@example.com',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $myArticle = TestHrArticle::create(['title' => 'My Article']);
        $otherArticle = TestHrArticle::create(['title' => 'Other Article']);

        TestHrArticleRevision::create([
            'test_hr_article_id' => $myArticle->id,
            'user_id' => $userId,
            'payload' => json_encode(['title' => 'Mine']),
        ]);

        TestHrArticleRevision::create([
            'test_hr_article_id' => $otherArticle->id,
            'user_id' => $otherUserId,
            'payload' => json_encode(['title' => 'Theirs']),
        ]);

        Auth::guard('modularity')->loginUsingId($userId);

        $mine = TestHrArticle::mine()->get();

        $this->assertCount(1, $mine);
        $this->assertEquals($myArticle->id, $mine->first()->id);
    }
}

// ---------------------------------------------------------------------------
// Stubs
// ---------------------------------------------------------------------------

class TestHrArticle extends Model
{
    use HasRevisions;

    protected $table = 'test_hr_articles';
    protected $fillable = ['title'];
    protected $revisionModel = TestHrArticleRevision::class;
}

class TestHrArticleRevision extends Revision
{
    protected $table = 'test_hr_article_revisions';

    // Empty $fillable + $guarded = [] → all fields are mass-assignable,
    // including created_at/updated_at for ordering tests, without triggering
    // the parent Revision constructor's foreign-key auto-append (which fires
    // only when count($fillable) == 3).
    protected $fillable = [];
    protected $guarded = [];
}
