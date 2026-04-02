<?php

namespace Unusualify\Modularity\Tests\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Revision;
use Unusualify\Modularity\Entities\Traits\HasRevisions;
use Unusualify\Modularity\Repositories\Traits\RevisionsTrait;
use Unusualify\Modularity\Tests\RepositoryTestCase;

class RevisionsTraitTest extends RepositoryTestCase
{
    use RefreshDatabase;

    protected TestRevisionsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_rt_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('test_rt_article_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_rt_article_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('source_revision_id')->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();
        });

        $this->repository = new TestRevisionsRepository(new TestRtArticle());
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_rt_article_revisions');
        Schema::dropIfExists('test_rt_articles');
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // createRevisionIfNeeded()
    // -------------------------------------------------------------------------

    public function test_creates_revision_when_payload_changes(): void
    {
        $article = TestRtArticle::create(['title' => 'Original']);

        $this->repository->createRevisionIfNeeded($article, ['title' => 'Original']);

        $this->assertCount(1, $article->revisions);
    }

    public function test_skips_revision_when_payload_is_unchanged(): void
    {
        $article = TestRtArticle::create(['title' => 'Same']);

        // First call — creates revision
        $this->repository->createRevisionIfNeeded($article, ['title' => 'Same']);
        $this->assertCount(1, $article->revisions);

        // Second call with identical payload — no new revision
        $this->repository->createRevisionIfNeeded($article->fresh(), ['title' => 'Same']);
        $this->assertCount(1, $article->fresh()->revisions);
    }

    public function test_creates_new_revision_when_payload_differs_from_last(): void
    {
        $article = TestRtArticle::create(['title' => 'First']);

        $this->repository->createRevisionIfNeeded($article, ['title' => 'First']);
        $this->assertCount(1, $article->revisions);

        $this->repository->createRevisionIfNeeded($article->fresh(), ['title' => 'Second']);
        $this->assertCount(2, $article->fresh()->revisions);
    }

    public function test_skips_revision_when_skip_revision_creation_flag_is_true(): void
    {
        $article = TestRtArticle::create(['title' => 'Skip me']);

        $this->repository->setSkipRevisionCreation(true);
        $this->repository->createRevisionIfNeeded($article, ['title' => 'Skip me']);

        $this->assertCount(0, $article->revisions);
    }

    public function test_sets_source_revision_id_when_pending_source_is_provided(): void
    {
        $article = TestRtArticle::create(['title' => 'Post']);

        $sourceRevision = TestRtArticleRevision::create([
            'test_rt_article_id' => $article->id,
            'payload' => json_encode(['title' => 'Source']),
        ]);

        $this->repository->setPendingSourceRevisionId($sourceRevision->id);
        $this->repository->createRevisionIfNeeded($article, ['title' => 'Post']);

        $created = $article->revisions()->latest('id')->first();
        $this->assertEquals($sourceRevision->id, $created->source_revision_id);
    }

    // -------------------------------------------------------------------------
    // restoreRevision() — regression test for the content-equality bug
    // -------------------------------------------------------------------------

    /**
     * Regression test: restoring a revision whose content is identical to the
     * most recent revision must still record a new revision entry.
     *
     * Before the fix, createRevisionIfNeeded() deduplication prevented
     * revision creation when content was unchanged (e.g. restoring to the
     * same version that is already current).
     */
    public function test_restore_always_creates_revision_even_when_content_is_identical_to_latest(): void
    {
        $article = TestRtArticle::create(['title' => 'Original']);

        // Create an initial revision that matches the current content exactly
        $existingRevision = TestRtArticleRevision::create([
            'test_rt_article_id' => $article->id,
            'payload' => json_encode(['title' => 'Original']),
        ]);

        $this->assertCount(1, $article->revisions);

        // Restore the revision — content is identical to the latest revision.
        // Without the fix this would silently skip creation.
        $this->repository->restoreRevision($article->id, $existingRevision->id);

        $this->assertCount(2, $article->fresh()->revisions);
    }

    public function test_restore_sets_source_revision_id_on_created_revision(): void
    {
        $article = TestRtArticle::create(['title' => 'Hello']);

        $sourceRevision = TestRtArticleRevision::create([
            'test_rt_article_id' => $article->id,
            'payload' => json_encode(['title' => 'Hello']),
        ]);

        $this->repository->restoreRevision($article->id, $sourceRevision->id);

        $newRevision = $article->fresh()->revisions()->latest('id')->first();
        $this->assertEquals($sourceRevision->id, $newRevision->source_revision_id);
    }

    public function test_restore_applies_revision_fields_to_model(): void
    {
        $article = TestRtArticle::create(['title' => 'Current']);

        $targetRevision = TestRtArticleRevision::create([
            'test_rt_article_id' => $article->id,
            'payload' => json_encode(['title' => 'Restored Title']),
        ]);

        // Add a newer revision so the target is not the latest
        TestRtArticleRevision::create([
            'test_rt_article_id' => $article->id,
            'payload' => json_encode(['title' => 'Current']),
        ]);

        $this->repository->restoreRevision($article->id, $targetRevision->id);

        $this->assertEquals('Restored Title', $article->fresh()->title);
    }

    public function test_restore_returns_updated_model(): void
    {
        $article = TestRtArticle::create(['title' => 'Before']);

        $revision = TestRtArticleRevision::create([
            'test_rt_article_id' => $article->id,
            'payload' => json_encode(['title' => 'Before']),
        ]);

        $returned = $this->repository->restoreRevision($article->id, $revision->id);

        $this->assertInstanceOf(TestRtArticle::class, $returned);
        $this->assertEquals($article->id, $returned->id);
    }

    // -------------------------------------------------------------------------
    // getRevisionPayload()
    // -------------------------------------------------------------------------

    public function test_get_revision_payload_returns_decoded_array(): void
    {
        $article = TestRtArticle::create(['title' => 'Payload test']);

        $revision = TestRtArticleRevision::create([
            'test_rt_article_id' => $article->id,
            'payload' => json_encode(['title' => 'Stored', 'body' => 'Content']),
        ]);

        $payload = $this->repository->getRevisionPayload($article->id, $revision->id);

        $this->assertEquals(['title' => 'Stored', 'body' => 'Content'], $payload);
    }

    public function test_get_revision_payload_returns_empty_array_for_empty_payload(): void
    {
        $article = TestRtArticle::create(['title' => 'Empty']);

        $revision = TestRtArticleRevision::create([
            'test_rt_article_id' => $article->id,
            'payload' => null,
        ]);

        $payload = $this->repository->getRevisionPayload($article->id, $revision->id);

        $this->assertEquals([], $payload);
    }

    // -------------------------------------------------------------------------
    // afterSaveRevisionsTrait() — invoked via afterSave hook
    // -------------------------------------------------------------------------

    public function test_after_save_hook_creates_revision_on_first_save(): void
    {
        $article = TestRtArticle::create(['title' => 'New post']);

        $this->repository->afterSave($article, ['title' => 'New post']);

        $this->assertCount(1, $article->revisions);
    }

    public function test_after_save_hook_prunes_revisions_beyond_limit(): void
    {
        $article = TestRtArticle::create(['title' => 'Limited']);
        // Set limitRevisions on the instance — afterSaveRevisionsTrait checks $object->limitRevisions
        $article->limitRevisions = 2;

        // Manually create 3 existing revisions
        foreach (range(1, 3) as $i) {
            TestRtArticleRevision::create([
                'test_rt_article_id' => $article->id,
                'payload' => json_encode(['title' => "V{$i}"]),
                'created_at' => now()->subMinutes(10 - $i),
                'updated_at' => now()->subMinutes(10 - $i),
            ]);
        }

        // afterSave creates a 4th revision and then prunes down to limitRevisions=2
        $this->repository->afterSave($article, ['title' => 'V4']);

        $this->assertCount(2, $article->fresh()->revisions);
    }
}

// ---------------------------------------------------------------------------
// Stubs
// ---------------------------------------------------------------------------

class TestRtArticle extends Model
{
    use HasRevisions;

    protected $table = 'test_rt_articles';
    protected $fillable = ['title'];
    protected $revisionModel = TestRtArticleRevision::class;
}

class TestRtArticleRevision extends Revision
{
    protected $table = 'test_rt_article_revisions';

    // Empty $fillable + $guarded = [] → all fields are mass-assignable,
    // including created_at/updated_at for ordering tests, without triggering
    // the parent Revision constructor's foreign-key auto-append (count == 3).
    protected $fillable = [];
    protected $guarded = [];
}

/**
 * Minimal repository stub using RevisionsTrait.
 *
 * The real Repository::update() involves DB transactions and unrelated
 * infrastructure. This stub replaces it with a direct fill + save so
 * RevisionsTrait logic can be tested in isolation.
 */
class TestRevisionsRepository
{
    use RevisionsTrait;

    public function __construct(public Model $model) {}

    /**
     * Simplified update: fill the model and persist, then fire afterSave hooks.
     */
    public function update(int $id, array $fields, $schema = null, $options = []): bool
    {
        $object = $this->model->findOrFail($id);
        $object->fill(array_intersect_key($fields, array_flip($object->getFillable())));
        $object->save();
        $this->afterSave($object, $fields);

        return true;
    }

    /**
     * Fire all afterSave* trait methods (mirrors Repository::afterSave()).
     */
    public function afterSave(Model $object, array $fields): void
    {
        $this->afterSaveRevisionsTrait($object, $fields);
    }

    public function setSkipRevisionCreation(bool $value): void
    {
        $this->skipRevisionCreation = $value;
    }

    public function setPendingSourceRevisionId(?int $id): void
    {
        $this->pendingSourceRevisionId = $id;
    }
}
