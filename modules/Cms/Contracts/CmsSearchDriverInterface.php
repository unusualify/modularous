<?php

namespace Modules\Cms\Contracts;

interface CmsSearchDriverInterface
{
    public function index(string $entityType, int|string $entityId, array $document): void;

    public function remove(string $entityType, int|string $entityId): void;

    public function search(string $query, array $options = []): array;
}
