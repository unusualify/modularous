<?php

namespace Modules\SystemUser\Blueprint\User\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\ModuleRoute;

final class UserIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Name',
                'key' => 'name',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'title' => 'Surname',
                'key' => 'surname',
                'sortable' => true,
                'searchable' => true,
            ],
            [
                'title' => 'Email',
                'key' => 'email',
                'align' => 'start',
                'sortable' => false,
                'searchable' => true,
            ],
            [
                'title' => 'Company',
                'key' => 'company',
            ],
            [
                'title' => 'Role',
                'key' => 'roles',
                'itemTitle' => 'title',
                'minWidth' => 130,
            ],
            [
                'title' => 'Status',
                'key' => 'published',
                'formatter' => [
                    'switch',
                    ['color' => 'primary-darken-1'],
                ],
            ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'sortable' => false,
            ],
        ];
    }
}
