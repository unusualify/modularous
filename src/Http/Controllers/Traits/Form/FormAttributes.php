<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Form;

use Illuminate\Support\Collection;

trait FormAttributes
{
    /**
     * @var array
     */
    protected $defaultFormAttributes = [];

    /**
     * Get the form attributes
     */
    public function getFormAttributes(): array
    {
        if ((bool) $this->config) {
            try {
                return Collection::make(
                    array_merge_recursive_preserve($this->defaultFormAttributes, object_to_array($this->getConfigFieldsByRoute('form_options', [])))
                )->toArray();
            } catch (\Throwable $th) {
                return [];
            }
        }

        return [];
    }

    public function addFormAppendsFormAttributes(): array
    {
        return $this->getConfigFieldsByRoute('form_appends', []);
    }

    protected function addFormWithsFormAttributes(): array
    {
        $formWith = [];
        $model = $this->repository->getModel();

        if (method_exists($model, 'hasRelation') || method_exists($model, 'definedRelations')) {
            $formWith = $this->mergeIndexWiths(
                $formWith,
                $this->resolveHeaderWiths($this->getConfigFieldsByRoute('form_with', []), $model)
            );
        }

        return $formWith;
    }
}
