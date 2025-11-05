<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Entities\Tag;
use Unusualify\Modularity\Facades\Modularity;

class TagHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'itemValue' => 'id',
        'itemTitle' => 'name',
        'cascadeKey' => 'items',
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-tag';
        $translated = $input['translated'] ?? false;

        $input['returnObject'] = false;
        $input['chips'] = false;
        $input['multiple'] ??= false;

        if (isset($input['_moduleName'])) {
            $module = Modularity::find($input['_moduleName']);

            $repository = $module->getRouteClass($input['_routeName'], 'repository');
            $repository = App::make($repository);

            $input['endpoint'] = $module->getRouteActionUrl($input['_routeName'], 'tags');
            $input['updateEndpoint'] = $module->getRouteActionUrl($input['_routeName'], 'tagsUpdate');
            $input['items'] = ! $this->skipQueries
                ? $repository->getTags(translated: $translated)->toArray()
                : [];

            $input['taggable'] = get_class($repository->getModel());
        } else if(isset($input['taggable']) && @class_exists($input['taggable'])) {
            $taggableModel = App::make($input['taggable']);

            $input['items'] = !$this->skipQueries
                ? ($translated ? $taggableModel->localeTagsList() : Tag::whereNamespace($input['taggable'])->get())
                : collect([]);
        }

        if(!isset($input['updateEndpoint'])) {
            $input['updateEndpoint'] = route('admin.tag.update');
        }

        if($translated) {
            $input['cacheKey'] = '${localeParameter}$_' . $input['taggable'];
            $input['updatePayload'] = ['locale' => '${localeParameter}$'];
        }

        if (! $translated && ! isset($input['default']) && count($input['items']) > 0) {
            $input['default'] = $input['items'][0][$input['itemValue']];
        }

        return $input;
    }
}
