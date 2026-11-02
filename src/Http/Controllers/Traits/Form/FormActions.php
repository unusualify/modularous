<?php

namespace Unusualify\Modularous\Http\Controllers\Traits\Form;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Traits\Allowable;
use Unusualify\Modularous\Traits\ResponsiveVisibility;

trait FormActions
{
    use Allowable, ResponsiveVisibility;

    /**
     * @var array
     */
    protected $defaultFormActions = [];

    /**
     * @var array
     */
    protected $formActions = [];

    /**
     * @return void
     */
    protected function __afterConstructFormActions($app, $request)
    {
        // $this->setFormActions();
    }

    /**
     * Whether repository/config form actions have been merged into $formActions.
     *
     * @var bool
     */
    protected $formActionsResolved = false;

    public function preloadFormActions()
    {
        $this->defaultFormActions = (array) Config::get(modularousBaseKey() . '.default_form_actions', []);
    }

    /**
     * Merge config and repository form actions on first use; repository actions
     * may hydrate heavy schemas (e.g. payment) that index pages without a form modal never ship.
     */
    protected function resolveFormActions(): array
    {
        if ($this->formActionsResolved) {
            return $this->formActions;
        }

        $this->formActionsResolved = true;

        $formActions = [];

        if ((bool) $this->config) {
            try {
                $formActions = Collection::make(
                    array_merge_recursive_preserve($this->defaultFormActions, object_to_array($this->getConfigFieldsByRoute('form_actions', [])))
                )->toArray();
            } catch (\Throwable $th) {

            }
        }

        if (! $this->request->ajax() || $this->isInertiaAjaxRequest()) {
            $formActions = array_merge_recursive_preserve($this->repository ? $this->repository->getFormActions($this->user) : [], $formActions);

            $this->formActions = array_merge_recursive_preserve($formActions, $this->formActions ?? []);
        }

        return $this->formActions;
    }

    /**
     * @var array
     */
    public function setFormActions() {}

    public function getFormActions($type = 'index'): array
    {
        $default_action = (array) Config::get(modularousBaseKey() . '.default_form_action');

        $editOnModal = $this->tableAttributes['editOnModal'] ?? true;
        $createOnModal = $this->tableAttributes['createOnModal'] ?? true;

        if ($type === 'index' && ! $editOnModal && ! $createOnModal) {
            return [];
        }

        return Collection::make($this->resolveFormActions())->reduce(function ($acc, $action, $key) use ($default_action, $editOnModal, $createOnModal, $type) {

            $creatable = $action['creatable'] ?? true;
            $editable = $action['editable'] ?? true;

            if ($type === 'index' && ! $editOnModal && ! $creatable) {
                return $acc;
            }

            if ($type === 'index' && ! $createOnModal && ! $editable) {
                return $acc;
            }

            if ($type === 'edit' && ! $editable) {
                return $acc;
            }

            if ($type === 'create' && ! $creatable) {
                return $acc;
            }

            $isAllowed = $this->isAllowedItem(
                $action,
                searchKey: 'allowedRoles',
                orClosure: fn ($item) => $this->user->is_superadmin,
            );

            if (! $isAllowed) {
                return $acc;
            }

            if (isset($action['endpoint']) && ($routeName = Route::hasAdmin($action['endpoint']))) {
                $parameters = Route::getRoutes()->getByName($routeName)->parameterNames();
                $action['endpoint'] = route($routeName, array_fill_keys($parameters, ':id'));
                // $action['endpoint'] = route($routeName, ['press_release' => ':id']);
                // dd($parameters, $action);
                // $action['endpoint'] = route($routeName, ['{id}' => '{id}']);
            }

            if (isset($action['formDraft'])) {
                $formDraft = $action['formDraft'];
                if ($formDraft === 'company') {
                    $action['formAttributes'] = array_merge($action['formAttributes'] ?? [], [
                        'modelValue' => $this->user->company,
                    ]);
                }
                $action['schema'] = $this->createFormSchema(getFormDraft($action['formDraft']));
            }

            if (isset($action['schema'])) {
                $action['schema'] = $this->createFormSchema($action['schema']);
            }

            if (isset($action['responsive'])) {
                $action = $this->applyResponsiveClasses(
                    item: $action,
                    searchKey: 'responsive',
                    display: 'flex',
                    classNotation: 'componentProps.class'
                );
            }

            $acc[$key] = array_merge_recursive_preserve($default_action, $action);

            return $acc;
        }, []);
    }
}
