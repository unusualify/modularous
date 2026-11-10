<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Facades\Modularous;

class CreatorHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'label' => 'Creator',
        'itemTitle' => 'email_with_company',
        'appends' => ['email_with_company'],
        'with' => ['company'],
        'allowedRoles' => ['superadmin'],
        'impersonatable' => true,
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        // add your logic
        $input['type'] = 'input-browser';

        $input['name'] = 'custom_creator_id';
        $input['multiple'] = false;
        $input['itemValue'] = 'id';
        $input['returnObject'] = false;

        $input['col'] = [
            'cols' => 12,
        ];

        $input['endpoint'] = route('admin.system.user.index', [
            'light' => true,
            'eager' => $input['with'],
            'appends' => $input['appends'],
        ]);
        unset($input['appends'], $input['with']);

        if (($input['impersonatable'] ?? true) && ($impersonateAction = $this->getImpersonateAction())) {
            $input['innerActions'] = array_merge($input['innerActions'] ?? [], [$impersonateAction]);
        }
        unset($input['impersonatable']);

        // add your logic

        return $input;
    }

    /**
     * Build the inner icon action that impersonates the selected creator,
     * only when the authenticated user is allowed to impersonate.
     */
    protected function getImpersonateAction(): ?array
    {
        $guardName = Modularous::getAuthGuardName();

        // The guard is only enforced for HTTP requests; console and test contexts may lack it.
        if (blank(config('auth.guards.' . $guardName))) {
            return null;
        }

        $user = Auth::guard($guardName)->user();

        if (! $user || ! $user->can('impersonate')) {
            return null;
        }

        $routeName = Route::hasAdmin('impersonate');

        if (! $routeName) {
            return null;
        }

        return [
            'icon' => 'mdi-account-switch',
            'tooltip' => __('Impersonate User'),
            'httpMethod' => 'post',
            'color' => 'primary',
            'href' => route($routeName, ['id' => ':id']),
        ];
    }
}
