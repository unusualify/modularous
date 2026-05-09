<?php

namespace Modules\Cms\Services;

use Illuminate\Support\Facades\DB;
use Modules\Cms\Contracts\CmsSearchDriverInterface;

class DbFullTextSearchDriver implements CmsSearchDriverInterface
{
    protected function searchIndexesTable(): string
    {
        return modularousConfig('tables.cms_search_indexes', 'um_cms_search_indexes');
    }

    public function index(string $entityType, int|string $entityId, array $document): void
    {
        DB::table($this->searchIndexesTable())->updateOrInsert(
            [
                'entity_type' => $entityType,
                'entity_id' => (string) $entityId,
            ],
            [
                'document' => json_encode($document),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function remove(string $entityType, int|string $entityId): void
    {
        DB::table($this->searchIndexesTable())
            ->where('entity_type', $entityType)
            ->where('entity_id', (string) $entityId)
            ->delete();
    }

    public function search(string $query, array $options = []): array
    {
        $limit = (int) ($options['limit'] ?? 20);

        return DB::table($this->searchIndexesTable())
            ->where('document', 'like', '%' . $query . '%')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->toArray();
    }
}
