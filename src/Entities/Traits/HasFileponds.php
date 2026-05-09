<?php

namespace Unusualify\Modularous\Entities\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Unusualify\Modularous\Entities\Filepond;
use Unusualify\Modularous\Entities\Traits\Core\ChangeRelationships;

trait HasFileponds
{
    use ChangeRelationships;

    /**
     * The deleted fileponds relationship.
     */
    protected Collection $deletedFileponds;

    /**
     * The new fileponds relationship.
     */
    protected Collection $newFileponds;

    public function initializeHasFileponds()
    {
        $this->deletedFileponds = Collection::make();
        $this->newFileponds = Collection::make();
    }

    public function getFilepondableClass()
    {
        if (! $this->filepondableClass) {
            return $this;
        }

        $class = new $this->filepondableClass;

        $class->setAttribute($this->getKeyName(), $this->getKey());
        $class->fill($this->getAttributes());
        $class->setRelations($this->getRelations());

        return $class;
    }

    public function fileponds(): MorphMany
    {
        $filepondableClass = $this->getFilepondableClass();

        return $filepondableClass->morphMany(
            Filepond::class,
            'filepondable'
        );
    }

    /**
     * @return Filepond[]
     */
    public function getFileponds()
    {
        return $this->fileponds()->get();
    }

    public function hasFilepond($role = null)
    {
        return (bool) $role
            ? $this->fileponds()->where('role', $role)->exists()
            : $this->fileponds()->exists();
    }

    public function addFilepondsAsChanged($fileponds)
    {
        $this->mergeChangedRelationships('fileponds', $fileponds);
    }

    public function setDeletedFilepondsAsChanged($fileponds)
    {
        $this->deletedFileponds = $fileponds;
    }

    public function mergeDeletedFilepondsAsChanged($fileponds)
    {
        $this->deletedFileponds = $this->deletedFileponds->merge($fileponds);
    }

    public function setNewFilepondsAsChanged($fileponds)
    {
        $this->newFileponds = $fileponds;
    }

    public function hasDeletedFileponds()
    {
        return ! empty($this->deletedFileponds);
    }

    public function hasNewFileponds()
    {
        return ! empty($this->newFileponds);
    }

    public function getDeletedFileponds()
    {
        return $this->deletedFileponds;
    }

    public function getNewFileponds()
    {
        return $this->newFileponds;
    }
}
