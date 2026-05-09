<?php

return [
    'name' => 'Cms',
    'system_prefix' => true,
    'group' => 'system',
    'headline' => 'CMS',
    'url' => 'cms',

    'promotion' => [
        'enabled' => modularousConfig('cms_promotion.enabled', true),
        'scope' => modularousConfig('cms_promotion.scope', []),
        'approval' => modularousConfig('cms_promotion.approval', []),
    ],

    'routes' => [
        'site_setting' => [
            'name' => 'SiteSetting',
            'headline' => 'Site Settings',
            'url' => 'site-settings',
            'route_name' => 'site_setting',
            'icon' => 'mdi-cog-sync-outline',
            'title_column_key' => 'key',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
            ],
            'headers' => [
                ['title' => 'Group', 'key' => 'group_key', 'searchable' => true],
                ['title' => 'Key', 'key' => 'key', 'searchable' => true],
                ['title' => 'Locale', 'key' => 'locale', 'searchable' => true],
                ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
            ],
            'inputs' => [
                ['name' => 'group_key', 'label' => 'Group', 'type' => 'text', 'rules' => 'required'],
                ['name' => 'key', 'label' => 'Key', 'type' => 'text', 'rules' => 'required'],
                ['name' => 'locale', 'label' => 'Locale', 'type' => 'locale', 'rules' => 'required'],
                ['name' => 'value', 'label' => 'Value', 'type' => 'textarea'],
            ],
        ],

        'page' => [
            'name' => 'Page',
            'headline' => 'Pages',
            'url' => 'pages',
            'route_name' => 'page',
            'icon' => '$submodule',
            'title_column_key' => 'title',
            'table_options' => [
                'includeScheduledInList' => true,
                'editOnModal' => false,
            ],
            'headers' => [
                ['title' => 'Title', 'key' => 'title', 'searchable' => true],
                ['title' => 'Published', 'key' => 'published', 'formatter' => [
                    0 => 'switch',
                    1 => [
                        'trueValue' => true,
                        'falseValue' => false,
                    ],
                ]],
                ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
            ],
            'inputs' => [
                // ['type' => 'switch', 'name' => 'published', 'label' => 'Published', 'trueValue' => true, 'falseValue' => false, 'isEvent' => true],
                // ['name' => 'publish_start_date', 'label' => 'Publish from', 'type' => 'date', 'isSecondary' => true],
                // ['name' => 'publish_end_date', 'label' => 'Publish until', 'type' => 'date', 'isSecondary' => true],
                ['type' => 'switch', 'name' => 'active', 'label' => 'Active', 'translated' => true, 'trueValue' => true, 'falseValue' => false, 'isSecondary' => true],
                ['type' => 'revision', 'maxHeight' => '150px'],
                ['type' => 'text', 'name' => 'title', 'label' => 'Title', 'translated' => true, 'rules' => 'required', 'ext' => 'update:slugs:slugSourceValue:modelValue'],
                ['type' => 'slug', 'name' => 'slugs', 'label' => 'URL slug', 'translated' => true, 'rules' => 'required', '_moduleName' => 'Cms', '_routeName' => 'page', 'localeScoped' => true],

                ['type' => 'file', 'name' => 'documents', 'label' => 'Files', 'translated' => true],
                ['type' => 'image', 'name' => 'photos', 'label' => 'Images', 'translated' => true],
                [
                    'type' => 'filepond',
                    'name' => 'attachments',
                    'label' => 'Fileponds',
                    'translated' => true,
                    'acceptedExtensions' => ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'tiff', 'ico', 'webp'],
                    'allowImagePreview' => true,
                ],
                [
                    'type' => 'json-repeater',
                    'name' => 'sessions',
                    'label' => 'Sessions',
                    'translated' => false,
                    'asObject' => true,
                    'default' => [],
                    'noHeaders' => true,
                    'formRowAttribute' => [
                        'noGutters' => true,
                        'class' => 'mt-6',
                    ],
                    'schema' => [
                        [
                            'type' => 'text',
                            'name' => 'session_title',
                            'label' => 'Session Title', 'type' => 'text',
                            'col' => [
                                'cols' => 6,
                                'class' => 'pr-2',
                            ],
                        ],
                        [
                            'type' => 'textarea',
                            'name' => 'session_description',
                            'label' => 'Session Description',
                            'col' => [
                                'cols' => 6,
                            ],
                        ],
                    ],
                ],
                ['name' => 'layout', 'label' => 'Layout', 'type' => 'text'],
                ['name' => 'content', 'label' => 'Content', 'type' => 'textarea', 'translated' => true],
                ['name' => 'schema', 'label' => 'Schema', 'type' => 'json', 'isSecondary' => true],
            ],
        ],
        'redirect' => [
            'name' => 'Redirect',
            'headline' => 'Redirects',
            'url' => 'redirects',
            'route_name' => 'redirect',
            'icon' => 'mdi-directions-fork',
            'title_column_key' => 'from_path',
            'headers' => [
                ['title' => 'Locale', 'key' => 'locale', 'searchable' => true],
                ['title' => 'From', 'key' => 'from_path', 'searchable' => true],
                ['title' => 'To', 'key' => 'to_path', 'searchable' => true],
                ['title' => 'Status', 'key' => 'status_code'],
                ['title' => 'Active', 'key' => 'is_active', 'formatter' => [
                    0 => 'switch',
                    1 => [
                        'trueValue' => true,
                        'falseValue' => false,
                    ],
                ]],
                ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
            ],
            'inputs' => [
                ['name' => 'from_path', 'label' => 'From path', 'type' => 'text', 'rules' => 'required'],
                ['name' => 'to_path', 'label' => 'To path', 'type' => 'text', 'rules' => 'required'],
                ['name' => 'locale', 'label' => 'Locale', 'type' => 'select', 'items' => getLocales(), 'rules' => 'required'],
                ['name' => 'status_code', 'label' => 'Status Code', 'type' => 'number', 'rules' => 'required|integer|in:301,302,307,308'],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'switch'],
            ],

            // CSV bulk sheet: default tool_key is derived from module + route (e.g. cms.redirect); override with tool_key if needed.
            'bulk_sheet' => [
                'export_download_filename' => 'redirects-export.csv',
                'step_up_ability' => 'redirect.bulk_import',
                'preview_table_columns' => [
                    ['title' => 'Line', 'key' => 'line', 'width' => '72px'],
                    ['title' => 'OK', 'key' => 'valid', 'sortable' => false],
                    ['title' => 'Action', 'key' => 'action'],
                    ['title' => 'Locale', 'key' => 'locale'],
                    ['title' => 'From', 'key' => 'from_path'],
                    ['title' => 'To', 'key' => 'to_path'],
                    ['title' => 'Errors', 'key' => 'errors', 'sortable' => false],
                    ['title' => 'Warnings', 'key' => 'warnings', 'sortable' => false],
                ],
                'api_route_names' => [
                    'dryRun' => 'bulk.dryRun',
                    'commit' => 'bulk.commit',
                    'export' => 'bulk.export',
                ],
            ],
        ],

        'parent_segment' => [
            'name' => 'ParentSegment',
            'headline' => 'URL parent segments',
            'url' => 'parent-segments',
            'route_name' => 'parent_segment',
            'icon' => 'mdi-source-branch',
            'title_column_key' => 'target_model_class',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
            ],
            'headers' => [
                ['title' => 'Model', 'key' => 'target_model_class', 'searchable' => true],
                ['title' => 'Locale', 'key' => 'locale', 'searchable' => true],
                ['title' => 'Prefix', 'key' => 'normalized_prefix', 'searchable' => true],
                ['title' => 'Label', 'key' => 'admin_label', 'searchable' => true],
                ['title' => 'Enabled', 'key' => 'enabled', 'formatter' => [
                    0 => 'switch',
                    1 => [
                        'trueValue' => true,
                        'falseValue' => false,
                    ],
                ]],
                ['title' => 'Sort', 'key' => 'sort_order'],
                ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
            ],
            'inputs' => [
                [
                    'type' => 'module-route-model',
                    'name' => 'target_model_class',
                    'label' => 'Module / route (model)',
                    'rules' => 'required|string|max:512',
                    'onlyParentSegmentModels' => true,
                ],
                ['name' => 'locale', 'label' => 'Locale (empty = all locales)', 'type' => 'text', 'rules' => 'nullable|string|max:12'],
                ['name' => 'normalized_prefix', 'label' => 'URL path prefix (empty = homepage / locale root)', 'type' => 'text', 'rules' => 'nullable|string|max:2048'],
                ['name' => 'admin_label', 'label' => 'Admin label', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                ['name' => 'enabled', 'label' => 'Enabled', 'type' => 'switch', 'trueValue' => true, 'falseValue' => false],
                ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number'],
            ],
        ],

        /**
         * Panel Inertia index ({@code Cms/Sitemap/Index}): item table + dry-run + commit; {@see \Modules\Cms\Repositories\SitemapRepository},
         * {@see \Modules\Cms\Http\Controllers\SitemapController}, {@see \Modules\Cms\Http\Controllers\CmsSitemapPanelController}, {@see \Modules\Cms\Routes\web}.
         */
        'sitemap' => [
            'name' => 'Sitemap',
            'headline' => 'Sitemap',
            'url' => 'sitemap',
            'route_name' => 'sitemap',
            'icon' => 'mdi-sitemap',
            'title_column_key' => 'id',
            'headers' => [
                ['title' => 'ID', 'key' => 'id', 'searchable' => true],
                ['title' => 'Slug', 'key' => 'slug', 'searchable' => true],
                ['title' => 'Created At', 'key' => 'created_at', 'searchable' => true],
                ['title' => 'Updated At', 'key' => 'updated_at', 'searchable' => true],
                ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
            ],
            'inputs' => [],
        ],
        'homepage_test' => [
            'name' => 'HomepageTest',
            'headline' => 'Homepage Tests',
            'url' => 'homepage-tests',
            'route_name' => 'homepage_test',
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
                    'formatter' => [
                        'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Status',
                    'key' => 'published',
                    'formatter' => [
                        'switch',
                    ],
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        'date',
                        'long',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Actions',
                    'key' => 'actions',
                    'sortable' => false,
                ],
            ],
            'inputs' => [
                [
                    'type' => 'text',
                    'name' => 'name',
                    'label' => 'Name',
                    'translated' => true,
                ]
            ],
        ],
    ],
];
