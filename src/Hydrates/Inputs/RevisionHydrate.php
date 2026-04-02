<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class RevisionHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'name' => 'revision_id',
        'noSubmit' => true,
        'col' => ['cols' => 12],
        'default' => null,
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-revision';
        $input['name'] = 'revisionable_id';


        $snakeRouteName = snakeCase($this->routeName);

        $input['restoreEndpoint'] = $this->module->getRouteActionUrl(
            $this->routeName,
            'restoreRevision',
            [$snakeRouteName => ':id']
        );

        $input['showViewEndpoint'] = $this->module->getRouteActionUrl(
            $this->routeName,
            'showView',
            [$snakeRouteName => ':id']
        );

        $input['listRevisionsEndpoint'] = $this->module->getRouteActionUrl(
            $this->routeName,
            'listRevisions',
            [$snakeRouteName => ':id']
        );

        dd($input);

        return $input;
    }
}
