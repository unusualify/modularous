<?php

namespace Unusualify\Modularity\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Unusualify\Modularity\Facades\Modularity;

trait RevisionsTrait
{
    protected bool $skipRevisionCreation = false;
    protected ?int $pendingSourceRevisionId = null;

    public function afterSaveRevisionsTrait($object, $fields): void
    {
        $this->createRevisionIfNeeded($object, $fields);
    }

        /**
     * @param Model $object
     * @param array $fields
     * @param array $schema
     * @return array
     */
    public function getFormFieldsRevisionsTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema
        if (isset($schema['revisionable_id'])) {
            $fields['revisionable_id'] = $object?->id;
        }

        return $fields;
    }

    public function createRevisionIfNeeded($object, array $fields): array
    {
        if ($this->skipRevisionCreation) {
            return $fields;
        }

        $lastRevision = $object->revisions()->latest('id')->first();
        $lastRevisionPayload = json_decode($lastRevision->payload ?? '{}', true) ?: [];

        $fullPayload = array_replace_recursive($lastRevisionPayload, $fields);

        if ($fullPayload !== $lastRevisionPayload) {
            $userId = Auth::guard(Modularity::getAuthGuardName())->id() ?? Auth::id();

            $object->revisions()->create([
                'payload' => json_encode($fullPayload),
                'user_id' => $userId,
                'source_revision_id' => $this->pendingSourceRevisionId,
            ]);
        }

        if (isset($object->limitRevisions) && (int) $object->limitRevisions > 0) {
            $object->deleteSpecificRevisions((int) $object->limitRevisions);
        }

        return $fields;
    }

    public function preview(int $id, array $fields)
    {
        $object = $this->model->findOrFail($id);

        return $this->hydrateObject($object, $fields);
    }

    public function previewForRevision(int $id, int $revisionId)
    {
        $object = $this->model->findOrFail($id);
        $revision = $object->revisions()->where('id', $revisionId)->firstOrFail();
        $fields = json_decode($revision->payload, true) ?: [];

        return $this->hydrateObject($this->model->newInstance()->setAttribute('id', $id), $fields);
    }

    public function restoreRevision(int $id, int $revisionId)
    {
        $object = $this->model->findOrFail($id);
        $revision = $object->revisions()->where('id', $revisionId)->firstOrFail();
        $fields = json_decode($revision->payload, true) ?: [];

        // Skip auto-revision creation during update so we can force-create one below,
        // ensuring a restore is always recorded even when content is identical to the latest revision.
        $this->skipRevisionCreation = true;
        $this->update($id, $fields);
        $this->skipRevisionCreation = false;

        $userId = Auth::guard(Modularity::getAuthGuardName())->id() ?? Auth::id();
        $object->revisions()->create([
            'payload' => json_encode($fields),
            'user_id' => $userId,
            'source_revision_id' => $revisionId,
        ]);

        return $this->model->findOrFail($id);
    }

    public function getRevisionPayload(int $id, int $revisionId): array
    {
        $object = $this->model->findOrFail($id);
        $revision = $object->revisions()->where('id', $revisionId)->firstOrFail();

        return json_decode($revision->payload, true) ?: [];
    }

    public function getCountForMine(): int
    {
        $query = $this->model->newQuery();

        return $this->filter($query, $this->countScope)->mine()->count();
    }

    public function getCountByStatusSlugRevisionsTrait(string $slug): int|bool
    {
        if ($slug === 'mine') {
            return $this->getCountForMine();
        }

        return false;
    }

    protected function hydrateObject($object, array $fields)
    {
        $fields = $this->prepareFieldsBeforeSave($object, $fields);
        $object->fill(Arr::except($fields, $this->getReservedFields()));

        return $this->hydrate($object, $fields);
    }

    public function getRevisions(int $id)
    {
        $revisionModel = $this->model->getRevisionModel();
        $revisions = $revisionModel::where($this->model->getForeignKey(), $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $revisions;
    }
}
