<?php

namespace Unusualify\Modularity\Repositories\Traits;

trait AuthorizableTrait
{
    public function getTableFiltersAuthorizableTrait($scope = null): array
    {
        $model = $this->getModel();

        $tableFilters = [];

        if ($model->hasAuthorizationUsage()) {
            $tableFilters[] = [
                'name' => ___('listing.filter.authorized'),
                'slug' => 'authorized',
                'methods' => 'getCountFor',
                'params' => ['hasAnyAuthorization'],
            ];

            $tableFilters[] = [
                'name' => ___('listing.filter.unauthorized'),
                'slug' => 'unauthorized',
                'methods' => 'getCountFor',
                'params' => ['unauthorized'],
            ];
        }

        $tableFilters[] = [
            'name' => ___('listing.filter.your-authorizations'),
            'slug' => 'your-authorizations',
            'methods' => 'getCountFor',
            'params' => ['isAuthorizedToYou'],
        ];

        return $tableFilters;
    }
}
