<?php

namespace Modules\SystemNotification\Blueprint\Notification\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteHeadersProvider;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\ModuleRoute;

final class NotificationIndexColumns implements ModuleRouteHeadersProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'title' => 'Message',
                'key' => 'data.message',
                'searchable' => true,
                'formatter' => [
                    'shorten',
                    10,
                ],
            ],
            [
                'title' => 'Recipient',
                'key' => 'recipient_summary',
                'sortable' => false,
                'formatter' => [
                    'hoverCard',
                    [
                        'title' => ['full_name', 'email'],
                        'subtitle' => 'email',
                        'avatar' => 'avatar',
                        'fields' => [
                            ['key' => 'company_name', 'label' => __('Company'), 'icon' => 'mdi-domain'],
                        ],
                        'actions' => $this->userActions(),
                    ],
                ],
            ],
            [
                'title' => 'Read',
                'key' => 'is_read',
                'formatter' => [
                    'status',
                ],
            ],
            [
                'title' => 'Created Time',
                'key' => 'created_at',
                'formatter' => [
                    'date',
                    'medium',
                ],
                'searchable' => true,
            ],
            [
                'title' => 'Actions',
                'key' => 'actions',
                'sortable' => false,
            ],
        ];
    }

    /**
     * Hover card actions; the edit URL keeps an `:id` placeholder filled per row on the client.
     *
     * @return list<array<string, string>>
     */
    private function userActions(): array
    {
        try {
            $editUrl = Modularous::find('SystemUser')?->getRouteActionUrl('User', 'edit', ['user' => ':id']);
        } catch (\Throwable) {
            $editUrl = null;
        }

        return $editUrl
            ? [['label' => __('Go to user'), 'href' => $editUrl, 'appendIcon' => 'mdi-arrow-right']]
            : [];
    }
}
