<?php

namespace Unusualify\Modularity\Services\BulkCsv;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Unusualify\Modularity\Contracts\CanBulkSheet;

/**
 * Application entry for CSV bulk import/export (delegates to {@see BulkCsvImportOrchestrator}).
 */
class BulkImportService
{
    public function __construct(
        protected BulkCsvImportOrchestrator $orchestrator,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function import(string $csvContent, bool $dryRun, CanBulkSheet $bulkSheet, string $toolKey): array
    {
        return $this->orchestrator->import($csvContent, $dryRun, $bulkSheet, $toolKey);
    }

    public function streamExport(CanBulkSheet $bulkSheet, ?string $filename = null): StreamedResponse
    {
        return $this->orchestrator->streamExport($bulkSheet, $filename);
    }
}
