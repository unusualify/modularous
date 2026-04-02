<?php

namespace Unusualify\Modularity\Entities\Traits;

use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Unusualify\Modularity\Facades\Modularity;

trait HasRevisions
{
    /**
     * Defines the one-to-many relationship for revisions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions()
    {
        return $this->hasMany($this->getRevisionModel())->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to only include the current user's revisions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine($query)
    {
        $user = Auth::guard(Modularity::getAuthGuardName())->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('revisions', function ($query) {
            $query->where('user_id', Auth::guard(Modularity::getAuthGuardName())->id());
        });
    }

    /**
     * Returns an array of revisions for the CMS views.
     *
     * @return array
     */
    public function revisionsArray()
    {
        $revisions = $this->revisions; // ordered DESC (newest first)
        $total = $revisions->count();

        $versionMap = $revisions->mapWithKeys(function ($revision, $index) use ($total) {
            return [$revision->id => $total - $index];
        });

        return $revisions
            ->map(function ($revision, $index) use ($total, $versionMap) {
                $sourceLabel = $revision->source_revision_id && isset($versionMap[$revision->source_revision_id])
                    ? 'V' . $versionMap[$revision->source_revision_id]
                    : null;

                return [
                    'id' => $revision->id,
                    'author' => $revision->user->name ?? 'Unknown',
                    'datetime' => $revision->created_at->toIso8601String(),
                    'label' => 'V' . ($total - $index),
                    'source_label' => $sourceLabel,
                ];
            })
            ->toArray();
    }

    /**
     * Deletes revisions from specific collection position
     * Used to keep max revision on specific Twill's module.
     */
    public function deleteSpecificRevisions(int $maxRevisions): void
    {
        if (isset($this->limitRevisions) && $this->limitRevisions > 0) {
            $maxRevisions = $this->limitRevisions;
        }

        $this->revisions()->get()->slice($maxRevisions)->each->delete();
    }


    public function getRevisionModel()
    {
        if (property_exists($this, 'revisionModel') && is_string($this->revisionModel) && @class_exists($this->revisionModel)) {
            return $this->revisionModel;
        }

        $modelClass = get_class($this);
        $candidates = [
            preg_replace('/\\\\Entities\\\\([^\\\\]+)$/', '\\Entities\\Revisions\\$1Revision', $modelClass),
            modularityConfig('namespace') . "\\Models\\Revisions\\" . class_basename($this) . 'Revision',
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && @class_exists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException("Revision model could not be resolved for [{$modelClass}]. Define a \$revisionModel property.");
    }
}
