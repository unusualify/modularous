<?php

namespace Unusualify\Modularity\Http\Controllers\Traits;

use Illuminate\Support\Facades\Config;

trait ManageForm
{
    use Form\FormAttributes,
        Form\FormSchema,
        Form\FormActions;

    /**
     * @var array
     */
    protected $formAttributes = [];

    /**
     * @param \Illuminate\Foundation\Application $app
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function __afterConstructManageForm($app, $request)
    {
        $this->defaultFormAttributes = (array) Config::get(modularityBaseKey() . '.default_form_attributes');

        // $this->formAttributes = array_merge_recursive_preserve($this->getFormAttributes(), $this->formAttributes ?? []);
    }

    public function preloadManageForm()
    {
        $this->formAttributes = array_merge_recursive_preserve($this->getFormAttributes(), $this->formAttributes ?? []);
    }

    protected function addWithsManageForm(): array
    {
        $counter = 0;

        $fetchFormWiths = $this->tableAttributes['editOnModal'] ?? true;

        if(!$fetchFormWiths){
            return [];
        }

        return collect(array_to_object($this->formSchema))->filter(function ($input) {
            // return $this->hasWithModel($item['type']);
            return in_array($input->type, [
                'treeview',
                'input-treeview',
                'select',
                'combobox',
                'autocomplete',
                // 'input-repeater',
            ]) && ! (isset($input->ext) && $input->ext == 'morphTo');
        })->mapWithKeys(function ($input, $key) use (&$counter) {

            if ($input->type == 'input-repeaterx') {
                if (isset($input->ext) && $input->ext == 'relationship') {
                    return [$counter++ => $input->name];
                } else {
                    return [];
                }
            } else {
                $relationship = $this->getCamelNameFromForeignKey($input->name) ?: $input->name;
            }

            if (in_array($input->type, ['select', 'combobox', 'autocomplete']) && ! isset($input->repository)) {
                return [];
            }

            $relationshipsTypes = [];

            if (method_exists($this->repository->getModel(), 'definedRelationsTypes')) {
                $relationshipsTypes = $this->repository->definedRelationsTypes();
            }

            $relationType = null;

            if (array_key_exists($relationship, $relationshipsTypes)) {
                $relationType = $relationshipsTypes[$relationship];
            }

            if (in_array($relationType, ['MorphToMany', 'BelongsToMany'])) {
                return [
                    $counter++ => $relationship,
                ];
            }

            return [
                $relationship => [
                    // ['select', $item['itemValue'], $item['itemTitle']],
                    ['addSelect', $input->itemValue ?? 'id'],
                    ['addSelect', $input->itemTitle ?? 'name'],
                ],
            ];
        })->toArray();
    }
}
