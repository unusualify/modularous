<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\Route;

class SlugHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     * @var array
     */
    public $requirements = [
        'label' => 'Slug',
        'default' => '',
        'localeScoped' => true,
        'excludeId' => null,
        'locale' => null,
    ];

    /**
     * Manipulate Input Schema Structure
     */
    public function hydrate(): array
    {
        $input = $this->input;

        $input['type'] = 'input-slug';

        if (isset($input['_moduleName']) && isset($input['_routeName'])) {
            $input['endpoint'] = resolve_route(Route::hasAdmin('inputs.slug.validate'));
        }

        $input['rules'] ??= 'required';

        return $input;
    }
}
