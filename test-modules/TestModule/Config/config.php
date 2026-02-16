<?php

return [
    'name' => 'TestModule',
    'system_prefix' => false,
    'group' => 'test',
    'headline' => 'Test Module',
    'routes' => [
        'item' => [
            'name' => 'Item',
            'headline' => 'Items',
            'url' => 'items',
            'route_name' => 'item',
            'icon' => '$submodule',
            'title_column_key' => 'name',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'headers' => [
                [
                    'title' => 'Name',
                    'key' => 'name',
                    'formatter' => ['edit'],
                    'searchable' => true,
                ],
            ],
            'inputs' => [
                [
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                ],
            ],
        ],
    ],
];
